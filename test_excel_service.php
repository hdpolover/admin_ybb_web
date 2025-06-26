<?php

// Set up CodeIgniter environment
define('SYSTEMPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/app/');
define('FCPATH', __DIR__ . '/public/');
define('WRITEPATH', __DIR__ . '/writable/');
define('ROOTPATH', __DIR__ . '/');

// Load CodeIgniter bootstrap
require_once SYSTEMPATH . 'bootstrap.php';

try {
    $service = new \App\Services\ExcelExport();
    echo "✓ ExcelExport service created successfully\n";
    echo "✓ All required PhpSpreadsheet classes are available\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
