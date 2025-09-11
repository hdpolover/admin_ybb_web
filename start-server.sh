#!/bin/bash

# YBB Admin Web Server Startup Script
# Starts the CodeIgniter development server on port 8100

echo "Starting YBB Admin Web Server on port 8100..."
echo "Press Ctrl+C to stop the server"
echo "Server will be available at: http://localhost:8100"
echo ""

# Set PHP error reporting to suppress deprecation warnings in development
# This helps reduce log noise from framework-level deprecations
export PHP_INI_SCAN_DIR=""
php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_STRICT" \
    -d display_errors=1 \
    -d log_errors=1 \
    -d session.sid_length=40 \
    spark serve --port 8100