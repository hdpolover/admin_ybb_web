<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPaymentPeriodModel extends Model
{
    protected $table = 'program_payment_periods';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'payment_id',
        'parent_period_id',
        'extension_type',
        'name',
        'description',
        'start_date',
        'end_date',
        'order_number',
        'is_active',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Get all periods for a specific payment
     *
     * @param int $paymentId The payment ID
     * @param bool $activeOnly Whether to get only active periods (default: true)
     * @param bool $includeDeleted Whether to include deleted periods (default: false)
     * @return array The payment periods
     */
    public function getByPaymentId($paymentId, $activeOnly = true, $includeDeleted = false)
    {
        $this->where('payment_id', $paymentId);
        
        if ($activeOnly) {
            $this->where('is_active', 1);
        }
        
        if (!$includeDeleted) {
            $this->where('is_deleted', 0);
        }
        
        $this->orderBy('order_number', 'ASC');
        return $this->findAll();
    }

    /**
     * Get the currently active period for a payment
     * This is used for frontend compatibility - returns the period that should be shown
     *
     * @param int $paymentId The payment ID
     * @return object|null The active period or null if none active
     */
    public function getCurrentActivePeriod($paymentId)
    {
        // Get current date/time using CodeIgniter's timezone handling
        helper('date');
        $appTimezone = app_timezone();
        
        // Create DateTime in application timezone
        $timezone = new \DateTimeZone($appTimezone);
        $currentDateTime = new \DateTime('now', $timezone);
        $currentDateTimeString = $currentDateTime->format('Y-m-d H:i:s');
        
        log_message('debug', "Checking active period for payment {$paymentId} at {$currentDateTimeString} ({$appTimezone})");
        
        $builder = $this->builder();
        $result = $builder
            ->where('payment_id', $paymentId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('start_date <=', $currentDateTimeString)
            ->where('end_date >=', $currentDateTimeString)
            ->orderBy('order_number', 'ASC')
            ->get()
            ->getFirstRow();
            
        if ($result) {
            log_message('debug', "Active period found: {$result->name} ({$result->start_date} to {$result->end_date})");
        } else {
            log_message('debug', "No active period found for payment {$paymentId}");
        }
        
        return $result;
    }

    /**
     * Get the next upcoming period for a payment
     * Useful for showing "registration opens on" messages
     *
     * @param int $paymentId The payment ID
     * @return object|null The next period or null if none upcoming
     */
    public function getNextUpcomingPeriod($paymentId)
    {
        // Get current date/time using CodeIgniter's timezone handling
        helper('date');
        $appTimezone = app_timezone();
        
        // Create DateTime in application timezone
        $timezone = new \DateTimeZone($appTimezone);
        $currentDateTime = new \DateTime('now', $timezone);
        $currentDateTimeString = $currentDateTime->format('Y-m-d H:i:s');
        
        $builder = $this->builder();
        $result = $builder
            ->where('payment_id', $paymentId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('start_date >', $currentDateTimeString)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getFirstRow();
            
        if ($result) {
            log_message('debug', "Upcoming period found: {$result->name} (starts {$result->start_date})");
        }
        
        return $result;
    }

    /**
     * Get the most recent ended period for a payment
     * Useful for showing what was the last available period
     *
     * @param int $paymentId The payment ID
     * @return object|null The most recent ended period or null if none found
     */
    public function getLastEndedPeriod($paymentId)
    {
        // Get current date/time using CodeIgniter's timezone handling
        helper('date');
        $appTimezone = app_timezone();
        
        // Create DateTime in application timezone
        $timezone = new \DateTimeZone($appTimezone);
        $currentDateTime = new \DateTime('now', $timezone);
        $currentDateTimeString = $currentDateTime->format('Y-m-d H:i:s');
        
        log_message('debug', "Checking for last ended period for payment {$paymentId} before {$currentDateTimeString}");
        
        $builder = $this->builder();
        $result = $builder
            ->where('payment_id', $paymentId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('end_date <', $currentDateTimeString)
            ->orderBy('end_date', 'DESC')
            ->get()
            ->getFirstRow();
            
        if ($result) {
            log_message('debug', "Last ended period found: {$result->name} (ended {$result->end_date})");
        } else {
            log_message('debug', "No ended period found for payment {$paymentId}");
        }
        
        return $result;
    }

    /**
     * Check if a payment has any active period currently or in the future
     *
     * @param int $paymentId The payment ID
     * @return bool Whether payment is available (now or later)
     */
    public function isPaymentAvailable($paymentId)
    {
        // Get current date/time using CodeIgniter's timezone handling
        helper('date');
        $appTimezone = app_timezone();
        
        // Create DateTime in application timezone
        $timezone = new \DateTimeZone($appTimezone);
        $currentDateTime = new \DateTime('now', $timezone);
        $currentDateTimeString = $currentDateTime->format('Y-m-d H:i:s');
        
        $builder = $this->builder();
        $count = $builder
            ->where('payment_id', $paymentId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->where('end_date >=', $currentDateTimeString)
            ->countAllResults();
            
        return $count > 0;
    }

    /**
     * Validate period dates for non-overlapping constraint
     * Extension periods (with parent_period_id) are allowed to overlap
     *
     * @param int $paymentId The payment ID
     * @param string $startDate The start date to validate
     * @param string $endDate The end date to validate
     * @param int|null $excludeId Period ID to exclude from validation (for updates)
     * @param int|null $parentPeriodId Parent period ID if this is an extension
     * @param string $extensionType Type of extension: 'continuation' or 'parallel'
     * @return array Validation result with 'valid' boolean and 'message'
     */
    public function validatePeriodDates($paymentId, $startDate, $endDate, $excludeId = null, $parentPeriodId = null, $extensionType = 'continuation')
    {
        // Basic date validation
        if (strtotime($startDate) >= strtotime($endDate)) {
            return [
                'valid' => false,
                'message' => 'Start date must be before end date'
            ];
        }

        // If this is an extension period, validate against parent
        if ($parentPeriodId) {
            $parentPeriod = $this->find($parentPeriodId);
            
            if (!$parentPeriod) {
                return [
                    'valid' => false,
                    'message' => 'Parent period not found'
                ];
            }
            
            // Validate parent belongs to same payment
            if ($parentPeriod->payment_id != $paymentId) {
                return [
                    'valid' => false,
                    'message' => 'Parent period must belong to the same payment'
                ];
            }
            
            // Validate extension type rules
            if ($extensionType === 'continuation') {
                // Continuation must start on or after parent's end date
                if (strtotime($startDate) < strtotime($parentPeriod->end_date)) {
                    return [
                        'valid' => false,
                        'message' => "Continuation extension must start on or after parent period end date ({$parentPeriod->end_date})"
                    ];
                }
            } elseif ($extensionType === 'parallel') {
                // Parallel extension must have some overlap with parent
                $parentStart = strtotime($parentPeriod->start_date);
                $parentEnd = strtotime($parentPeriod->end_date);
                $newStart = strtotime($startDate);
                $newEnd = strtotime($endDate);
                
                // Check if there's any overlap
                $hasOverlap = ($newStart <= $parentEnd && $newEnd >= $parentStart);
                
                if (!$hasOverlap) {
                    return [
                        'valid' => false,
                        'message' => "Parallel extension must overlap with parent period ({$parentPeriod->start_date} to {$parentPeriod->end_date})"
                    ];
                }
            }
            
            // Extension periods are allowed to overlap - skip overlap check
            return [
                'valid' => true,
                'message' => 'Extension period dates are valid'
            ];
        }

        // For base periods (no parent), check for overlaps with OTHER base periods only
        $builder = $this->builder();
        $builder->where('payment_id', $paymentId)
                ->where('is_deleted', 0)
                ->where('parent_period_id IS NULL', null, false); // Only check against base periods
                
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        // Check for any overlap: new period overlaps if:
        // 1. New start is between existing start and end, OR
        // 2. New end is between existing start and end, OR  
        // 3. New period completely encompasses existing period
        $builder->groupStart()
                    ->groupStart()
                        ->where('start_date <=', $startDate)
                        ->where('end_date >=', $startDate)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('start_date <=', $endDate)
                        ->where('end_date >=', $endDate)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('start_date >=', $startDate)
                        ->where('end_date <=', $endDate)
                    ->groupEnd()
                ->groupEnd();
        
        $conflicting = $builder->get()->getFirstRow();
        
        if ($conflicting) {
            return [
                'valid' => false,
                'message' => "Period overlaps with existing base period: {$conflicting->name} ({$conflicting->start_date} to {$conflicting->end_date})"
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Period dates are valid'
        ];
    }

    /**
     * Get the next available order number for a payment
     *
     * @param int $paymentId The payment ID
     * @return int The next order number
     */
    public function getNextOrderNumber($paymentId)
    {
        $builder = $this->builder();
        $result = $builder
            ->where('payment_id', $paymentId)
            ->where('is_deleted', 0)
            ->selectMax('order_number')
            ->get()
            ->getFirstRow();
            
        return ($result && $result->order_number) ? $result->order_number + 1 : 1;
    }

    /**
     * Reorder periods after deletion or modification
     *
     * @param int $paymentId The payment ID
     * @return bool Success status
     */
    public function reorderPeriods($paymentId)
    {
        try {
            $periods = $this->getByPaymentId($paymentId, false, false);
            
            $orderNumber = 1;
            foreach ($periods as $period) {
                $this->update($period->id, ['order_number' => $orderNumber]);
                $orderNumber++;
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', "Error reordering periods for payment {$paymentId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get effective date range for a period (includes parent if extension)
     *
     * @param int $periodId The period ID
     * @return array|null Effective range with start, end, and metadata
     */
    public function getEffectiveDateRange($periodId)
    {
        $period = $this->find($periodId);
        
        if (!$period) {
            return null;
        }
        
        // Base period - use its own dates
        if (!$period->parent_period_id) {
            return [
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'display_label' => $period->name,
                'is_extension' => false,
                'period_id' => $period->id
            ];
        }
        
        // Extension period - get parent info
        $parent = $this->find($period->parent_period_id);
        
        if (!$parent) {
            // Parent not found, fallback to period's own dates
            return [
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'display_label' => $period->name,
                'is_extension' => true,
                'period_id' => $period->id
            ];
        }
        
        return [
            'start_date' => $parent->start_date,  // Show from parent's start
            'end_date' => $period->end_date,      // Until extension's end
            'display_label' => "{$parent->name} + {$period->name}",
            'extension_active_from' => $period->start_date,  // When extension actually begins
            'parent_period_name' => $parent->name,
            'extension_period_name' => $period->name,
            'extension_type' => $period->extension_type,
            'is_extension' => true,
            'parent_period_id' => $parent->id,
            'period_id' => $period->id
        ];
    }

    /**
     * Get all base periods (non-extension) for a payment
     *
     * @param int $paymentId The payment ID
     * @param bool $activeOnly Whether to get only active periods
     * @return array Base periods
     */
    public function getBasePeriods($paymentId, $activeOnly = true)
    {
        $builder = $this->builder();
        $builder->where('payment_id', $paymentId)
                ->where('is_deleted', 0)
                ->where('parent_period_id IS NULL', null, false);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        $builder->orderBy('order_number', 'ASC');
        return $builder->get()->getResult();
    }

    /**
     * Get all extension periods for a specific parent period
     *
     * @param int $parentPeriodId The parent period ID
     * @param bool $activeOnly Whether to get only active periods
     * @return array Extension periods
     */
    public function getExtensionsByParent($parentPeriodId, $activeOnly = true)
    {
        $builder = $this->builder();
        $builder->where('parent_period_id', $parentPeriodId)
                ->where('is_deleted', 0);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }
        
        $builder->orderBy('start_date', 'ASC');
        return $builder->get()->getResult();
    }

    /**
     * Get period hierarchy (base periods with their extensions)
     *
     * @param int $paymentId The payment ID
     * @param bool $activeOnly Whether to get only active periods
     * @return array Hierarchical period structure
     */
    public function getPeriodHierarchy($paymentId, $activeOnly = true)
    {
        $basePeriods = $this->getBasePeriods($paymentId, $activeOnly);
        $hierarchy = [];
        
        foreach ($basePeriods as $basePeriod) {
            $extensions = $this->getExtensionsByParent($basePeriod->id, $activeOnly);
            
            $hierarchy[] = [
                'base' => $basePeriod,
                'extensions' => $extensions,
                'has_extensions' => count($extensions) > 0
            ];
        }
        
        return $hierarchy;
    }

    /**
     * Check if a period can be deleted (has no extensions)
     *
     * @param int $periodId The period ID
     * @return array Result with 'can_delete' boolean and 'message'
     */
    public function canDeletePeriod($periodId)
    {
        $extensions = $this->getExtensionsByParent($periodId, false);
        
        if (count($extensions) > 0) {
            return [
                'can_delete' => false,
                'message' => 'Cannot delete period with active extensions. Delete extensions first.',
                'extension_count' => count($extensions)
            ];
        }
        
        return [
            'can_delete' => true,
            'message' => 'Period can be deleted'
        ];
    }
}