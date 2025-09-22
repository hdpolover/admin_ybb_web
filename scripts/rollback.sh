#!/bin/bash

# YBB Admin System Rollback Script
# This script handles rollback to a previous deployment

set -e

# Configuration
DEPLOYMENT_PATH="${VPS_PATH:-/var/www/ybb-admin}"
BACKUP_DIR="${DEPLOYMENT_PATH}/backups"
LOG_FILE="${DEPLOYMENT_PATH}/rollback.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Logging function
log() {
    echo -e "${2:-$GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    log "ERROR: $1" "$RED"
    exit 1
}

warn() {
    log "WARNING: $1" "$YELLOW"
}

# List available backups
list_backups() {
    log "Available backups:"
    if [ -d "$BACKUP_DIR" ]; then
        ls -la "$BACKUP_DIR"/backup_*.tar.gz 2>/dev/null | awk '{print NR". "$9" ("$5" bytes, "$6" "$7" "$8")"}' || log "No backups found"
    else
        log "Backup directory not found: $BACKUP_DIR"
    fi
}

# Rollback to specific backup
rollback() {
    local backup_file="$1"
    
    if [ -z "$backup_file" ]; then
        error "No backup file specified"
    fi
    
    if [ ! -f "$backup_file" ]; then
        error "Backup file not found: $backup_file"
    fi
    
    log "Rolling back to: $backup_file"
    
    # Create backup of current state before rollback
    log "Creating backup of current state..."
    timestamp=$(date +%Y%m%d_%H%M%S)
    current_backup="${BACKUP_DIR}/pre_rollback_${timestamp}.tar.gz"
    
    tar -czf "$current_backup" \
        --exclude='writable/logs/*' \
        --exclude='writable/cache/*' \
        --exclude='writable/session/*' \
        --exclude='writable/debugbar/*' \
        --exclude='writable/uploads/temp/*' \
        app/ public/ system/ vendor/ spark composer.* *.php .env 2>/dev/null || true
    
    # Preserve current uploads and logs
    if [ -d "writable/uploads" ]; then
        cp -r writable/uploads uploads.rollback.preserve
        log "Current uploads preserved"
    fi
    
    if [ -d "writable/logs" ]; then
        cp -r writable/logs logs.rollback.preserve
        log "Current logs preserved"
    fi
    
    # Extract the backup
    log "Extracting backup..."
    tar -xzf "$backup_file"
    
    # Restore current uploads and logs
    if [ -d "uploads.rollback.preserve" ]; then
        rm -rf writable/uploads 2>/dev/null || true
        mv uploads.rollback.preserve writable/uploads
        log "Current uploads restored"
    fi
    
    if [ -d "logs.rollback.preserve" ]; then
        rm -rf writable/logs 2>/dev/null || true
        mv logs.rollback.preserve writable/logs
        log "Current logs restored"
    fi
    
    # Set permissions
    chmod -R 755 .
    chmod -R 777 writable/
    
    # Clear cache
    if [ -f "spark" ]; then
        php spark cache:clear 2>/dev/null || true
    fi
    
    log "Rollback completed successfully!"
}

# Show usage
usage() {
    echo "Usage: $0 [command] [backup_file]"
    echo ""
    echo "Commands:"
    echo "  list                 - List available backups"
    echo "  rollback <file>      - Rollback to specific backup file"
    echo "  latest               - Rollback to latest backup"
    echo ""
    echo "Examples:"
    echo "  $0 list"
    echo "  $0 rollback /path/to/backup_20250920_143022.tar.gz"
    echo "  $0 latest"
}

# Main function
main() {
    local command="$1"
    local backup_file="$2"
    
    case "$command" in
        "list")
            list_backups
            ;;
        "rollback")
            if [ -z "$backup_file" ]; then
                error "Please specify backup file path"
            fi
            rollback "$backup_file"
            ;;
        "latest")
            latest_backup=$(ls -t "$BACKUP_DIR"/backup_*.tar.gz 2>/dev/null | head -n1)
            if [ -z "$latest_backup" ]; then
                error "No backups found"
            fi
            rollback "$latest_backup"
            ;;
        *)
            usage
            exit 1
            ;;
    esac
}

# Check if we're in the right directory
if [ ! -d "app" ] && [ ! -d "public" ]; then
    error "Not in application directory!"
fi

# Execute main function
main "$@"