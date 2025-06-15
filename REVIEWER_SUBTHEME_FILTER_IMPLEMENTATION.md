# Reviewer Abstract Papers Subtheme Filter Implementation

## Overview
Modified the reviewer abstract papers page to replace the "Abstract Status" dropdown with a subtheme filter dropdown, while keeping the "Review Status" filter. This allows reviewers to filter abstracts by their assigned subthemes.

## Changes Made

### 1. View Changes (`app/Views/reviewers/abstracts-papers/index.php`)

#### Replaced Filter Dropdowns
**Before:**
- Review Status (pending/completed)
- Abstract Status (submitted/under_review/accepted)

**After:**
- **Subtheme Filter** (reviewer's assigned subthemes)
- Review Status (pending/completed)

#### Added Visual Enhancements
- Filter indicator showing active filters below page title
- Better responsive layout (col-md-4 instead of col-md-3)
- Enhanced CSS styling for filter section
- Dynamic loading of subthemes from server data

#### Updated JavaScript
- `loadReviewerSubthemes()`: Populates subtheme dropdown from PHP data
- Enhanced filter change handlers with visual feedback
- `updateFilterIndicator()`: Shows active filters in the header
- Updated AJAX data to send `subtheme_filter` instead of `abstract_status_filter`

### 2. Controller Changes (`app/Controllers/Reviewers/AbstractsPapers.php`)

#### Modified getData() Method
- Replaced `abstract_status_filter` with `subtheme_filter` parameter
- Updated filter processing logic
- Maintains compatibility with existing review status filtering

### 3. Model Changes (`app/Models/AbstractFeedbackModel.php`)

#### Enhanced getFeedbacksByReviewer() Method
- Added `subtheme_id` filter support
- Filters by `ps_link.program_subtheme_id` when subtheme is selected
- Maintains existing functionality for status-based filtering

## Additional Improvements Made

### Always Show All Abstracts
- **Previous Behavior**: Only showed pending abstracts when "Pending" was selected, and only completed when "Completed" was selected
- **New Behavior**: All assigned abstracts are always visible in the table, with filtering options to focus on specific review statuses
- **Benefit**: Reviewers can see their complete workload and easily switch between reviewing new abstracts and editing existing reviews

### Enhanced Review Management
- **Edit Reviews**: Reviewers can now edit their existing feedback, not just submit new reviews
- **Visual Indicators**: 
  - "Add Review" button (blue) for abstracts without feedback
  - "Edit Review" button (yellow/warning) for abstracts with existing feedback
  - Review completion date shown in status badge
- **Table Header**: Changed "Status" to "Review Status" for clarity

### Filter Logic Updates
- **"All Status"**: Shows all abstracts regardless of feedback status
- **"Pending"**: Shows only abstracts without reviewer feedback
- **"Completed"**: Shows only abstracts with reviewer feedback
- **Subtheme Filtering**: Works independently and can be combined with review status filtering

## Code Changes Summary

### Model (`AbstractFeedbackModel.php`)
```php
// Removed restrictive feedback filtering when status is 'all'
if (isset($filters['status'])) {
    if ($filters['status'] === 'completed') {
        $builder->where('af.feedback IS NOT NULL');
        $builder->where('af.feedback !=', '');
    } elseif ($filters['status'] === 'pending') {
        $builder->where('(af.feedback IS NULL OR af.feedback = "")');
    }
    // If status is 'all' - show all abstracts regardless of feedback status
}
```

### Controller (`Reviewers/AbstractsPapers.php`)
```php
// Updated action buttons to always show review option
if (empty($abstract->feedback)) {
    // "Add Review" button (blue)
    $buttons .= '<a href="..." class="btn btn-sm btn-outline-primary" title="Add Review">
} else {
    // "Edit Review" button (yellow)
    $buttons .= '<a href="..." class="btn btn-sm btn-outline-warning" title="Edit Review">
}

// Enhanced status badge with feedback date
return '<span class="badge bg-success">Completed</span>' . $feedbackDate;
```

### View (`reviewers/abstracts-papers/index.php`)
- Changed table header from "Status" to "Review Status"
- Maintained all filtering functionality
- Enhanced visual feedback for active filters

## Features

### For Reviewers
- **Subtheme Filtering**: Dropdown shows only reviewer's assigned subthemes
- **Visual Feedback**: Active filters displayed below page title
- **Combined Filtering**: Can filter by both subtheme and review status simultaneously
- **All Abstracts Visible**: Can see all assigned abstracts regardless of feedback status
- **Edit Capability**: Can edit existing reviews (not just add new ones)
- **Responsive Design**: Improved layout for mobile devices

### User Experience
- **Smart Loading**: Subthemes loaded from existing data (no additional AJAX call)
- **Clear Indicators**: Visual feedback when filters are active
- **Error Handling**: Graceful handling when no subthemes are assigned
- **Backward Compatibility**: Review status filtering still works as before

## Technical Implementation

### Data Flow
1. **Page Load**: Subthemes populated from `$assignedSubthemes` PHP variable
2. **Filter Change**: JavaScript sends filter values via AJAX
3. **Server Processing**: Controller extracts `subtheme_filter` parameter
4. **Database Query**: Model applies WHERE clause on `participant_subthemes` table
5. **Results**: Filtered abstracts returned to DataTable

### Database Relationships
```sql
-- Subtheme filtering logic
participant_subthemes ps_link
WHERE ps_link.program_subtheme_id = {selected_subtheme_id}
```

### Filter Parameters
- `subtheme_filter`: Selected subtheme ID (replaces `abstract_status_filter`)
- `status_filter`: Review status (pending/completed) - unchanged

## Benefits

### Improved User Experience
- **Focused View**: Reviewers can focus on specific subthemes
- **Better Navigation**: Clear visual indicators of active filters
- **Reduced Cognitive Load**: Fewer but more relevant filter options

### Enhanced Workflow
- **Subtheme-Based Review**: Aligns with reviewer assignment structure
- **Efficient Processing**: Can process all abstracts in one subtheme at a time
- **Status Tracking**: Still maintains review completion tracking

### System Architecture
- **Cleaner Logic**: Removes redundant abstract status filtering
- **Better Performance**: More targeted database queries
- **Maintainable Code**: Simplified filter logic

## Testing Scenarios

1. **Reviewer with Multiple Subthemes**
   - Login as reviewer assigned to multiple subthemes
   - Verify dropdown shows all assigned subthemes
   - Test filtering by each subtheme
   - Verify filter indicator updates correctly

2. **Reviewer with Single Subtheme**
   - Login as reviewer with one subtheme
   - Verify dropdown shows the single subtheme
   - Test combined filtering (subtheme + review status)

3. **Reviewer with No Subthemes**
   - Test graceful handling with appropriate message
   - Verify table shows no data

4. **Filter Combinations**
   - Test "All Assigned Subthemes" + "Pending" status
   - Test specific subtheme + "Completed" status
   - Verify filter indicator shows multiple active filters

## Backward Compatibility

- **Existing Routes**: All existing endpoints remain unchanged
- **Database Schema**: No database changes required
- **Review Process**: Review workflow remains identical
- **Admin Interface**: Admin abstract management unaffected

## Migration Notes

- **No Data Migration**: Changes are purely interface-level
- **Session Compatibility**: Uses existing reviewer session data
- **API Compatibility**: DataTables AJAX interface unchanged
