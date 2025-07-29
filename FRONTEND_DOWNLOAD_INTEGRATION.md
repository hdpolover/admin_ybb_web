# Frontend Download Integration Guide

## Overview
This guide explains what your frontend application needs to do after receiving a successful export status response from the YBB Data Management Service.

## When to Use This Guide
Use this guide when your status check returns:
```json
{
  "success": true,
  "data": {
    "status": "success",
    "export_id": "f72f0c14-28be-4f5e-b5b8-efbca02056de",
    "file_size": 1442532,
    "record_count": 860,
    "export_type": "participants",
    "created_at": "2025-07-26T13:32:46.377978",
    "expires_at": "2025-07-27T13:32:46.377988"
  }
}
```

## Step-by-Step Integration

### 1. Parse the Successful Status Response

When your status check receives `"status": "success"`, the export file is ready for download.

**Key Information Available:**
- `export_id`: Unique identifier for the export
- `file_size`: Size of the generated file in bytes
- `record_count`: Number of records in the export
- `export_type`: Type of export (participants, payments, ambassadors)
- `expires_at`: When the export file will be automatically deleted

### 2. CodeIgniter Implementation

#### A. Controller Method for Direct Download

```php
<?php
class ExportsController extends CI_Controller 
{
    public function __construct() {
        parent::__construct();
        $this->load->library('ybb_export');
    }
    
    /**
     * Download export file after successful status check
     */
    public function download($export_id) 
    {
        try {
            // Final status verification
            $status = $this->ybb_export->get_export_status($export_id);
            
            if (!$status['success'] || $status['data']['status'] !== 'success') {
                show_404('Export not found or not ready');
                return;
            }
            
            // Get export metadata
            $export_data = $status['data'];
            
            // Download the file
            $download_result = $this->ybb_export->download_export($export_id);
            
            if (!$download_result['success']) {
                log_message('error', 'Export download failed: ' . $download_result['message']);
                show_error('Download failed: ' . $download_result['message'], 500);
                return;
            }
            
            // Prepare file for download
            $file_content = $download_result['data']['content'];
            $filename = $download_result['data']['filename'];
            
            // Set appropriate headers
            $this->_set_download_headers($filename, strlen($file_content));
            
            // Output file content
            echo $file_content;
            
            // Log successful download
            log_message('info', "Export downloaded: ID={$export_id}, records={$export_data['record_count']}, size=" . strlen($file_content));
            
        } catch (Exception $e) {
            log_message('error', 'Export download exception: ' . $e->getMessage());
            show_error('Download failed: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * AJAX endpoint for getting download information
     */
    public function get_download_info($export_id) 
    {
        $this->output->set_content_type('application/json');
        
        try {
            $status = $this->ybb_export->get_export_status($export_id);
            
            if (!$status['success'] || $status['data']['status'] !== 'success') {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'Export not ready for download'
                ]));
                return;
            }
            
            $export_data = $status['data'];
            
            $this->output->set_output(json_encode([
                'success' => true,
                'data' => [
                    'download_url' => base_url("exports/download/{$export_id}"),
                    'filename' => $this->_generate_display_filename($export_data),
                    'file_size' => $export_data['file_size'],
                    'file_size_formatted' => $this->_format_file_size($export_data['file_size']),
                    'record_count' => $export_data['record_count'],
                    'record_count_formatted' => number_format($export_data['record_count']),
                    'export_type' => $export_data['export_type'],
                    'created_at' => $export_data['created_at'],
                    'expires_at' => $export_data['expires_at']
                ]
            ]));
            
        } catch (Exception $e) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Set appropriate download headers
     */
    private function _set_download_headers($filename, $file_size) 
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Determine content type
        $content_types = [
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv'
        ];
        
        $content_type = isset($content_types[$extension]) 
            ? $content_types[$extension] 
            : 'application/octet-stream';
        
        // Set headers
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        header('Pragma: public');
    }
    
    /**
     * Generate user-friendly filename for display
     */
    private function _generate_display_filename($export_data) 
    {
        $type = ucfirst($export_data['export_type']);
        $date = date('Y-m-d_H-i-s', strtotime($export_data['created_at']));
        $count = $export_data['record_count'];
        
        return "YBB_{$type}_Export_{$count}_records_{$date}.xlsx";
    }
    
    /**
     * Format file size for display
     */
    private function _format_file_size($bytes) 
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
?>
```

