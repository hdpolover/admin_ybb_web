<!DOCTYPE html>
<html>
<head>
    <title>YBB Export Filters Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>YBB Export Filters Test</h1>
    
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
        
        <table border="1">
            <tr>
                <th>Filter</th>
                <th>Value</th>
                <th>Expected Behavior</th>
            </tr>
            <tr>
                <td>Template</td>
                <td><select name="template">
                    <option value="standard" selected>Standard</option>
                    <option value="detailed">Detailed</option>
                </select></td>
                <td>Should appear in export options</td>
            </tr>
            <tr>
                <td>Format</td>
                <td><select name="format">
                    <option value="excel" selected>Excel</option>
                    <option value="csv">CSV</option>
                </select></td>
                <td>Should control export format</td>
            </tr>
            <tr>
                <td>Category</td>
                <td><select name="category">
                    <option value="">All</option>
                    <option value="fully_funded" selected>Fully Funded</option>
                    <option value="self_funded">Self Funded</option>
                </select></td>
                <td>Should filter participants by category</td>
            </tr>
            <tr>
                <td>Form Status</td>
                <td><select name="form_status">
                    <option value="">All</option>
                    <option value="0">Not Started</option>
                    <option value="1">On Progress</option>
                    <option value="2" selected>Submitted</option>
                </select></td>
                <td>Should filter by form submission status</td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td><select name="payment_status">
                    <option value="">All</option>
                    <option value="success" selected>Success Only</option>
                </select></td>
                <td>Should filter only paid participants</td>
            </tr>
            <tr>
                <td>Date Range</td>
                <td><input type="text" name="date_range" value="2024-01-01 - 2024-12-31"></td>
                <td>Should filter by registration date range</td>
            </tr>
            <tr>
                <td>Program Payment ID</td>
                <td><select name="program_payment_id">
                    <option value="">All</option>
                    <option value="1" selected>Payment Option 1</option>
                </select></td>
                <td>Should filter by specific payment type</td>
            </tr>
            <tr>
                <td>Limit</td>
                <td><select name="limit">
                    <option value="">All</option>
                    <option value="5" selected>5 Records</option>
                    <option value="10">10 Records</option>
                </select></td>
                <td>Should limit export to specified number</td>
            </tr>
        </table>
        
        <br>
        <button type="submit">Test All Filters</button>
    </form>
    
    <h2>Individual Filter Tests</h2>
    
    <div id="test-category">
        <h3>Test Category Filter Only</h3>
        <form method="POST" action="/exports/participants">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
            <input type="hidden" name="category" value="fully_funded">
            <button type="submit">Test Category: Fully Funded</button>
        </form>
    </div>
    
    <div id="test-form-status">
        <h3>Test Form Status Filter Only</h3>
        <form method="POST" action="/exports/participants">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
            <input type="hidden" name="form_status" value="2">
            <button type="submit">Test Form Status: Submitted</button>
        </form>
    </div>
    
    <div id="test-payment-status">
        <h3>Test Payment Status Filter Only</h3>
        <form method="POST" action="/exports/participants">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
            <input type="hidden" name="payment_status" value="success">
            <button type="submit">Test Payment Status: Success</button>
        </form>
    </div>
    
    <div id="test-date-range">
        <h3>Test Date Range Filter Only</h3>
        <form method="POST" action="/exports/participants">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
            <input type="hidden" name="date_range" value="2024-04-01 - 2024-07-31">
            <button type="submit">Test Date Range: Apr-Jul 2024</button>
        </form>
    </div>
    
    <div id="test-limit">
        <h3>Test Limit Filter Only</h3>
        <form method="POST" action="/exports/participants">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="program_id" value="<?= session('current_program') ?>">
            <input type="hidden" name="limit" value="3">
            <button type="submit">Test Limit: 3 Records</button>
        </form>
    </div>
    
    <h2>Instructions</h2>
    <ol>
        <li>Test each filter individually to see which ones work</li>
        <li>Check the logs at <code>writable/logs/</code> for detailed debugging information</li>
        <li>Look for messages like "Export filter: Added category = fully_funded"</li>
        <li>Compare the number of records exported with and without filters</li>
        <li>Verify that the filtering is working correctly by checking export contents</li>
    </ol>
    
    <h2>Expected Log Messages</h2>
    <pre>
INFO - Export filter: Using program_id from session: 7
INFO - Export filter: Added category = fully_funded
INFO - Export filter: Added form_status = 2
INFO - Export filter: Added payment_status = success
INFO - Export filter: Converted date_range "2024-01-01 - 2024-12-31" to date_from: 2024-01-01 00:00:00, date_to: 2024-12-31 23:59:59
INFO - Export filter: Added program_payment_id = 1
INFO - Export filter: Added limit = 5
INFO - Export filters applied: {"program_id":"7","category":"fully_funded","form_status":"2","payment_status":"success","date_from":"2024-01-01 00:00:00","date_to":"2024-12-31 23:59:59","program_payment_id":"1","limit":"5"}
INFO - Participant export: Filtering by category = fully_funded
INFO - Participant export: Filtering by form_status = 2
INFO - Participant export: Filtering by payment_status = success (only paid participants)
INFO - Participant export: Filtering by date_from = 2024-01-01 00:00:00
INFO - Participant export: Filtering by date_to = 2024-12-31 23:59:59
INFO - Participant export: Filtering by program_payment_id = 1
INFO - Participant export: Applying limit = 5
    </pre>
    
</body>
</html>
