# MANUAL DATABASE UTF8MB4 CONVERSION GUIDE

## ⚠️ IMPORTANT: READ THIS FIRST

**This guide will permanently change your database encoding. Make sure you:**
1. Have a complete database backup
2. Test on a staging environment first (if possible)
3. Have downtime planned for production
4. Understand the risks involved

## 📋 PRE-CONVERSION CHECKLIST

- [ ] Database backup completed
- [ ] Application temporarily offline (recommended)
- [ ] MySQL user has ALTER privileges
- [ ] Sufficient disk space available
- [ ] Team notified of maintenance

## 🛠️ CONVERSION STEPS

### Step 1: Create Database Backup

```bash
# Option 1: Using mysqldump (if available)
mysqldump -h 194.163.42.101 -u u1437096_ybb_master_app_admin_user -p u1437096_ybb_master_app_db > backup_before_utf8mb4.sql

# Option 2: Use the PHP backup script
php convert_database_to_utf8mb4.php
# (This will create backup first before converting)
```

### Step 2: Check Current Database Encoding

```sql
-- Connect to your database and run:
SELECT @@character_set_database, @@collation_database;

-- Check table encodings:
SELECT 
    TABLE_NAME, 
    TABLE_COLLATION 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'u1437096_ybb_master_app_db'
AND TABLE_COLLATION LIKE 'latin1%';
```

### Step 3: Convert Database

```sql
-- Convert the database
ALTER DATABASE u1437096_ybb_master_app_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Convert Critical Tables

```sql
-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Convert critical tables (adjust table names as needed)
ALTER TABLE participants 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE participant_essays 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE program_essays 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE users 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE programs 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE payments 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE participant_statuses 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
```

### Step 5: Update CodeIgniter Configuration

Edit `app/Config/Database.php`:

```php
// Change both 'default' and 'export' configurations:
public array $default = [
    // ... other settings ...
    'charset'  => 'utf8mb4',          // Changed from 'utf8'
    'DBCollat' => 'utf8mb4_unicode_ci', // Changed from 'utf8_general_ci'
    // ... other settings ...
];

public array $export = [
    // ... other settings ...
    'charset'  => 'utf8mb4',          // Changed from 'utf8'
    'DBCollat' => 'utf8mb4_unicode_ci', // Changed from 'utf8_general_ci'
    // ... other settings ...
];
```

### Step 6: Verify Conversion

```sql
-- Check database encoding
SELECT @@character_set_database, @@collation_database;

-- Check table encodings
SELECT 
    TABLE_NAME, 
    TABLE_COLLATION 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'u1437096_ybb_master_app_db'
AND TABLE_NAME IN ('participants', 'participant_essays', 'program_essays', 'users');

-- Test with some sample data
SELECT id, full_name, experiences 
FROM participants 
WHERE experiences IS NOT NULL 
LIMIT 5;
```

## 🧪 POST-CONVERSION TESTING

1. **Test Application Login:**
   - Verify users can log in
   - Check if all pages load correctly

2. **Test Data Integrity:**
   - Check participant names display correctly
   - Verify essay content shows properly
   - Look for any unexpected characters

3. **Test Excel Exports:**
   - Export a small dataset
   - Verify Excel file opens without corruption
   - Check if Unicode characters display correctly

4. **Monitor Logs:**
   - Check for any database connection errors
   - Monitor for encoding-related issues

## 🚨 ROLLBACK PROCEDURE (IF NEEDED)

If something goes wrong:

```sql
-- Restore from backup
mysql -h 194.163.42.101 -u u1437096_ybb_master_app_admin_user -p u1437096_ybb_master_app_db < backup_before_utf8mb4.sql

-- Or revert database encoding
ALTER DATABASE u1437096_ybb_master_app_db 
CHARACTER SET latin1 COLLATE latin1_swedish_ci;

-- Revert table encodings
ALTER TABLE participants 
CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci;
-- (repeat for other tables)
```

Then revert the CodeIgniter config file changes.

## 📊 EXPECTED RESULTS

**After Successful Conversion:**

✅ **New Data:**
- Unicode characters stored correctly
- No more corruption for new entries
- Proper emoji and special character support

✅ **Excel Exports:**
- Files open without corruption errors
- Clean data export (combined with cleaning function)
- Better handling of international characters

⚠️ **Existing Corrupted Data:**
- Previously corrupted data may still show '?' symbols
- This is expected and normal
- The export cleaning function will handle these gracefully

## 🔧 TROUBLESHOOTING

### "Foreign key constraint fails" Error:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Run your ALTER statements
SET FOREIGN_KEY_CHECKS = 1;
```

### "Table doesn't exist" Error:
- Check table names are correct
- Verify you have proper permissions

### Application Connection Errors:
- Ensure CodeIgniter config is updated
- Restart web server if needed
- Check database connection settings

### Still Getting Corrupted Exports:
- The data cleaning function should handle old corrupted data
- Check logs for cleaning messages
- May need additional cleaning rules for specific corruption patterns

## 📞 SUPPORT

If you encounter issues:
1. Check the backup was created successfully
2. Review any error messages carefully
3. Test with a small subset of data first
4. Consider reverting and trying again in smaller steps

The conversion should resolve your Excel export corruption issues permanently for all new data.