#### B. Routes Configuration

Add to your `routes.php`:

```php
// Export download routes
$route['exports/download/(:any)'] = 'exports/download/$1';
$route['exports/info/(:any)'] = 'exports/get_download_info/$1';
```

### 3. Frontend JavaScript Implementation

#### A. Status Check Success Handler

```javascript
/**
 * Handle successful export status response
 */
function handleExportReady(exportId, statusData) {
    console.log('Export ready for download:', statusData);
    
    // Update UI to show export is ready
    updateExportStatus(statusData);
    
    // Enable download functionality
    enableDownload(exportId, statusData);
    
    // Optional: Auto-download or show download button
    showDownloadOptions(exportId, statusData);
}

/**
 * Update UI with export completion information
 */
function updateExportStatus(statusData) {
    const statusContainer = document.getElementById('export-status');
    const data = statusData.data;
    
    statusContainer.innerHTML = `
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Export Ready!</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Records:</strong> ${data.record_count.toLocaleString()}</p>
                    <p><strong>File Size:</strong> ${formatFileSize(data.file_size)}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Type:</strong> ${data.export_type}</p>
                    <p><strong>Created:</strong> ${formatDateTime(data.created_at)}</p>
                </div>
            </div>
        </div>
    `;
}

/**
 * Enable download functionality
 */
function enableDownload(exportId, statusData) {
    const downloadBtn = document.getElementById('download-btn');
    const data = statusData.data;
    
    downloadBtn.disabled = false;
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download Export';
    downloadBtn.className = 'btn btn-success btn-lg';
    
    // Set click handler
    downloadBtn.onclick = () => initiateDownload(exportId);
    
    // Show additional info
    const infoText = document.getElementById('download-info');
    infoText.innerHTML = `
        Ready to download ${data.record_count.toLocaleString()} records 
        (${formatFileSize(data.file_size)})
    `;
}

/**
 * Initiate file download
 */
function initiateDownload(exportId) {
    // Method 1: Direct download via window.open
    const downloadUrl = `${baseUrl}exports/download/${exportId}`;
    window.open(downloadUrl, '_blank');
    
    // Method 2: Alternative with fetch (for progress tracking)
    // downloadWithProgress(exportId);
    
    // Update UI
    showDownloadStarted();
}

/**
 * Alternative download with progress tracking
 */
function downloadWithProgress(exportId) {
    const downloadBtn = document.getElementById('download-btn');
    downloadBtn.disabled = true;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing Download...';
    
    fetch(`${baseUrl}exports/info/${exportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create download link
                const link = document.createElement('a');
                link.href = data.data.download_url;
                link.download = data.data.filename;
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Show success
                showDownloadSuccess(data.data);
            } else {
                showDownloadError(data.message);
            }
        })
        .catch(error => {
            showDownloadError('Network error: ' + error.message);
        })
        .finally(() => {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download Export';
        });
}

/**
 * Show download started message
 */
function showDownloadStarted() {
    showMessage('info', 'Download started! Check your downloads folder.', 3000);
}

/**
 * Show download success message
 */
function showDownloadSuccess(downloadData) {
    showMessage('success', 
        `Download completed: ${downloadData.filename} (${downloadData.file_size_formatted})`, 
        5000
    );
}

/**
 * Show download error message
 */
function showDownloadError(message) {
    showMessage('error', 'Download failed: ' + message, 10000);
}

