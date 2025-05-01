<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Excel Export Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .container { margin-top: 50px; }
        #export-loading { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Simplified Excel Export Test</h4>
                    </div>
                    <div class="card-body">
                        <form id="export-form">
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Select Program</label>
                                <select class="form-control" id="program_id" name="program_id" required>
                                    <option value="">-- Select Program --</option>
                                    <?php foreach ($programs as $program): ?>
                                    <option value="<?= $program->id ?>"><?= esc($program->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Export Participants</button>
                            </div>
                        </form>
                        
                        <div id="export-loading" class="mt-3 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Generating Excel file, please wait...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/excel-export.js"></script>
    <script>
        $(document).ready(function() {
            // Handle form submission
            $('#export-form').on('submit', function(e) {
                e.preventDefault();
                
                const programId = $('#program_id').val();
                if (!programId) {
                    alert('Please select a program');
                    return;
                }
                
                // Call the export function
                exportParticipants(programId);
            });
        });
    </script>
</body>
</html>
