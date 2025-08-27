# GitHub Copilot Instructions for YBB Admin System

This document provides comprehensive guidelines for AI coding agents when working with the YBB (Youth Break the Boundaries) Administration System.

## System Architecture Overview

### Framework & Technology Stack
- **Backend**: CodeIgniter 4 PHP framework
- **Frontend**: jQuery, Bootstrap, SweetAlert2 for notifications
- **Database**: MySQL with utf8mb4 charset for international support
- **External Services**: YBB Export Python API (Railway-hosted), Midtrans payment gateway
- **Key Libraries**: PhpSpreadsheet, DOMPdf, Firebase JWT, Midtrans SDK

### Core Application Structure
```
app/
├── Controllers/          # Business logic controllers
├── Models/              # Database models and data access
├── Views/               # Template files (PHP/HTML)
├── Config/              # Configuration files and routes
├── Libraries/           # Custom libraries (YbbExport, etc.)
├── Services/            # Service classes (MenuService, etc.)
├── Filters/             # Request filters and middleware
└── Helpers/             # Utility functions
```

## Architectural Patterns & Conventions

### Controller Architecture
- **BaseController**: Provides common functionality including topbar data loading, session management, and cache handling
- **AdminBaseController**: Specialized for admin authentication, menu management, and role-based access
- **Program-specific logic**: Most controllers require program selection context via session

### Authentication & Authorization
- Session-based authentication with role hierarchy: `super` > `program_admin` > `editor` > `moderator`
- Reviewers have separate authentication flow tied to specific programs
- Route protection via filters: `auth`, `program_selection`, `access_control`
- Menu system managed through `MenuService` with role-based visibility

### Database & Model Patterns
- **Remote Database**: The database is hosted remotely - ALWAYS verify connectivity and consider network latency in queries
- Standard CodeIgniter 4 model inheritance
- Program-centric data organization (most entities tied to programs)
- Participant-centric workflows with status tracking
- Export models optimized for large datasets
- **Single Responsibility**: Each method should have one clear purpose
- **Query Separation**: ALL database queries must be in Models, never in Controllers

## Critical Integration Points

### YBB Export System
The system integrates with a Python Flask API for handling large-scale data exports:

**Key Components:**
- `YbbExport` Library: PHP wrapper for Python API integration
- `EnhancedExportManager.js`: Frontend export management with real-time status tracking
- Controllers: `YbbExportController`, `Participants::export_batch()`

**Export Workflow:**
1. Frontend initiates export via AJAX
2. Controller validates filters and calls YbbExport library
3. YbbExport sends data to Python API
4. Frontend polls for status updates
5. Download links provided upon completion

**Memory Management:**
- Chunked processing for large datasets (>1000 records)
- Memory-safe JSON encoding
- Configurable retry logic and timeouts

### Frontend Export Integration
```javascript
// Standard export initialization pattern
class EnhancedExportManager {
    constructor() {
        this.isProcessing = false;
        this.currentExports = new Map();
        this.exportHistory = this.loadExportHistory();
    }
}
```

## Development Guidelines

### Code Style & Standards
- Follow CodeIgniter 4 conventions
- Use meaningful variable names and comprehensive documentation
- Implement proper error handling with user-friendly messages
- Log important operations for debugging
- **Single Responsibility Principle**: Each method should have ONE clear, specific purpose
- **Separation of Concerns**: Controllers handle HTTP requests/responses, Models handle data operations
- **No Direct Queries in Controllers**: All database operations must be delegated to appropriate Model methods

### Database Operations
- **Remote Database Connection**: Always verify database connectivity before operations - the database is hosted remotely
- Always use parameterized queries
- Implement proper transaction handling for multi-table operations
- Use CodeIgniter's Query Builder for complex queries
- Consider performance impact of joins on large tables
- **Models Only**: ALL database queries MUST be written in Model classes, NEVER in Controllers
- Consider network latency when designing query patterns for remote database

### Cache Management
- Topbar data cached per user with 24-hour TTL
- Program-specific cache invalidation patterns
- Use cache helper functions for consistent behavior
- Clear relevant caches when data changes

### Error Handling Patterns
```php
// Standard error response pattern
return [
    'success' => false,
    'message' => 'User-friendly error message',
    'error_code' => 'SPECIFIC_ERROR_CODE',
    'details' => $debugDetails
];
```

### Frontend JavaScript Standards
- Use SweetAlert2 for all user notifications
- Implement loading states for AJAX operations
- Handle errors gracefully with fallback options
- Use consistent event delegation patterns

