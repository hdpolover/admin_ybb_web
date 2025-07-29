<!DOCTYPE html>
<html>
<head>
    <title>Export Filters Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Export Filters Test</h1>
    
    <h2>Test Form 1: Category Filter</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Category: <select name="category">
            <option value="">All</option>
            <option value="fully_funded" selected>Fully Funded</option>
            <option value="self_funded">Self Funded</option>
        </select></label>
        <button type="submit">Test Category Filter</button>
    </form>
    
    <h2>Test Form 2: Form Status Filter</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Form Status: <select name="form_status">
            <option value="">All</option>
            <option value="0">Not Started</option>
            <option value="1">On Progress</option>
            <option value="2" selected>Submitted</option>
        </select></label>
        <button type="submit">Test Form Status Filter</button>
    </form>
    
    <h2>Test Form 3: Payment Status Filter</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Payment Status: <select name="payment_status">
            <option value="">All</option>
            <option value="success" selected>Success Only</option>
        </select></label>
        <button type="submit">Test Payment Status Filter</button>
    </form>
    
    <h2>Test Form 4: Date Range Filter</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Date Range: <input type="text" name="date_range" value="2024-01-01 - 2024-12-31"></label>
        <button type="submit">Test Date Range Filter</button>
    </form>
    
    <h2>Test Form 5: Limit Filter</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Limit: <select name="limit">
            <option value="">All</option>
            <option value="5" selected>5 Records</option>
            <option value="10">10 Records</option>
        </select></label>
        <button type="submit">Test Limit Filter</button>
    </form>
    
    <h2>Test Form 6: Multiple Filters</h2>
    <form method="POST" action="/exports/participants">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="program_id" value="2">
        <label>Category: <select name="category">
            <option value="">All</option>
            <option value="fully_funded" selected>Fully Funded</option>
        </select></label><br><br>
        <label>Form Status: <select name="form_status">
            <option value="">All</option>
            <option value="2" selected>Submitted</option>
        </select></label><br><br>
        <label>Limit: <select name="limit">
            <option value="">All</option>
            <option value="3" selected>3 Records</option>
        </select></label>
        <button type="submit">Test Multiple Filters</button>
    </form>
    
    <h2>Instructions</h2>
    <p>1. Click each test button to test individual filters</p>
    <p>2. Check the logs at <code>writable/logs/</code> for debug information</p>
    <p>3. Check browser network tab to see the actual request data</p>
    <p>4. Compare the results to see which filters work and which don't</p>
    
</body>
</html>
