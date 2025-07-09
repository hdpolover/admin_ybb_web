<?php
/**
 * Test script to verify Ambassador DataTable filters
 */

// Test the column mapping function
function getColumnIndex($columnName) {
    switch ($columnName) {
        case 'created_at':
            return 0; // Maps to # column (row number, but we can use ID for sorting)
        case 'full_name':
        case 'name':
            return 1; // Maps to Details column
        case 'ref_code':
            return 2; // Maps to Referral Code column
        case 'referral_count':
            return 3; // Maps to Referrals column
        default:
            return 0; // Default to first column
    }
}

// Test the column mapping
$testCases = [
    'created_at' => 0,
    'name' => 1,
    'full_name' => 1,
    'ref_code' => 2,
    'referral_count' => 3,
    'unknown' => 0
];

echo "Testing Column Mapping:\n";
foreach ($testCases as $input => $expected) {
    $result = getColumnIndex($input);
    $status = ($result === $expected) ? 'PASS' : 'FAIL';
    echo "$input -> $result (Expected: $expected) [$status]\n";
}

// Test the column database mapping
$columns = [
    0 => 'ambassadors.id',        // # column
    1 => 'ambassadors.name',      // Details column (sorted by name)
    2 => 'ambassadors.ref_code',  // Referral Code column
    3 => 'referral_count',        // Referrals column (virtual)
    4 => 'ambassadors.id'         // Actions column (not sortable)
];

echo "\nTesting Database Column Mapping:\n";
foreach ($columns as $index => $dbColumn) {
    echo "Column $index -> $dbColumn\n";
}

// Test sort order logic
function testSortOrder($sortString) {
    $sortInfo = explode('-', $sortString);
    if (count($sortInfo) === 2) {
        return [
            'column' => getColumnIndex($sortInfo[0]),
            'dir' => $sortInfo[1]
        ];
    }
    return ['column' => 0, 'dir' => 'desc'];
}

echo "\nTesting Sort Order Parsing:\n";
$sortTests = [
    'created_at-desc' => ['column' => 0, 'dir' => 'desc'],
    'name-asc' => ['column' => 1, 'dir' => 'asc'],
    'referral_count-desc' => ['column' => 3, 'dir' => 'desc'],
    'referral_count-asc' => ['column' => 3, 'dir' => 'asc'],
];

foreach ($sortTests as $input => $expected) {
    $result = testSortOrder($input);
    $status = ($result['column'] === $expected['column'] && $result['dir'] === $expected['dir']) ? 'PASS' : 'FAIL';
    echo "$input -> Column: {$result['column']}, Dir: {$result['dir']} [$status]\n";
}

echo "\nAll tests completed!\n";
