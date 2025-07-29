<?php
echo "=== YBB Export Filters Debug Test ===\n\n";

// Simulate form data from export modal
echo "1. Testing Form Data Collection:\n";
$testFormData = [
    'template' => 'standard',
    'format' => 'excel',
    'limit' => '100',
    'category' => 'fully_funded',
    'form_status' => '2',
    'date_range' => '2024-01-01 - 2024-12-31',
    'payment_status' => 'success',
    'program_payment_id' => '1'
];

foreach ($testFormData as $key => $value) {
    echo "   {$key} = '{$value}'\n";
}

echo "\n2. Testing Filter Application Logic:\n";

// Simulate the filter processing
function testFilterProcessing($filters) {
    $queryConditions = [];
    
    // Category filter
    if (!empty($filters['category'])) {
        $queryConditions[] = "participants.category = '{$filters['category']}'";
    }

    // Form status filter
    if ($filters['form_status'] !== '' && $filters['form_status'] !== null) {
        $queryConditions[] = "participant_statuses.form_status = '{$filters['form_status']}'";
    }

    // Date range filter
    if (!empty($filters['date_range'])) {
        $dates = explode(' - ', $filters['date_range']);
        if (count($dates) == 2) {
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            $queryConditions[] = "DATE(participants.created_at) >= '{$startDate}' AND DATE(participants.created_at) <= '{$endDate}'";
        }
    }

    // Payment status filter
    if (!empty($filters['payment_status']) && $filters['payment_status'] == 'success') {
        $queryConditions[] = "participants.id IN (SELECT participant_id FROM payments WHERE status = 2 AND is_deleted = 0)";
    }

    // Specific program payment filter
    if (!empty($filters['program_payment_id']) && is_numeric($filters['program_payment_id'])) {
        $queryConditions[] = "participants.id IN (SELECT participant_id FROM payments WHERE program_payment_id = {$filters['program_payment_id']} AND status = 2 AND is_deleted = 0)";
    }

    // Limit filter
    if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
        $queryConditions[] = "LIMIT {$filters['limit']}";
    }
    
    return $queryConditions;
}

$conditions = testFilterProcessing($testFormData);
foreach ($conditions as $condition) {
    echo "   WHERE/CONDITION: {$condition}\n";
}

echo "\n3. Potential Issues Identified:\n";

// Check for common issues
$issues = [];

// Issue 1: Date range parsing
if (!empty($testFormData['date_range'])) {
    $dates = explode(' - ', $testFormData['date_range']);
    if (count($dates) != 2) {
        $issues[] = "Date range format issue - expected 'YYYY-MM-DD - YYYY-MM-DD'";
    } else {
        $startDate = date('Y-m-d', strtotime($dates[0]));
        $endDate = date('Y-m-d', strtotime($dates[1]));
        if ($startDate == '1970-01-01' || $endDate == '1970-01-01') {
            $issues[] = "Date parsing failed - invalid date format";
        }
    }
}

// Issue 2: Form status value type
if (isset($testFormData['form_status'])) {
    if (!in_array($testFormData['form_status'], ['', '0', '1', '2'])) {
        $issues[] = "Invalid form_status value: '{$testFormData['form_status']}'";
    }
}

// Issue 3: Payment status validation
if (isset($testFormData['payment_status']) && !in_array($testFormData['payment_status'], ['', 'success'])) {
    $issues[] = "Invalid payment_status value: '{$testFormData['payment_status']}'";
}

// Issue 4: Numeric validation
if (isset($testFormData['program_payment_id']) && !is_numeric($testFormData['program_payment_id'])) {
    $issues[] = "program_payment_id must be numeric";
}

if (isset($testFormData['limit']) && !is_numeric($testFormData['limit'])) {
    $issues[] = "limit must be numeric";
}

if (empty($issues)) {
    echo "   ✓ No obvious filter validation issues found\n";
} else {
    foreach ($issues as $issue) {
        echo "   ✗ {$issue}\n";
    }
}

echo "\n4. Testing JavaScript Form Data Collection:\n";

$jsFormElements = [
    'export-template' => 'standard',
    'export-format' => 'excel', 
    'export-limit' => '100',
    'export-category' => 'fully_funded',
    'export-form-status' => '2',
    'export-date-range' => '2024-01-01 - 2024-12-31',
    'export-payment-status' => 'success',
    'export-program-payment' => '1'
];

echo "   JavaScript should collect these form elements:\n";
foreach ($jsFormElements as $elementId => $value) {
    echo "     $('#{$elementId}').val() = '{$value}'\n";
}

echo "\n5. Expected Request Data Structure:\n";
echo "   POST /exports/participants with form data:\n";
foreach ($testFormData as $key => $value) {
    echo "     {$key}: '{$value}'\n";
}

echo "\n=== End Debug Test ===\n";
?>
