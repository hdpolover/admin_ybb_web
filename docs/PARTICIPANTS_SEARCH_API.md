# Participants Search API Endpoint

## Overview
A new endpoint has been added to search participants by custom parameters with joins to the users table.

## Endpoint
```
GET /api/participants/search
```

## Description
This endpoint allows you to search for participants using multiple parameters. It joins the `participants` and `users` tables to provide comprehensive search functionality. The endpoint returns a single participant object if only one result is found, or a paginated list if multiple results are found.

## Parameters

All parameters are optional, but **at least one parameter must be provided**:

### Search Parameters:
- `email` (string) - Filter by user email (exact match)
- `full_name` (string) - Filter by participant full name (partial match)
- `user_full_name` (string) - Filter by user full name (partial match)
- `program_id` (integer) - Filter by program ID (exact match)
- `program_category_id` (integer) - Filter by program category ID (exact match)
- `gender` (string) - Filter by gender (exact match)
- `phone_number` (string) - Filter by phone number (partial match)
- `nationality` (string) - Filter by nationality (partial match)
- `institution` (string) - Filter by institution (partial match)
- `occupation` (string) - Filter by occupation (partial match)
- `category` (string) - Filter by category (exact match)
- `is_verified` (integer) - Filter by user verification status (0 or 1)

### Pagination Parameters:
- `page` (integer) - Page number (default: 1)
- `limit` (integer) - Items per page (default: 10)

### Optional Include Parameters:
- `include` (string) - Comma-separated list of related data to include
  - `essays` - Include participant essays
  - `payments` - Include participant payments
  - Example: `include=essays,payments`

## Examples

### Search by email:
```
GET /api/participants/search?email=john.doe@example.com
```

### Search by partial name:
```
GET /api/participants/search?full_name=John
```

### Search by multiple parameters:
```
GET /api/participants/search?program_id=5&gender=male&page=1&limit=20
```

### Search by program category:
```
GET /api/participants/search?program_category_id=2
```

### Multiple parameters with program category:
```
GET /api/participants/search?program_category_id=2&gender=female&page=1&limit=20
```

### Search with additional related data:
```
GET /api/participants/search?program_id=5&include=essays,payments
```

## Response Format

### Single Result (when only 1 participant found):
```json
{
    "status": "success",
    "code": 200,
    "message": "Participant found",
    "data": {
        "id": 123,
        "user_id": 456,
        "full_name": "John Doe",
        "program_id": 5,        "gender": "male",
        "phone_number": "+1234567890",
        "nationality": "USA",
        "user": {
            "id": 456,
            "full_name": "John Doe",
            "email": "john.doe@example.com",
            "is_verified": 1,
            "program_category_id": 2,
            "is_active": 1,
            "created_at": "2025-01-01 12:00:00",
            "updated_at": "2025-01-02 15:30:00"
        },
        // ... other participant fields
    },
    "meta": {
        "total_results": 1
    }
}
```

### Multiple Results (when more than 1 participant found):
```json
{
    "status": "success",
    "code": 200,
    "message": "Participants found",
    "data": [
        {
            "id": 123,
            "user_id": 456,            "full_name": "John Doe",
            "user": {
                "id": 456,
                "full_name": "John Doe",
                "email": "john.doe@example.com",
                "is_verified": 1,
                "program_category_id": 2,
                "is_active": 1,
                "created_at": "2025-01-01 12:00:00",
                "updated_at": "2025-01-02 15:30:00"
            },
            // ... other participant fields
        },
        // ... more participants
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total_items": 25,
        "total_pages": 3
    }
}
```

### Error Response (no search parameters):
```json
{
    "status": "error",
    "code": 400,
    "message": "At least one search parameter is required"
}
```

### Error Response (no results found):
```json
{
    "status": "error",
    "code": 404,
    "message": "No participants found matching the search criteria"
}
```

## Features

1. **Flexible Search**: Support for multiple search parameters that can be combined
2. **Partial Matching**: Text fields support partial matching (LIKE queries)
3. **User Data Integration**: Automatically includes related user data in the response
4. **Smart Response Format**: Returns single object for one result, paginated list for multiple results
5. **Security**: Only returns active participants and users (is_active=1, is_deleted=0)
6. **Performance**: Uses joins instead of multiple queries for better performance

## Implementation Details

- **Controller**: `App\Controllers\Api\ParticipantsApiController::search()`
- **Model Method**: `App\Models\ParticipantModel::searchParticipants()`
- **Route**: `GET /api/participants/search`
- **Tables Joined**: `participants` INNER JOIN `users`
