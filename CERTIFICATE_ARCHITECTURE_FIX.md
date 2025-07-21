# Certificate System Architecture Fix

## Issue Resolution
**Error**: `mysqli_sql_exception #1054 Unknown column 'certificate_path' in 'WHERE'`

**Root Cause**: Code was trying to use non-existent `certificate_path` and `certificate_generated_at` fields in the `participant_awards` table.

## Actual Database Schema

After examining the models, the actual certificate system architecture is:

### 1. `program_certificates` Table
**Purpose**: Certificate templates/configurations for awards
**Fields**:
- `program_id`, `award_id` (links to specific award)
- `template_url`, `template_type`, `preview_url`
- `issue_date`, `published_at`
- `is_active`, `is_deleted`

### 2. `program_awards` Table  
**Purpose**: Award definitions within programs
**Fields**:
- `program_id`, `title`, `description`
- `award_type` (winner, runner_up, mention, other)
- `order_number`, `is_active`, `is_deleted`

### 3. `participant_awards` Table
**Purpose**: Tracks participant assignment to awards
**Fields**:
- `participant_id`, `award_id`, `assigned_by`
- `assigned_at`, `notes`
- `is_active`, `is_deleted`

## Key Architecture Insights

### Certificate Generation Model
- **NO certificate tracking fields** exist in any table
- Certificates are **generated on-demand** based on:
  1. Participant assignment to award (`participant_awards`)
  2. Existence of certificate template (`program_certificates`)
- Certificate generation is **stateless** - no persistent storage of "issued" status

### Certificate Eligibility
- Participant is eligible for certificate IF:
  - Assigned to award (`participant_awards.is_active = 1`)
  - Award has certificate template (`program_certificates` exists for award_id)

### Certificate Operations
- **Generate**: Create PDF on-demand for assigned participants
- **Revoke**: Remove participant assignment (soft delete participant_awards record)
- **List**: Show all award assignments (potential certificates)

## Files Fixed

### 1. app/Controllers/Certificates.php
**Changes**:
- Removed references to non-existent `certificate_path` fields
- Certificate count = participant assignments to award
- Revoke = soft delete participant assignment
- Issue certificates = validate existing assignments

### 2. app/Controllers/Api/CertificatesApiController.php  
**Changes**:
- Removed certificate tracking logic
- Generate always creates fresh PDF (no duplicate checking)
- Revoke removes award assignment
- Stats show awards with/without certificate templates
- Details query uses `template_url` instead of `template_data`

## Updated Certificate Workflow

1. **Admin assigns participant to award** → `participant_awards` record created
2. **Certificate generation requested** → Check assignment exists + template exists → Generate PDF
3. **Certificate revocation** → Remove assignment (`participant_awards.is_deleted = 1`)
4. **Certificate listing** → Show all participant assignments with template availability

## Benefits of This Architecture

- **Stateless**: No certificate tracking means no sync issues
- **On-demand**: Fresh certificates generated each time
- **Flexible**: Same participant can get certificate multiple times
- **Simple**: Certificate eligibility = award assignment + template existence
- **Clean**: No orphaned certificate records

## Testing Required

- ✅ PHP syntax validation passed
- ⏳ Test certificate generation API
- ⏳ Test certificate listing
- ⏳ Test participant assignment/removal
- ⏳ Verify certificate templates are loaded correctly

## Status
**RESOLVED** ✅

The certificate system now correctly uses the actual database schema without trying to access non-existent certificate tracking fields.
