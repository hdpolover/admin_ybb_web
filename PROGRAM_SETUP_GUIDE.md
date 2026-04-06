# Korea Youth Summit 2026 Batch 2 - Program Setup Guide

This guide explains how to use the automated CLI commands to create and manage programs in the YBB system.

## Available Commands

### 1. Create New Program

Creates a new program with all related database records automatically.

```bash
# Interactive mode (prompts for batch and year)
php spark program:create

# Quick mode with options
php spark program:create --batch=2 --year=2026

# Skip creating payments (useful if you want to add them manually)
php spark program:create --skip-payments
```

**What this command creates:**
- Program Type (if not exists): "Summit"
- Program Category: "Korea Youth Summit 2026"
- Program: "Korea Youth Summit 2026 Batch 2"
- Web Settings for the category
- Default Payment Records:
  - Self Funded: IDR 15,000,000 / USD 950
  - Fully Funded: IDR 500,000 / USD 35 (processing fee)
- Default Payment Periods (Early Bird, Application Period)
- Default Schedules (Registration, Selection, Program Execution, Post-Program)
- Default Essay Questions (4 questions)

### 2. List Existing Programs

View all programs and categories with their details.

```bash
# List all programs and categories
php spark program:list

# Filter by specific category
php spark program:list --category=5

# Show payment details
php spark program:list --payments

# Combine options
php spark program:list --category=5 --payments
```

### 3. Clone Existing Program

Clone an existing program with all its related data (useful for creating a new batch based on previous one).

```bash
# Interactive mode
php spark program:clone

# Direct mode
php spark program:clone --source=5 --name="Korea Youth Summit 2026 Batch 2"

# Auto-suggest batch number
php spark program:clone --source=5 --batch=2
```

**What gets cloned:**
- ✓ Program record (new ID, starts inactive)
- ✓ Payment records (starts inactive)
- ✓ Payment periods
- ✓ Schedules
- ✓ Essay questions
- ✓ Subthemes
- ✓ Speakers
- ✓ Documents
- ✓ Rundowns
- ✓ Awards
- ✓ FAQs
- ✓ Announcements (starts inactive)

## Quick Start for Korea Youth Summit 2026 Batch 2

### Option A: Create from Scratch (Recommended for new programs)

```bash
cd admin_ybb_web
php spark program:create --batch=2 --year=2026
```

Then update the created records through the admin panel with specific details like:
- Banner images
- Program descriptions
- Payment amounts (if different from defaults)
- Speaker information
- FAQ entries

### Option B: Clone from Previous Batch (Recommended if Batch 1 exists)

```bash
cd admin_ybb_web

# First, list existing programs to find Batch 1 ID
php spark program:list

# Then clone from that ID
php spark program:clone --source=<BATCH_1_ID> --batch=2
```

## After Creating the Program

1. **Update Program Details**
   - Go to Admin Panel → Programs
   - Edit the new program
   - Upload banner image
   - Update description, guidelines, theme

2. **Configure Payments**
   - Go to Admin Panel → Program Payments
   - Update amounts if needed
   - Adjust payment period dates
   - Activate payments when ready

3. **Add Content**
   - Speakers
   - FAQ entries
   - Announcements
   - Program rundowns (detailed schedule)

4. **Activate Program**
   - Set `is_active` to 1
   - Set `is_registration_open` to 1 (when ready to accept registrations)

5. **Test Registration Flow**
   - Register a test participant
   - Verify payment flow
   - Check email notifications

## Database Tables Affected

### Core Tables
- `program_types` - Program type definitions
- `program_categories` - Category information and web settings
- `programs` - Main program records
- `web_settings` - Category-specific settings

### Payment Tables
- `program_payments` - Payment definitions
- `program_payment_periods` - Payment date periods

### Content Tables
- `program_schedules` - Program timeline/schedules
- `program_essays` - Essay questions
- `program_subthemes` - Subthemes/topics
- `program_speakers` - Speaker information
- `program_documents` - Required documents
- `program_rundowns` - Detailed event schedule
- `program_awards` - Awards/certificates
- `program_faqs` - FAQ entries
- `program_announcements` - Announcements

## Troubleshooting

### Command Not Found
Make sure you're in the `admin_ybb_web` directory:
```bash
cd /path/to/admin_ybb_web
php spark program:create
```

### Database Connection Error
Check your `.env` file:
```env
database.default.hostname = localhost
database.default.database = your_database
database.default.username = your_username
database.default.password = your_password
```

### Duplicate Entry Errors
If you get duplicate key errors, the program/category might already exist. Use the `program:list` command to check existing records.

### Transaction Rollback
If the command fails midway, it will automatically rollback all changes. Check the error message and fix the issue before retrying.

## Customization

### Modifying Default Values

Edit the command files to change default values:

1. **Payment Amounts**: Edit `app/Commands/CreateProgram.php`
   - Look for `createProgramPayments()` method
   - Modify `idr_amount` and `usd_amount` values

2. **Essay Questions**: Edit `app/Commands/CreateProgram.php`
   - Look for `createDefaultEssays()` method
   - Modify the `$essays` array

3. **Schedule Dates**: Edit `app/Commands/CreateProgram.php`
   - Look for `createDefaultSchedules()` method
   - Modify `start_date` and `end_date` values

## Need Help?

Run any command with `--help` for usage information:
```bash
php spark program:create --help
php spark program:clone --help
php spark program:list --help
```
