<?php
echo "PHP Configuration for File Uploads:\n";
echo "=====================================\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "\nPHP Extensions:\n";
echo "================\n";
echo "FTP extension: " . (extension_loaded('ftp') ? 'Yes' : 'No') . "\n";
echo "cURL extension: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "OpenSSL extension: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "\n";
echo "\nRecommended Settings for Large File Uploads:\n";
echo "============================================\n";
echo "upload_max_filesize: 50M or higher\n";
echo "post_max_size: 50M or higher\n";
echo "max_execution_time: 300 or higher\n";
echo "max_input_time: 300 or higher\n";
echo "memory_limit: 256M or higher\n";
?>
