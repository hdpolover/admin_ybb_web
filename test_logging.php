<?php

// Load the framework
require 'app/Config/Paths.php';
require 'system/bootstrap.php';

// Output the current environment
echo "Current environment: " . ENVIRONMENT . "\n";

// Try to create a log entry
log_message('info', 'Test log entry from test_logging.php');
log_message('debug', 'Debug test log entry');
log_message('error', 'Error test log entry');

echo "Log entries created. Check writable/logs directory.\n";
