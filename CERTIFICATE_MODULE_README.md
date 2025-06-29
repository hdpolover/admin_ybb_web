# Certificate Module for CodeIgniter 4

This certificate module provides a complete system for managing awards and certificates for participants in programs. The module includes 5 database tables with their corresponding models and controllers.

## Database Tables

### 1. program_awards
Defines the awards available for each program.
- `id` (INT, Primary Key)
- `program_id` (INT) - Reference to programs table
- `title` (VARCHAR(255)) - Award title
- `description` (TEXT) - Award description
- `award_type` (ENUM: 'winner', 'runner_up', 'mention', 'other')
- `order_number` (INT) - Display order
- `is_active`, `is_deleted` (TINYINT)
- `created_at`, `updated_at` (DATETIME)

### 2. program_certificates
Certificate templates for awards.
- `id` (INT, Primary Key)
- `program_id` (INT) - Reference to programs table
- `award_id` (INT) - Reference to program_awards table
- `template_url` (VARCHAR(512)) - URL to certificate template image
- `issue_date` (DATE) - Date when certificate is issued
- `published_at` (DATETIME) - When certificate was published
- `is_active`, `is_deleted` (TINYINT)
- `created_at`, `updated_at` (DATETIME)

### 3. program_certificate_content_blocks
Define text and placeholder positions on certificates.
- `id` (INT, Primary Key)
- `certificate_id` (INT) - Reference to program_certificates table
- `type` (ENUM: 'text', 'placeholder') - Block type
- `value` (TEXT) - Text content or placeholder name
- `x`, `y` (INT) - Position coordinates
- `font_size` (INT) - Font size
- `font_family` (VARCHAR(100)) - Font family
- `font_weight` (ENUM: 'normal', 'bold') - Font weight
- `text_align` (ENUM: 'left', 'center', 'right') - Text alignment
- `color` (VARCHAR(10)) - Text color (hex code)
- `is_active`, `is_deleted` (TINYINT)
- `created_at`, `updated_at` (DATETIME)

### 4. participant_awards
Tracks which participants have received which awards.
- `id` (INT, Primary Key)
- `participant_id` (INT) - Reference to participants table
- `award_id` (INT) - Reference to program_awards table
- `assigned_by` (INT) - User ID who assigned the award
- `assigned_at` (DATETIME) - When award was assigned
- `notes` (TEXT) - Additional notes
- `is_active`, `is_deleted` (TINYINT)
- `created_at`, `updated_at` (DATETIME)

### 5. participant_certificates
Tracks generated certificates for participants.
- `id` (INT, Primary Key)
- `participant_id` (INT) - Reference to participants table
- `award_id` (INT) - Reference to program_awards table
- `certificate_id` (INT) - Reference to program_certificates table
- `generated_at` (DATETIME) - When certificate was generated
- `is_active`, `is_deleted` (TINYINT)
- `created_at`, `updated_at` (DATETIME)

## Models Created

### 1. ProgramAwardModel
- **File**: `app/Models/ProgramAwardModel.php`
- **Key Methods**:
  - `getActiveAwardsByProgram($programId)` - Get awards for a specific program
  - `getAwardWithProgram($id)` - Get award with program details
  - `softDelete($id)` - Soft delete an award

### 2. ProgramCertificateModel
- **File**: `app/Models/ProgramCertificateModel.php`
- **Key Methods**:
  - `getCertificatesByProgram($programId)` - Get certificates for a program
  - `getCertificateWithDetails($id)` - Get certificate with related data
  - `getPublishedCertificates($programId)` - Get published certificates
  - `softDelete($id)` - Soft delete a certificate

### 3. ProgramCertificateContentBlockModel
- **File**: `app/Models/ProgramCertificateContentBlockModel.php`
- **Key Methods**:
  - `getContentBlocksByCertificate($certificateId)` - Get blocks for a certificate
  - `getContentBlocksByType($certificateId, $type)` - Get blocks by type
  - `updatePosition($id, $x, $y)` - Update block position
  - `deleteBlocksByCertificate($certificateId)` - Bulk delete blocks

### 4. ParticipantAwardModel
- **File**: `app/Models/ParticipantAwardModel.php`
- **Key Methods**:
  - `getParticipantAwards($participantId)` - Get awards for a participant
  - `getAwardParticipants($awardId)` - Get participants for an award
  - `hasParticipantAward($participantId, $awardId)` - Check if participant has award
  - `getParticipantAwardWithDetails($id)` - Get award with details

