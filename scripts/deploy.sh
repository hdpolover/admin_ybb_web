#!/bin/bash

# YBB Admin System Deployment Script
# This script handles the deployment of the CodeIgniter 4 application to VPS

set -e  # Exit on any error

# Configuration
DEPLOYMENT_PATH="${VPS_PATH:-/var/www/ybb-admin}"
BACKUP_DIR="${DEPLOYMENT_PATH}/backups"
LOG_FILE="${DEPLOYMENT_PATH}/deployment.log"
PHP_VERSION="${PHP_VERSION:-8.1}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${2:-$GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

# Error handling
error() {
    log "ERROR: $1" "$RED"
    exit 1
}

# Warning function
warn() {
    log "WARNING: $1" "$YELLOW"
}

# Check if we're in the right directory
if [ ! -f "public/index.php" ] || [ ! -d "app" ]; then
    error "Not in CodeIgniter application directory!"
fi

log "Starting YBB Admin System deployment..."

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# 1. Create backup of current deployment
create_backup() {
    log "Creating backup of current deployment..."
    
    timestamp=$(date +%Y%m%d_%H%M%S)
    backup_file="${BACKUP_DIR}/backup_${timestamp}.tar.gz"
    
    tar -czf "$backup_file" \
        --exclude='writable/logs/*' \
        --exclude='writable/cache/*' \
        --exclude='writable/session/*' \
        --exclude='writable/debugbar/*' \
        --exclude='writable/uploads/temp/*' \
        --exclude='*.log' \
        app/ public/ system/ vendor/ spark composer.* *.php .env 2>/dev/null || true
    
    if [ -f "$backup_file" ]; then
        log "Backup created: $backup_file"
        
        # Keep only last 10 backups
        cd "$BACKUP_DIR"
        ls -t backup_*.tar.gz 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
        cd - > /dev/null
    else
        warn "Backup creation failed"
    fi
}

# 2. Preserve important data
preserve_data() {
    log "Preserving important data..."
    
    # Preserve environment file
    if [ -f ".env" ]; then
        cp .env .env.preserve
        log "Environment file preserved"
    fi
    
    # Preserve uploads directory
    if [ -d "writable/uploads" ]; then
        cp -r writable/uploads uploads.preserve
        log "Uploads directory preserved"
    fi
    
    # Preserve logs directory
    if [ -d "writable/logs" ]; then
        cp -r writable/logs logs.preserve
        log "Logs directory preserved"
    fi
    
    # Preserve cache if needed (some cache might contain important data)
    if [ -d "writable/cache" ] && [ "$(ls -A writable/cache 2>/dev/null)" ]; then
        cp -r writable/cache cache.preserve
        log "Cache directory preserved"
    fi
}

# 3. Extract deployment
extract_deployment() {
    log "Extracting new deployment..."
    
    if [ ! -f "deployment.tar.gz" ]; then
        error "deployment.tar.gz not found!"
    fi
    
    tar -xzf deployment.tar.gz
    log "New deployment extracted"
}

# 4. Restore preserved data
restore_data() {
    log "Restoring preserved data..."
    
    # Restore environment file
    if [ -f ".env.preserve" ]; then
        mv .env.preserve .env
        log "Environment file restored"
    fi
    
    # Restore uploads directory
    if [ -d "uploads.preserve" ]; then
        rm -rf writable/uploads 2>/dev/null || true
        mv uploads.preserve writable/uploads
        log "Uploads directory restored"
    fi
    
    # Restore logs directory
    if [ -d "logs.preserve" ]; then
        rm -rf writable/logs 2>/dev/null || true
        mv logs.preserve writable/logs
        log "Logs directory restored"
    fi
    
    # Restore cache if preserved
    if [ -d "cache.preserve" ]; then
        rm -rf writable/cache 2>/dev/null || true
        mv cache.preserve writable/cache
        log "Cache directory restored"
    fi
}

# 5. Set permissions
set_permissions() {
    log "Setting proper permissions..."
    
    # Set general permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    # Set executable permissions for spark
    if [ -f "spark" ]; then
        chmod +x spark
    fi
    
    # Set writable permissions
    chmod -R 777 writable/
    
    # Set specific permissions for certain directories
    if [ -d "public" ]; then
        chmod -R 755 public/
    fi
    
    log "Permissions set successfully"
}

# 6. Clear application cache
clear_cache() {
    log "Clearing application cache..."
    
    # Clear CodeIgniter cache using spark
    if [ -f "spark" ]; then
        php spark cache:clear 2>/dev/null || warn "Could not clear cache using spark"
    fi
    
    # Manual cache clearing
    if [ -d "writable/cache" ]; then
        find writable/cache -name "*.php" -type f -delete 2>/dev/null || true
        log "Manual cache clearing completed"
    fi
}

# 7. Run database migrations (if needed)
run_migrations() {
    if [ "$RUN_MIGRATIONS" = "true" ] && [ -f "spark" ]; then
        log "Running database migrations..."
        php spark migrate 2>/dev/null || warn "Database migrations failed"
    fi
}

# 8. Verify deployment
verify_deployment() {
    log "Verifying deployment..."
    
    # Check critical files
    critical_files=("public/index.php" "app/Config/App.php" "system/CodeIgniter.php")
    for file in "${critical_files[@]}"; do
        if [ ! -f "$file" ]; then
            error "Critical file missing: $file"
        fi
    done
    
    # Check critical directories
    critical_dirs=("app" "system" "vendor" "public" "writable")
    for dir in "${critical_dirs[@]}"; do
        if [ ! -d "$dir" ]; then
            error "Critical directory missing: $dir"
        fi
    done
    
    # Check if composer dependencies are installed
    if [ ! -d "vendor/codeigniter" ]; then
        error "CodeIgniter vendor files missing"
    fi
    
    # Check PHP syntax of main files
    php -l public/index.php > /dev/null || error "Syntax error in index.php"
    
    log "Deployment verification passed!"
}

# 9. Cleanup
cleanup() {
    log "Cleaning up..."
    
    # Remove deployment archive
    rm -f deployment.tar.gz
    
    # Remove any temporary preserve files (in case of error)
    rm -f .env.preserve 2>/dev/null || true
    rm -rf uploads.preserve logs.preserve cache.preserve 2>/dev/null || true
    
    log "Cleanup completed"
}

# Main deployment process
main() {
    log "=== YBB Admin System Deployment Started ==="
    
    # Check for deployment archive
    if [ ! -f "deployment.tar.gz" ]; then
        error "deployment.tar.gz not found in current directory"
    fi
    
    # Execute deployment steps
    create_backup
    preserve_data
    extract_deployment
    restore_data
    set_permissions
    clear_cache
    run_migrations
    verify_deployment
    cleanup
    
    log "=== YBB Admin System Deployment Completed Successfully ==="
    log "Application should be available at your configured domain"
}

# Handle script termination
trap 'error "Deployment interrupted"' INT TERM

# Execute main function
main "$@"