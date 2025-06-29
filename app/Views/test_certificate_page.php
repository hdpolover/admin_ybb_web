<!DOCTYPE html>
<html>
<head>
    <title>Certificate Management Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .btn-group .btn { margin-right: 2px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1>🎓 Certificate Management Test Page</h1>
        
        <div class="alert alert-info">
            <strong>Test Results:</strong><br>
            Program ID: 8 (Middle East Youth Summit 2025)<br>
            Expected: 4 awards with 0 participants assigned<br>
            Status: <span id="status">Loading...</span>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Awards & Certificate Management</h5>
            </div>
            <div class="card-body">
                <table id="test-certificates-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Award</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Recipients</th>
                            <th>Progress</th>
                            <th>Certificate Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            <h5>🔍 Debugging Information</h5>
            <div id="debug-info" class="bg-light p-3">
                <!-- Debug info will appear here -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log('🚀 Initializing test page...');
            
            $('#test-certificates-table').DataTable({
                ajax: {
                    url: '/test-certificates/data',
                    type: 'GET',
                    success: function(data) {
                        console.log('✅ DataTable success:', data);
                        document.getElementById('status').innerHTML = `<span class="text-success">Success! Found ${data.data.length} awards</span>`;
                        
                        let debugInfo = `
                            <strong>AJAX Response:</strong><br>
                            - Awards found: ${data.data.length}<br>
                            - Data structure: Valid<br>
                            - Buttons: ${data.data.length > 0 ? 'Present in actions column' : 'No data to show buttons'}<br>
                            <br>
                            <strong>Sample Award:</strong><br>
                            ${data.data.length > 0 ? `
                                - Title: ${data.data[0].title}<br>
                                - Participants: ${data.data[0].participants_count}<br>
                                - Actions: Available<br>
                            ` : 'No awards to display'}
                        `;
                        document.getElementById('debug-info').innerHTML = debugInfo;
                    },
                    error: function(xhr, error, thrown) {
                        console.error('❌ DataTable error:', error);
                        document.getElementById('status').innerHTML = `<span class="text-danger">Error: ${error}</span>`;
                        document.getElementById('debug-info').innerHTML = `
                            <strong>Error Details:</strong><br>
                            - Status: ${xhr.status}<br>
                            - Error: ${error}<br>
                            - Response: ${xhr.responseText.substring(0, 500)}...
                        `;
                    }
                },
                columns: [
                    { data: 'title' },
                    { data: 'award_type' },
                    { data: 'description' },
                    { data: 'participants_count', className: 'text-center' },
                    { data: 'progress', orderable: false },
                    { data: 'certificate_status', className: 'text-center', orderable: false },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                language: {
                    emptyTable: "No awards found for this program"
                }
            });
        });
    </script>
</body>
</html>
