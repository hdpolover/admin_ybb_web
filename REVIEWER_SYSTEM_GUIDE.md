# Reviewer Abstract Management System

## Overview

This reviewer dashboard allows abstract reviewers to view, review, and provide feedback on submitted abstracts based on their assigned subthemes.

## Key Features

### 1. Subtheme-Based Access Control
- Reviewers can only see abstracts from participants assigned to their designated subthemes
- The system uses the `abstract_reviewer_subthemes` table to manage reviewer assignments
- Participants' subtheme assignments are stored in `participant_subthemes` table

### 2. Abstract Filtering
- **Status-based filtering**: Only submitted abstracts are shown by default
- **Review status filtering**: Completed vs Pending reviews
- **Search functionality**: Search by title, participant name, program, or subtheme

### 3. Review Process
- **View abstracts**: Read-only access to abstract content
- **Review abstracts**: Provide feedback and recommendations
- **Status management**: Option to recommend accept/revise/reject

### 4. Security Features
- Program-level isolation: Reviewers only see abstracts from their assigned program
- Session validation: Ensures reviewer is properly authenticated
- Access control: Multiple validation layers to prevent unauthorized access

## Database Structure

### Key Tables
- `abstracts`: Main abstract table with program and status information
- `abstract_versions`: Stores different versions of abstracts
- `abstract_reviewers`: Reviewer accounts with program assignments
- `abstract_reviewer_subthemes`: Links reviewers to specific subthemes
- `participant_subthemes`: Links participants to their chosen subthemes
- `abstract_feedbacks`: Stores reviewer feedback and ratings

### Relationships
```
abstracts.primary_participant_id → participants.id
participants.id → participant_subthemes.participant_id
participant_subthemes.program_subtheme_id → program_subthemes.id
abstract_reviewers.id → abstract_reviewer_subthemes.abstract_reviewer_id
abstract_reviewer_subthemes.program_subtheme_id → program_subthemes.id
```

## Controller Functions

### AbstractsPapers Controller
- `index()`: Main dashboard view with assigned subthemes display
- `getData()`: DataTables AJAX endpoint with filtering and security
- `view($abstractId)`: Read-only abstract viewing
- `review($abstractId)`: Interactive review interface
- `submitReview($abstractId)`: Process feedback submission
- `getStats()`: Reviewer statistics and progress
- `debugReviewerAccess()`: Debug method for troubleshooting

## Model Functions

### AbstractFeedbackModel
- `getFeedbacksByReviewer($reviewer_id, $filters)`: Main method to get reviewer's assigned abstracts
- `getFeedbackDetails($abstract_id, $reviewer_id)`: Get specific abstract with access validation
- `submitFeedback($abstract_id, $reviewer_id, $feedback)`: Save or update feedback
- `getReviewerStats($reviewer_id)`: Calculate completion statistics

## Usage Instructions

### For Administrators
1. Create reviewer accounts in the Abstract Reviewers management section
2. Assign reviewers to specific subthemes
3. Ensure participants have selected their subthemes
4. Monitor review progress through admin dashboard

### For Reviewers
1. Log in to the reviewer portal
2. Navigate to "Abstracts & Papers"
3. View your assigned subthemes at the top of the page
4. Filter abstracts by status or search for specific content
5. Click "View" to read an abstract or "Review" to provide feedback
6. Submit constructive feedback and optional recommendations

## Troubleshooting

### If reviewers see no abstracts:
1. Check if reviewer is assigned to subthemes (`abstract_reviewer_subthemes`)
2. Verify participants have selected subthemes (`participant_subthemes`)
3. Ensure abstracts have status "submitted"
4. Confirm reviewer and abstracts are in the same program
5. Use the debug endpoint: `/reviewers/abstracts-papers/debug`

### Common Issues:
- **Empty table**: Usually means no subtheme assignments or no submitted abstracts
- **Access denied**: Program mismatch or inactive reviewer account
- **Missing abstracts**: Participant hasn't selected a subtheme the reviewer is assigned to

## Security Considerations

- All database queries include active/deleted status checks
- Session validation on every request
- CSRF protection on form submissions
- Program-level data isolation
- Multiple join conditions to prevent data leakage

## Future Enhancements

- Bulk feedback import/export
- Review assignment automation
- Email notifications for new assignments
- Review deadline management
- Collaborative review features
