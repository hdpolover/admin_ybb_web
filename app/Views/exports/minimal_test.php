<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Excel Export Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .container { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Minimal Excel Export - Names Only</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            This exports only participant names to Excel.
                        </div>
                        
                        <form action="/minimal-export/export-names" method="get">
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
                                <button type="submit" class="btn btn-primary">Export Names Only</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
