# Payment Cache Invalidation - Complete Implementation

## Overview
This document outlines all cached data that gets invalidated when payment status changes, ensuring data consistency across the application.

## Cache Invalidation Triggers
Cache invalidation occurs in the `PaymentModel` during:
1. Payment status updates (`updatePaymentStatus()`)
2. Payment creation (`insert()`)
3. Payment updates (`update()`)
4. Payment deletion (`delete()`)

## Categories of Cached Data Invalidated

### 1. Payment-Specific Caches
- `payment_stats_{programId}` - Payment statistics for specific program
- `payment_stats_currency_{programId}` - Payment statistics by currency for program
- `pending_manual_payments_{programId}` - Manual payments awaiting approval
- `payments_with_details_{programId}` - Payment details with participant info
- `payment_stats` - General payment statistics
- `payment_stats_by_currency` - General payment statistics by currency
- `pending_manual_payments` - General pending manual payments

### 2. Dashboard Caches
When payments change, dashboard statistics are affected:
- `dashboard_summary_{programId}` - Program summary statistics
- `dashboard_registration_stats_{programId}_{period}_{limit}` - Registration trends
- `dashboard_gender_stats_{programId}` - Gender distribution statistics
- `dashboard_age_stats_{programId}` - Age distribution statistics
- `dashboard_nationality_stats_{programId}_{limit}` - Nationality distribution
- `dashboard_ambassador_stats_{programId}_{limit}` - Ambassador referral statistics

### 3. Participant-Related Caches
- `participant_payments_{participantId}` - Specific participant's payments
- `has_successful_payments_{participantId}` - Whether participant has successful payments
- `has_payments_{participantId}_{programId}` - Payment status for participant in program
- `participant_{participantId}` - Participant details
- `participant_details_{participantId}` - Extended participant information
- `participant_stats_{programId}_{date}` - Participant statistics for program
- `total_countries_{programId}` - Total countries represented
- `countries_data_{programId}` - Countries data with participant counts

### 4. Export Caches
Payment status affects export data:
- `participants_export_{programId}` - Cached export data for participants

### 5. Search Caches
Search results may include payment status:
- Search cache invalidation flag set via `invalidate_search_cache()`

### 6. Landing Page & API Caches (via RedisCacheService)
Through the cache invalidation helper functions:
- Program-specific landing page caches
- Payment method caches
- User-specific caches

## Helper Functions Used

The implementation leverages several helper functions from `CacheHelper.php`:

1. **`invalidate_dashboard_cache($programId)`**
   - Clears all dashboard-related caches for a program
   - Includes registration stats, demographics, and ambassador data

2. **`invalidate_export_cache($programId)`**
   - Clears export-related caches
   - Ensures fresh data for Excel/CSV exports

3. **`invalidate_participant_cache($participantId, $programId)`**
   - Clears participant-specific caches
   - Triggers program cache invalidation

4. **`invalidate_search_cache()`**
   - Sets search cache invalidation flags
   - Ensures search results reflect updated payment status

5. **`invalidate_payment_cache($paymentId, $participantId)`**
   - Clears payment-specific caches
   - Handles both general and participant-specific payment data

## Implementation Details

### Cache Invalidation Method
```php
private function invalidatePaymentCaches($paymentData)
{
    // Get participant and program context
    $participantId = $paymentData['participant_id'] ?? null;
    $participant = $participantModel->find($participantId);
    $programId = $participant->program_id ?? null;
    
    // Invalidate all related caches
    // ... (see implementation in PaymentModel.php)
}
```

### Automatic Triggering
Cache invalidation is automatically triggered by overriding base model methods:
- `insert()` - New payments
- `update()` - Payment modifications
- `delete()` - Payment removals
- `updatePaymentStatus()` - Status changes

## Benefits

1. **Data Consistency**: All cached data reflects current payment status
2. **Performance**: Strategic cache invalidation maintains performance while ensuring accuracy
3. **Automatic**: No manual intervention required - happens on all payment operations
4. **Comprehensive**: Covers all data that could be affected by payment changes
5. **Logging**: All cache invalidations are logged for monitoring

## Testing

Use the test script `test_payment_cache_fix.php` to verify:
1. Cache keys are properly invalidated
2. Fresh data is loaded after payment updates
3. Statistics reflect current payment status
4. Export data includes latest payment information

## Cache TTL Values

Different cache types have different Time-To-Live (TTL) values:
- Payment stats: 1-2 hours
- Dashboard data: 15 minutes
- Participant data: 4 hours
- Export data: Variable based on size
- Search results: 30 minutes

## Monitoring

Monitor cache invalidation through:
- Application logs (`PaymentModel::invalidatePaymentCaches` entries)
- Cache hit/miss ratios
- Dashboard refresh rates
- Export generation times

This comprehensive cache invalidation ensures that any changes to payment data are immediately reflected across all parts of the application that display or use payment information.
