# Abstract Papers Subtheme Filter Implementation

## Overview
Modified the abstracts papers index page to replace the review status dropdown with a subthemes dropdown filter. This allows reviewers to filter abstracts by their assigned subthemes.

## Changes Made

### 1. Controller Changes (`app/Controllers/AbstractPapers.php`)

#### Added New Method: `getReviewerSubthemes()`
- Gets the current reviewer's assigned subthemes for filtering
- For admin users: returns all program subthemes
- For reviewer users: returns only assigned subthemes
- Returns JSON response with subtheme data

#### Modified Method: `getAbstractsByProgram()`
- Added support for subtheme filtering via `subtheme_id` GET parameter
- Joins with `participant_subthemes` table to filter by participant's assigned subtheme
- Maintains backward compatibility when no filter is applied

#### Added Property: `$programSubthemeModel`
- Instantiated `ProgramSubthemeModel` for accessing subtheme data

### 2. View Changes (`app/Views/submissions/abstract-paper/index.php`)

#### Added Subtheme Filter Dropdown
- Placed in the card header next to the page title
- Styled with custom CSS for better appearance
- Shows "All Assigned Subthemes" as default option

#### Added JavaScript Functions
- `loadReviewerSubthemes()`: Loads reviewer's subthemes via AJAX
- Enhanced filter change handler to update DataTable and show active filter in title
- Added loading states and error handling

#### Added CSS Styling
- `.subtheme-filter-container`: Container styling for the filter
- `#subthemeFilter`: Dropdown specific styling
- Responsive design considerations

### 3. Route Changes (`app/Config/Routes/Admin.php`)

#### Added New Route
```php
$routes->get('getReviewerSubthemes', 'AbstractPapers::getReviewerSubthemes');
```

## Features

### For Reviewers
- Dropdown shows only subthemes assigned to the current reviewer
- Filters abstracts to show only those from participants in the selected subtheme
- Visual indicator in page title when filter is active

### For Admins
- Dropdown shows all subthemes for the current program
- Can filter by any subtheme to review specific areas

### User Experience
- Loading states during AJAX calls
- Error handling for network issues
- Disabled state when no subthemes are available
- Clear visual feedback when filter is applied

## Technical Details

### Session Dependencies
- `reviewerId`: For reviewer-specific subtheme filtering
- `adminId`: To detect admin users
- `current_program`: To scope subthemes to current program

### Database Relationships
- Uses `abstract_reviewer_subthemes` table for reviewer assignments
- Joins `participant_subthemes` to filter abstracts by participant's subtheme
- Leverages `program_subthemes` for subtheme metadata

### API Endpoints
- `GET /submissions/abstracts-papers/getReviewerSubthemes`: Get reviewer's subthemes
- `GET /submissions/abstracts-papers/getAbstractsByProgram?subtheme_id=X`: Get filtered abstracts

## Testing
To test the implementation:
1. Login as a reviewer with assigned subthemes
2. Navigate to `/submissions/abstracts-papers`
3. Verify dropdown shows assigned subthemes
4. Select a subtheme and verify table filters correctly
5. Check that page title shows active filter
6. Test with admin user to ensure all subthemes appear

## Backward Compatibility
- Existing functionality remains unchanged when no filter is applied
- Admin users can still see all abstracts when "All Assigned Subthemes" is selected
- All existing routes and methods continue to work as before
