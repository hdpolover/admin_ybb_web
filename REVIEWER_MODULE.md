# Reviewer Module Documentation

## Overview

The Reviewer Module is a dedicated section for reviewers to access and manage their abstract review assignments. This module is completely separate from the admin panel and provides reviewers with their own authentication flow, dashboard, and feature set.

## Features

### Authentication
- Dedicated reviewer authentication through the main sign-in page
- Session management separate from admin sessions
- Secure password-based authentication

### Dashboard
- Overview of review statistics (total assigned, completed, pending)
- Recent reviews list
- Quick action buttons
- Review guidelines and tips

### Abstracts & Papers Management
- View all assigned abstract versions for review
- Filter by status (pending, completed)
- Search functionality
- Detailed abstract viewing
- Feedback submission with text comments

### Profile Management (My Info)
- Update personal information (name, email, institution)
- Change password
- Upload profile picture
- View review statistics

## Routes

### Authentication Routes
- `POST /reviewer-sign-in` - Reviewer authentication
- `GET /reviewers/sign-out` - Sign out

### Dashboard Routes
- `GET /reviewers/dashboard` - Main dashboard
- `GET /reviewers/dashboard/ajaxAbstractStats` - Abstract statistics (AJAX)
- `GET /reviewers/dashboard/ajaxReviewStats` - Review statistics (AJAX)

### Abstracts & Papers Routes
- `GET /reviewers/abstracts-papers` - List all assigned reviews
- `GET /reviewers/abstracts-papers/getData` - DataTables data (AJAX)
- `GET /reviewers/abstracts-papers/view/{id}` - View abstract details
- `GET /reviewers/abstracts-papers/review/{id}` - Review abstract
- `POST /reviewers/abstracts-papers/submit-review/{id}` - Submit review

### Profile Routes
- `GET /reviewers/my-info` - Profile management page
- `POST /reviewers/my-info/update` - Update profile
- `POST /reviewers/my-info/change-password` - Change password
- `POST /reviewers/my-info/upload-avatar` - Upload profile picture

## Database Structure

### Abstract Reviewers Table
```sql
CREATE TABLE `abstract_reviewers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `institution` text,
  `password` varchar(255) NOT NULL,
  `role` enum('reviewer') DEFAULT 'reviewer',
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`),
  KEY `program_id` (`program_id`),
  KEY `email` (`email`)
);
```

### Abstract Feedbacks Table
```sql
CREATE TABLE `abstract_feedbacks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `abstract_version_id` int(11) unsigned NOT NULL,
  `abstract_reviewer_id` int(11) unsigned NOT NULL,
  `feedback` text,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`),
  KEY `abstract_version_reviewer` (`abstract_version_id`,`abstract_reviewer_id`),
  KEY `abstract_reviewer_id` (`abstract_reviewer_id`)
);
```

### Abstract Reviewer Subthemes Table
```sql
CREATE TABLE `abstract_reviewer_subthemes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `abstract_reviewer_id` int(11) unsigned NOT NULL,
  `program_subtheme_id` int(11) unsigned NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`),
  KEY `reviewer_subtheme` (`abstract_reviewer_id`,`program_subtheme_id`)
);
```

## Installation

1. **Run Migrations**
   ```bash
   php spark migrate
   ```

2. **Seed Sample Data**
   ```bash
   php spark db:seed ReviewerSeeder
   ```

3. **Set Up Routes**
   - Routes are automatically included in the main routes file
   - No additional configuration needed

## Default Reviewer Accounts

After running the seeder, you can use these accounts to test:

| Email | Password | Specialization |
|-------|----------|----------------|
| reviewer1@example.com | reviewer123 | Environmental Science |
| reviewer2@example.com | reviewer123 | Technology & Innovation |
| reviewer3@example.com | reviewer123 | Social Sciences |
| reviewer4@example.com | reviewer123 | Economics & Business |
| reviewer5@example.com | reviewer123 | Health Sciences |

## Security Features

### Program and Subtheme-Based Access Control
- **Reviewers can only access abstracts from their assigned program**
- **Reviewers can only review abstracts in their assigned subthemes**
- **Double verification**: Both program ID and subtheme assignment are checked
- **SQL-level filtering**: Security is enforced at the database query level