### View File Management
- **Template Files**: View files located directly in the `app/Views/` root folder are template files from the original theme
- **Reference Only**: Template files should be used as reference for styling and structure patterns - DO NOT edit them directly
- **Consistency Check**: When creating new UI elements, examine template files to maintain visual and functional consistency
- **Custom Views**: Create custom view files in appropriate subfolders (e.g., `app/Views/users/`, `app/Views/admin/`)
- **Widget Patterns**: Look at template files to understand existing widget patterns before creating new components

## Security Considerations

### Input Validation
- Sanitize all user inputs
- Validate file uploads thoroughly
- Use CSRF protection for state-changing operations
- Implement rate limiting for export operations

### Data Protection
- Sensitive data encryption for payments
- Role-based data access restrictions
- Audit logging for critical operations
- Secure file handling with proper mime type validation

### API Security
- JWT tokens for API authentication
- API rate limiting and request validation
- Secure webhook handling for payment notifications
- Environment-based configuration management

## Performance Optimization

### Database Performance
- Use indexes on frequently queried columns
- Implement pagination for large result sets
- Optimize JOIN operations and avoid N+1 queries
- Regular database maintenance and optimization

### Frontend Performance
- Lazy loading for large datasets
- Debounced search inputs
- Efficient DOM manipulation
- Asset compression and caching

### Export Performance
- Chunked processing for large exports
- Background processing for time-intensive operations
- Progress tracking and user feedback
- Cleanup old export files regularly

## Testing & Debugging

### Development Tools
- Built-in debugging routes under `/debug/`
- Test controllers for export functionality
- Certificate generation testing endpoints
- Session debugging utilities

### Logging Strategy
- Use CodeIgniter's logging system consistently
- Debug level for development, info/error for production
- Export operation logging for monitoring
- Performance metrics logging

### Common Debugging Patterns
```php
// Standard debugging approach
if ($this->config->enableDebugLogging) {
    log_message('debug', 'Operation details: ' . json_encode($data));
}
```

## Specific Module Guidelines

### Participant Management
- Always consider program context when querying participants
- Handle multiple competition categories and status transitions
- Implement proper export filtering and sorting
- Use optimized models for large participant datasets

### Payment System
- Integrate with Midtrans payment gateway
- Handle webhook notifications securely
- Implement proper payment status tracking
- Support multiple payment methods per program

### Abstract & Paper Management
- Version control for submitted papers
- Reviewer assignment and feedback system
- Topic-based categorization
- PDF generation for papers

### Certificate System
- Integration with external certificate generation service
- Participant award tracking
- Template management with placeholders
- Bulk certificate generation capabilities

## Configuration Management

### Environment Variables
- `YBB_EXPORT_API_URL`: Python export service endpoint
- Database connection parameters
- Midtrans API credentials
- Cache configuration settings

### Configuration Files
- Routes are modularized in `Config/Routes/`
- Service configurations in respective config classes
- Environment-specific settings support

## Deployment Considerations

### Production Deployment
- Enable proper error handling and logging
- Configure cache settings appropriately
- Set up proper file permissions
- Monitor export service connectivity

### Monitoring & Maintenance
- Regular cleanup of temporary export files
- Database performance monitoring
- Cache invalidation monitoring
- Export service health checks

## Common Patterns & Examples

### Standard Controller Method
```php
public function index()
{
    $this->requireAuth();
    $data = $this->prepareViewData([
        'pageTitle' => 'Page Title'
    ]);
    return $this->renderView('view/path', $data);
}
```

### Export Implementation
```php
public function export_batch()
{
    $ybbExport = new \App\Libraries\YbbExport();
    $result = $ybbExport->exportParticipants($data, $options);
    
    if ($result['success']) {
        return $this->response->setJSON($result);
    } else {
        return $this->response->setJSON($result)->setStatusCode(500);
    }
}
```

### Frontend AJAX Pattern
```javascript
$.ajax({
    url: baseUrl + '/endpoint',
    method: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            Swal.fire('Success', response.message, 'success');
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }
});
```

## Version History & Migration Notes

### Recent Major Changes
- Enhanced export system with Python API integration
- Improved cache management system
- UTF8MB4 database conversion for international support
- Comprehensive error handling improvements

### Migration Considerations
- Database charset conversion requires careful planning
- Export system backward compatibility maintained
- Session management updates for better performance
- Menu system restructured for better maintainability

---

When working with this codebase, always consider the multi-tenant nature (program-based), the large-scale data handling requirements, and the complex workflow dependencies between participants, payments, and exports. Pay special attention to memory management when dealing with export operations and maintain consistency with the established patterns throughout the application.