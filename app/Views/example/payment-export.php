<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Participants by Payment Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>Export Participants by Payment Status</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">This example shows how to export participants based on their payment status.</p>
                        
                        <h5 class="mt-4">Example 1: Export participants who have paid registration fee</h5>
                        <p>This will export all participants who have successfully paid their registration fees.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('exports/participants-by-payment?program_id=1&payment_category=registration') ?>" class="btn btn-primary">
                                Export Participants with Paid Registration
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mt-4">Example 2: Export participants who have paid program fee 1</h5>
                        <p>This will export all participants who have successfully paid their first program fee installment.</p>
                        
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('exports/participants-by-payment?program_id=1&payment_category=program_fee_1') ?>" class="btn btn-success">
                                Export Participants with Paid Program Fee 1
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mt-4">Example 3: Custom export with multiple filters</h5>
                        <p>You can also export participants with custom filters using the form below:</p>
                        
                        <form action="<?= base_url('exports/filtered-participants') ?>" method="get" class="mt-3">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="program_id" class="form-label">Program</label>
                                    <select name="program_id" id="program_id" class="form-select" required>
                                        <option value="">Select a program</option>
                                        <option value="1">Youth Break Through</option>
                                        <option value="2">Leadership Academy</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-select">
                                        <option value="">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Export with Custom Filters
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-muted">
                        <small>The exports will include participant essays and payment information.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
