// Excel Export Helper Functions

/**
 * Handles downloading an Excel file from base64 data
 * @param {string} base64data - The base64 encoded Excel data
 * @param {string} filename - The filename to save as
 * @param {string} mimeType - The MIME type (default: Excel MIME type)
 */
function downloadExcelFromBase64(base64data, filename, mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
    // Convert base64 to blob
    const byteCharacters = atob(base64data);
    const byteArrays = [];
    
    for (let offset = 0; offset < byteCharacters.length; offset += 512) {
        const slice = byteCharacters.slice(offset, offset + 512);
        
        const byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }
        
        const byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
    }
    
    const blob = new Blob(byteArrays, { type: mimeType });
    
    // Create download link
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    
    // Trigger download and cleanup
    link.click();
    
    // Clean up
    setTimeout(() => {
        URL.revokeObjectURL(url);
        document.body.removeChild(link);
    }, 100);
}

/**
 * Exports participants via AJAX and downloads the file
 * @param {number} programId - The program ID to export participants from
 */
function exportParticipants(programId) {
    // Show loading indicator
    const loadingElement = document.getElementById('export-loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Make AJAX request to the server
    fetch(`/simpleexport/exportSimple?program_id=${programId}`)
        .then(response => response.json())
        .then(data => {
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            if (data.success) {
                // Download the file
                downloadExcelFromBase64(data.data, data.filename, data.mime);
            } else {
                // Show error
                alert('Export failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            // Hide loading indicator
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
            console.error('Export error:', error);
            alert('Export failed due to a network error. Please try again.');
        });
}
