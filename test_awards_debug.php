<?php
/**
 * Quick test to see available awards
 */

// Get database connection
$db = \Config\Database::connect();

echo "<h2>Award Testing</h2>";

// Check program_awards table
echo "<h3>Program Awards in Database:</h3>";
$awards = $db->query("SELECT id, title, program_id, award_type FROM program_awards WHERE is_active = 1 AND is_deleted = 0 LIMIT 10")->getResult();

if (empty($awards)) {
    echo "<p>No awards found in database!</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Program ID</th><th>Award Type</th><th>Test Link</th></tr>";
    foreach ($awards as $award) {
        echo "<tr>";
        echo "<td>{$award->id}</td>";
        echo "<td>{$award->title}</td>";
        echo "<td>{$award->program_id}</td>";
        echo "<td>{$award->award_type}</td>";
        echo "<td><a href='/debug/certificates-view/{$award->id}' target='_blank'>Debug</a> | <a href='/documents/certificates/view/{$award->id}' target='_blank'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check session info
echo "<h3>Session Information:</h3>";
echo "<p>Current Program: " . (session('current_program') ?? 'Not Set') . "</p>";
echo "<p>User ID: " . (session('user_id') ?? 'Not Set') . "</p>";

// Test basic database tables
echo "<h3>Database Tables Check:</h3>";
$tables = ['program_awards', 'participants', 'participant_awards', 'program_certificates', 'participant_certificates'];

foreach ($tables as $table) {
    try {
        $count = $db->query("SELECT COUNT(*) as count FROM {$table}")->getRow()->count;
        echo "<p>✅ Table '{$table}': {$count} records</p>";
    } catch (Exception $e) {
        echo "<p>❌ Table '{$table}': Error - {$e->getMessage()}</p>";
    }
}
?>