### Authentication Filter
- `ReviewerAuthFilter` ensures only authenticated reviewers can access reviewer routes
- Checks for active reviewer status and proper session data
- Redirects unauthorized users to sign-in page

### Data Protection
- **Password hashing** using PHP's `password_hash()`
- **CSRF protection** on all forms
- **Input validation** and sanitization
- **Secure file upload** handling
- **SQL injection prevention** through parameterized queries

### Access Validation
- **Abstract access verification**: Each abstract view/edit request is validated
- **Subtheme assignment checking**: Reviewers must be assigned to the abstract's subtheme
- **Program matching**: Abstract's program must match reviewer's program
- **Session-based authorization**: All actions require valid reviewer session

### Administrative Controls
- **Reviewer-subtheme assignments** can be managed by administrators
- **Workload balancing** through assignment statistics
- **Access audit trail** through database timestamps
- **Bulk assignment tools** with automatic validation

## Usage Instructions

### For Reviewers

1. **Sign In**
   - Go to the main sign-in page
   - Select "Reviewer" from the dropdown
   - Enter your email and password
   - Click "Sign In as Reviewer"

2. **Dashboard**
   - View your review statistics
   - See recent reviews
   - Access quick actions

3. **Review Abstracts**
   - Go to "Abstracts & Papers"
   - Click "Review" on any pending abstract
   - Provide score (1-10) and recommendation
   - Add detailed comments
   - Submit review

4. **Manage Profile**
   - Go to "My Info"
   - Update personal details
   - Change password
   - Upload profile picture

### For Administrators

1. **Assign Reviewers to Subthemes**
   - Use the `AbstractReviewerSubthemeModel` to assign reviewers to specific subthemes
   - Ensure reviewers are only assigned to subthemes within their program
   - Use bulk assignment methods for efficiency

2. **Assign Abstract Reviews**
   - Create entries in the `abstract_feedbacks` table
   - Use the `getAvailableReviewersForAbstract()` method to get eligible reviewers
   - System automatically validates program and subtheme compatibility

3. **Monitor Review Progress**
   - Use `getReviewerWorkloadStats()` to balance assignments
   - Track completion rates and pending reviews
   - Monitor reviewer performance across subthemes

4. **Manage Access Control**
   - Reviewers are automatically restricted to their program and assigned subthemes
   - No additional configuration needed - security is built into the data model
   - Review access is validated on every request

## Customization

### Adding New Features
1. Create new controller methods in the `App\Controllers\Reviewers` namespace
2. Add corresponding routes in `app/Config/Routes/Reviewers.php`
3. Create new views in `app/Views/reviewers/`

### Modifying the UI
- Update views in `app/Views/reviewers/`
- Modify the reviewer layout in `app/Views/layouts/reviewer.php`
- Update the sidebar in `app/Views/reviewers/partials/sidebar.php`

### Extending the Database
- Create new migrations for additional tables
- Update models to include new relationships
- Add new fields to existing tables as needed

## Troubleshooting

### Common Issues

1. **Cannot Access Reviewer Routes**
   - Check if `ReviewerAuthFilter` is properly registered
   - Ensure reviewer is logged in with correct session data

2. **Database Errors**
   - Verify migrations have been run
   - Check foreign key constraints
   - Ensure proper table relationships

3. **Authentication Issues**
   - Verify reviewer email and password
   - Check if reviewer account is active (`is_active = 1`)
   - Ensure account is not deleted (`is_deleted = 0`)

### Debug Mode
Enable CodeIgniter's debug mode to get detailed error information:
```php
// In app/Config/Boot/development.php
ini_set('display_errors', '1');
error_reporting(E_ALL);
```

## Future Enhancements

### Possible Additions
1. **Email Notifications**
   - Review assignment notifications
   - Deadline reminders
   - Completion confirmations

2. **Review Templates**
   - Predefined review criteria
   - Structured review forms
   - Scoring rubrics

3. **Conflict of Interest Management**
   - Reviewer-author relationship checks
   - Automatic conflict detection
   - Alternative reviewer suggestions

4. **Advanced Analytics**
   - Review quality metrics
   - Reviewer performance tracking
   - Statistical reporting

5. **Mobile Responsiveness**
   - Improved mobile interface
   - Touch-friendly controls
   - Offline review capabilities

## Support

For technical support or feature requests, please contact the development team.