/**
 * Utility: Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Utility: Format date/time
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

/**
 * Utility: Show message to user
 */
function showMessage(type, message, duration = 5000) {
    const messageContainer = document.getElementById('message-container');
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    messageDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    messageContainer.appendChild(messageDiv);
    
    // Auto-remove after duration
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, duration);
}
```

### 4. HTML Template Example

```html
<!DOCTYPE html>
<html>
<head>
    <title>YBB Export Download</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-file-export"></i> Export Status</h4>
                    </div>
                    <div class="card-body">
                        <!-- Message container -->
                        <div id="message-container"></div>
                        
                        <!-- Export status -->
                        <div id="export-status">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin"></i> Checking export status...
                            </div>
                        </div>
                        
                        <!-- Download section -->
                        <div class="text-center mt-4">
                            <button id="download-btn" class="btn btn-secondary btn-lg" disabled>
                                <i class="fas fa-clock"></i> Waiting for Export...
                            </button>
                            <div id="download-info" class="text-muted mt-2"></div>
                        </div>
                        
                        <!-- Export details -->
                        <div class="mt-4">
                            <h6>Export Information:</h6>
                            <div id="export-details" class="small text-muted">
                                Export ID: <?= $export_id ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const baseUrl = '<?= base_url() ?>';
        const exportId = '<?= $export_id ?>';
        
        // Your status checking and download code here
    </script>
</body>
</html>
```

### 5. Error Handling

#### Common Scenarios to Handle:

1. **Export Expired**: File was deleted due to TTL
2. **Download Failed**: Network or server error
3. **File Not Found**: Export ID invalid or cleaned up
4. **Large File Timeout**: Download interrupted

#### Error Handling Code:

```php
// In your controller
public function download($export_id) {
    try {
        $status = $this->ybb_export->get_export_status($export_id);
        
        if (!$status['success']) {
            if (strpos($status['message'], 'not found') !== false) {
                show_404('Export not found or has expired');
            } else {
                show_error('Export status check failed: ' . $status['message'], 500);
            }
            return;
        }
        
        if ($status['data']['status'] !== 'success') {
            show_error('Export is not ready for download', 400);
            return;
        }
        
        // Continue with download...
        
    } catch (Exception $e) {
        log_message('error', 'Download error: ' . $e->getMessage());
        show_error('An unexpected error occurred during download', 500);
    }
}
```

### 6. Testing Your Implementation

#### Test Download Flow:

1. **Generate Test Export**:
   ```bash
   curl -X POST "https://ybb-data-management-service-production.up.railway.app/api/ybb/export/participants" \
        -H "Content-Type: application/json" \
        -d '{"limit": 10}'
   ```

2. **Check Status Until Ready**:
   ```bash
   curl "https://ybb-data-management-service-production.up.railway.app/api/ybb/export/{export_id}/status"
   ```

3. **Test Download**:
   ```bash
   curl -o "test_export.xlsx" \
        "https://ybb-data-management-service-production.up.railway.app/api/ybb/export/{export_id}/download"
   ```

### 7. Performance Considerations

- **Large Files**: Consider using streaming for files > 10MB
- **Concurrent Downloads**: Implement rate limiting if needed
- **Caching**: Cache export metadata to reduce API calls
- **Progress Tracking**: Show download progress for large files

### 8. Security Notes

- Validate export IDs before processing
- Implement proper access controls
- Log all download attempts
- Consider implementing download tokens for additional security
- Set appropriate file download limits

## Summary

After receiving a successful export status:

1. ✅ **Parse the response** - Extract export metadata
2. ✅ **Update UI** - Show export is ready with details
3. ✅ **Enable download** - Activate download button/link
4. ✅ **Handle download** - Use direct URL or fetch approach
5. ✅ **Provide feedback** - Show success/error messages
6. ✅ **Handle errors** - Gracefully handle various failure scenarios

Your export with **860 participants** (1.4MB) is ready for download using the provided integration methods.