### 5. ParticipantCertificateModel
- **File**: `app/Models/ParticipantCertificateModel.php`
- **Key Methods**:
  - `getParticipantCertificates($participantId)` - Get certificates for a participant
  - `getCertificateParticipants($certificateId)` - Get participants for a certificate
  - `hasParticipantCertificate($participantId, $certificateId)` - Check if participant has certificate
  - `getCertificatesByAward($awardId)` - Get certificates by award

## Controllers Created

### 1. ProgramAwards
- **File**: `app/Controllers/ProgramAwards.php`
- **Endpoints**: Standard CRUD + `byProgram($programId)`

### 2. ProgramCertificates
- **File**: `app/Controllers/ProgramCertificates.php`
- **Endpoints**: Standard CRUD + `byProgram($programId)`, `publish($id)`, `published()`

### 3. ProgramCertificateContentBlocks
- **File**: `app/Controllers/ProgramCertificateContentBlocks.php`
- **Endpoints**: Standard CRUD + `byCertificate($certificateId)`, `updatePosition($id)`, `bulkCreate()`

### 4. ParticipantAwards
- **File**: `app/Controllers/ParticipantAwards.php`
- **Endpoints**: Standard CRUD + `byParticipant($participantId)`, `byAward($awardId)`, `bulkAssign()`

### 5. ParticipantCertificates
- **File**: `app/Controllers/ParticipantCertificates.php`
- **Endpoints**: Standard CRUD + `byParticipant($participantId)`, `byCertificate($certificateId)`, `byAward($awardId)`, `bulkGenerate()`

## Features

### ✅ Complete CRUD Operations
- All controllers include standard Create, Read, Update, Delete operations
- JSON responses for all endpoints
- Proper error handling and validation

### ✅ Soft Delete Support
- All models implement soft delete using `is_deleted` flag
- Controllers filter by `is_active = 1` AND `is_deleted = 0`

### ✅ Validation Rules
- Field-specific validation based on database constraints
- VARCHAR length limits enforced
- ENUM values validated
- Required fields checked

### ✅ Relationship Queries
- Models include methods to fetch related data
- JOIN queries for displaying combined information
- Efficient database queries

### ✅ Bulk Operations
- Bulk assign awards to multiple participants
- Bulk generate certificates for multiple participants
- Bulk create content blocks

### ✅ Specialized Features
- Certificate publishing system
- Content block positioning system
- Award type management
- Duplicate prevention (participant can't get same award/certificate twice)

## Route Structure

The routes are organized under `/api/` prefix with RESTful conventions:

```
GET    /api/program-awards              - List all awards
POST   /api/program-awards              - Create new award
GET    /api/program-awards/{id}         - Get specific award
PUT    /api/program-awards/{id}         - Update award
DELETE /api/program-awards/{id}         - Delete award
GET    /api/program-awards/program/{id} - Get awards for program
```

## Usage Examples

### 1. Create Award
```bash
POST /api/program-awards
{
    "program_id": 1,
    "title": "Best Innovation",
    "description": "Award for the most innovative project",
    "award_type": "winner",
    "order_number": 1
}
```

### 2. Bulk Assign Awards
```bash
POST /api/participant-awards/bulk-assign
{
    "participant_ids": [1, 2, 3],
    "award_id": 1,
    "assigned_by": 1,
    "notes": "Outstanding performance"
}
```

### 3. Generate Certificates
```bash
POST /api/participant-certificates/bulk-generate
{
    "participant_ids": [1, 2, 3],
    "certificate_id": 1,
    "award_id": 1
}
```

## Next Steps

1. **Database Migration**: Create the database tables using the schema provided
2. **Routes**: Add the suggested routes to your `app/Config/Routes.php`
3. **Testing**: Test the API endpoints using Postman or similar tools
4. **Frontend Integration**: Build admin interface to interact with these APIs
5. **Certificate Generation**: Implement actual PDF/image generation logic
6. **Permissions**: Add role-based access control as needed

## Files Created

1. **Models** (5 files):
   - `app/Models/ProgramAwardModel.php`
   - `app/Models/ProgramCertificateModel.php`
   - `app/Models/ProgramCertificateContentBlockModel.php`
   - `app/Models/ParticipantAwardModel.php`
   - `app/Models/ParticipantCertificateModel.php`

2. **Controllers** (5 files):
   - `app/Controllers/ProgramAwards.php`
   - `app/Controllers/ProgramCertificates.php`
   - `app/Controllers/ProgramCertificateContentBlocks.php`
   - `app/Controllers/ParticipantAwards.php`
   - `app/Controllers/ParticipantCertificates.php`

3. **Documentation**:
   - `certificate_module_routes.php` - Route suggestions and examples

All files follow CodeIgniter 4 conventions and best practices, with comprehensive error handling, validation, and documentation.
