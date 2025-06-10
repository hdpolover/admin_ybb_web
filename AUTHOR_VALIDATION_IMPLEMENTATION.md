# Author Validation Implementation

## Overview
This implementation ensures that one participant can only be assigned to one abstract at a time within the same program. The validation is based on email and program_id as requested.

## Changes Made

### 1. AbstractAuthorModel.php
**Added new method:** `checkAuthorEmailInProgram()`

```php
/**
 * Check if an author email is already assigned to any abstract within the same program
 * @param string $email
 * @param int $program_id
 * @param int|null $exclude_abstract_id Abstract ID to exclude from check (for updates)
 * @return object|null
 */
public function checkAuthorEmailInProgram($email, $program_id, $exclude_abstract_id = null)
```

This method:
- Searches for authors by email within a specific program
- Joins with abstracts table to get program_id
- Excludes deleted/inactive records
- Optionally excludes a specific abstract (useful for updates)
- Returns the conflicting author record or null if no conflict

### 2. AbstractsApiController.php
**Modified:** `addAbstractAuthor()` method

Added validation logic that:
- Checks if the author email is already assigned to any abstract in the same program
- Returns appropriate error response if conflict is found
- Maintains existing validation for same abstract

**Added new endpoint:** `validateAuthorForAbstract()`

```php
/**
 * Validate if an author can be added to an abstract
 * POST /api/abstracts/{abstract_id}/authors/validate
 */
public function validateAuthorForAbstract($abstract_id)
```

This endpoint:
- Validates email format
- Checks for conflicts within the same program
- Checks for conflicts within the same abstract
- Returns detailed validation response
- Can be called before showing add author form

### 3. Routes.php
**Added new route:**
```php
$routes->post('(:num)/authors/validate', 'AbstractsApiController::validateAuthorForAbstract/$1');
```

## API Endpoints

### Validate Author (New)
**POST** `/api/abstracts/{abstract_id}/authors/validate`

**Request:**
```json
{
    "email": "author@example.com"
}
```

**Response (Success):**
```json
{
    "status": "success",
    "message": "Author can be added to this abstract",
    "data": {
        "can_add": true,
        "email": "author@example.com",
        "abstract_id": 123,
        "program_id": 2
    }
}
```

**Response (Error - Email already in program):**
```json
{
    "status": "error",
    "message": "This author email is already assigned to another abstract in the same program. One participant can only be assigned to one abstract at a time per program.",
    "data": {
        "can_add": false,
        "existing_abstract_id": 456,
        "conflict_reason": "email_already_in_program"
    }
}
```

### Add Author (Modified)
**POST** `/api/abstracts/{abstract_id}/authors`

**Request:**
```json
{
    "full_name": "John Doe",
    "institution": "University XYZ",
    "email": "john@example.com",
    "is_participant": 1,
    "participant_id": 123
}
```

**Response (Error - Conflict):**
```json
{
    "status": "error",
    "message": "This author email is already assigned to another abstract (ID: 456) in the same program. One participant can only be assigned to one abstract at a time per program."
}
```

## Usage Examples

### Frontend Validation Flow
1. **Pre-validation:** Call `/api/abstracts/{id}/authors/validate` when user enters email
2. **Show feedback:** Display appropriate message based on validation response
3. **Submit form:** Only if validation passes, call `/api/abstracts/{id}/authors`

### Backend Validation
The `addAbstractAuthor` method now automatically validates:
1. Email is not already in the same abstract
2. Email is not already assigned to any abstract within the same program
3. All existing validations (participant eligibility, etc.)

## Validation Logic

### Program-Level Uniqueness
- One email can only be assigned to one abstract per program
- Different programs can have the same email as author
- Validation checks active and non-deleted records only

### Abstract-Level Uniqueness
- One email can only be assigned once per abstract
- Prevents duplicate authors within the same abstract

### Participant Restrictions
- Participants (is_participant=1) still have additional restrictions
- Primary participants and authors are tracked separately

## Error Handling

All validation errors return appropriate HTTP status codes:
- `409 Conflict` - When email already exists in program/abstract
- `400 Bad Request` - Invalid data format
- `404 Not Found` - Abstract doesn't exist
- `403 Forbidden` - Participant not eligible

## Benefits

1. **Data Integrity:** Prevents duplicate participants across abstracts
2. **Clear Error Messages:** Users understand why validation failed
3. **Flexible API:** Pre-validation endpoint for better UX
4. **Backward Compatible:** Existing functionality unchanged
5. **Program Isolation:** Same participant can join different programs

## Testing

To test the implementation:

1. **Test duplicate prevention:**
   - Add an author to abstract A in program 1
   - Try to add same email to abstract B in program 1
   - Should get 409 error

2. **Test cross-program allowance:**
   - Add an author to abstract A in program 1
   - Add same email to abstract C in program 2
   - Should succeed

3. **Test validation endpoint:**
   - Call validate endpoint before adding
   - Verify response matches actual add attempt

## Notes

- The implementation uses email as the unique identifier as requested
- Program isolation is maintained (same email can be in different programs)
- The validation is performed at the database level for reliability
- All changes are backward compatible with existing functionality
