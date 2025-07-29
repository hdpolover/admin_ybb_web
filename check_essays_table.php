<?php
$pdo = new PDO('mysql:host=194.163.42.101;dbname=u1437096_ybb_master_app_db', 'u1437096_ybb_master_app_admin_user', '7J8*^dFEa&lN');
echo "PROGRAM_ESSAYS TABLE:\n";
$stmt = $pdo->query('DESCRIBE program_essays');
while ($row = $stmt->fetch()) {
    echo "- {$row['Field']} ({$row['Type']})\n";
}
?>
