<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramPaymentModel extends Model {
    
    protected $periodModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->periodModel = new ProgramPaymentPeriodModel();
    }
    protected $table = 'program_payments';
    // `id`, `program_id`, `name`, `description`, `order_number`, `idr_amount`, `usd_amount`, `category`, `type`, `is_active`, `is_deleted`, `created_at`, `updated_at`
    // NOTE: start_date and end_date are now managed through program_payment_periods table
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    // auto increment
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'program_id',
        'name',
        'description',
        'order_number',
        'idr_amount',
        'usd_amount',
        'category',
        'type',
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
     * Get program payments by program ID
     * Returns payments with current active period dates for frontend compatibility
     *
     * @param int $programId The program ID
     * @param bool $activeOnly Whether to get only active payments (default: true)
     * @param bool $includeDeleted Whether to include deleted payments (default: false)
     * @return array The program payments with current period dates
     */
    public function getByProgramId($programId, $activeOnly = true, $includeDeleted = false)
    {
        $this->where('program_id', $programId);
        
        if ($activeOnly) {
            $this->where('is_active', 1);
        }
        
        if (!$includeDeleted) {
            $this->where('is_deleted', 0);
        }
        
        $this->orderBy('order_number', 'ASC');
        $payments = $this->findAll();
        
        // Enhance each payment with current period information for frontend compatibility
        foreach ($payments as &$payment) {
            $this->enhancePaymentWithCurrentPeriod($payment);
        }
        
        return $payments;
    }

    /**
     * Get registration payment information for a program
     * Uses periods to determine current availability
     *
     * @param int $programId The program ID
     * @return array Array with self_funded and fully_funded payment info
     */
    public function getRegistrationPaymentFlags($programId)
    {
        $builder = $this->builder();
        $payments = $builder
            ->where('program_id', $programId)
            ->where('category', 'registration')
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->whereIn('type', ['self_funded', 'fully_funded'])
            ->get()
            ->getResult();

        $paymentFlags = [
            'self_funded' => null,
            'fully_funded' => null
        ];

        foreach ($payments as $payment) {
            // Get current active period for this payment
            $currentPeriod = $this->periodModel->getCurrentActivePeriod($payment->id);
            $isAvailable = ($currentPeriod !== null);
            
            // If no current period, check if there's a future one
            if (!$isAvailable) {
                $nextPeriod = $this->periodModel->getNextUpcomingPeriod($payment->id);
                $isAvailable = ($nextPeriod !== null);
            }
            
            // Use current period dates if available, otherwise use next period dates
            $activePeriod = $currentPeriod ?: $this->periodModel->getNextUpcomingPeriod($payment->id);
            $startDate = $activePeriod ? $activePeriod->start_date : null;
            $endDate = $activePeriod ? $activePeriod->end_date : null;

            $paymentData = [
                'id' => $payment->id,
                'name' => $payment->name,
                'description' => $payment->description,
                'usd_amount' => $payment->usd_amount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_available' => $isAvailable && ($currentPeriod !== null), // Only available if currently active
                'is_active' => $payment->is_active == 1
            ];

            if ($payment->type === 'self_funded') {
                $paymentFlags['self_funded'] = $paymentData;
            } elseif ($payment->type === 'fully_funded') {
                $paymentFlags['fully_funded'] = $paymentData;
            }
        }

        return $paymentFlags;
    }

    /**
     * Get available registration payment for a specific category
     * Used for category switching validation - now uses periods
     * 
     * @param int $programId The program ID
     * @param string $paymentType The payment type (self_funded or fully_funded)
     * @return object|null The available payment or null if not available
     */
    public function getAvailableRegistrationPayment($programId, $paymentType)
    {
        try {
            $builder = $this->builder();
            $payment = $builder
                ->where('program_id', $programId)
                ->where('category', 'registration')
                ->where('type', $paymentType)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->get()
                ->getFirstRow();

            if (!$payment) {
                return null;
            }
            
            // Check if payment has an active period
            $currentPeriod = $this->periodModel->getCurrentActivePeriod($payment->id);
            
            if ($currentPeriod) {
                // Enhance payment with current period data for compatibility
                $this->enhancePaymentWithCurrentPeriod($payment);
                return $payment;
            }
            
            return null;

        } catch (\Exception $e) {
            log_message('error', "Error getting available registration payment: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enhance a payment object with current period information for frontend compatibility
     * This method modifies the payment object to include start_date and end_date from the current period
     *
     * @param object $payment The payment object to enhance (passed by reference)
     * @return void
     */
    public function enhancePaymentWithCurrentPeriod(&$payment)
    {
        try {
            log_message('debug', "Enhancing payment {$payment->id} ({$payment->name}) with period data");
            
            // Get current active period
            $currentPeriod = $this->periodModel->getCurrentActivePeriod($payment->id);
            
            if ($currentPeriod) {
                log_message('debug', "Found active period for payment {$payment->id}: {$currentPeriod->name} ({$currentPeriod->start_date} to {$currentPeriod->end_date})");
                
                // Check if this is an extension period - if so, get effective date range
                if ($currentPeriod->parent_period_id) {
                    $effectiveRange = $this->periodModel->getEffectiveDateRange($currentPeriod->id);
                    
                    if ($effectiveRange) {
                        log_message('debug', "Period is an extension, using effective range: {$effectiveRange['start_date']} to {$effectiveRange['end_date']}");
                        
                        // Use effective date range (parent's start, extension's end)
                        $payment->start_date = $effectiveRange['start_date'];
                        $payment->end_date = $effectiveRange['end_date'];
                        $payment->current_period_name = $effectiveRange['display_label'];
                        $payment->extension_active_from = $effectiveRange['extension_active_from'];
                        $payment->is_extension_period = true;
                        $payment->extension_type = $currentPeriod->extension_type;
                    } else {
                        // Fallback to period's own dates
                        $payment->start_date = $currentPeriod->start_date;
                        $payment->end_date = $currentPeriod->end_date;
                        $payment->current_period_name = $currentPeriod->name;
                        $payment->is_extension_period = true;
                    }
                } else {
                    // Base period - use its own dates
                    $payment->start_date = $currentPeriod->start_date;
                    $payment->end_date = $currentPeriod->end_date;
                    $payment->current_period_name = $currentPeriod->name;
                    $payment->is_extension_period = false;
                }
                
                $payment->has_active_period = true;
            } else {
                log_message('debug', "No active period found for payment {$payment->id}, checking upcoming periods");
                
                // No current period, check for next upcoming period
                $nextPeriod = $this->periodModel->getNextUpcomingPeriod($payment->id);
                
                if ($nextPeriod) {
                    log_message('debug', "Found upcoming period for payment {$payment->id}: {$nextPeriod->name} ({$nextPeriod->start_date} to {$nextPeriod->end_date})");
                    
                    // Check if upcoming period is an extension
                    if ($nextPeriod->parent_period_id) {
                        $effectiveRange = $this->periodModel->getEffectiveDateRange($nextPeriod->id);
                        
                        if ($effectiveRange) {
                            $payment->start_date = $effectiveRange['start_date'];
                            $payment->end_date = $effectiveRange['end_date'];
                            $payment->current_period_name = $effectiveRange['display_label'];
                            $payment->extension_active_from = $effectiveRange['extension_active_from'];
                            $payment->is_extension_period = true;
                            $payment->extension_type = $nextPeriod->extension_type;
                        } else {
                            $payment->start_date = $nextPeriod->start_date;
                            $payment->end_date = $nextPeriod->end_date;
                            $payment->current_period_name = $nextPeriod->name;
                            $payment->is_extension_period = true;
                            $payment->extension_type = $nextPeriod->extension_type;
                        }
                    } else {
                        // Base period
                        $payment->start_date = $nextPeriod->start_date;
                        $payment->end_date = $nextPeriod->end_date;
                        $payment->current_period_name = $nextPeriod->name;
                        $payment->is_extension_period = false;
                    }
                    
                    $payment->has_active_period = false;
                    $payment->upcoming_period = true;
                } else {
                    log_message('debug', "No upcoming period found for payment {$payment->id}, checking for last ended period");
                    
                    // Check for the most recent ended period
                    $lastEndedPeriod = $this->periodModel->getLastEndedPeriod($payment->id);
                    
                    if ($lastEndedPeriod) {
                        log_message('debug', "Found last ended period for payment {$payment->id}: {$lastEndedPeriod->name} ({$lastEndedPeriod->start_date} to {$lastEndedPeriod->end_date})");
                        
                        // Check if ended period was an extension
                        if ($lastEndedPeriod->parent_period_id) {
                            $effectiveRange = $this->periodModel->getEffectiveDateRange($lastEndedPeriod->id);
                            
                            if ($effectiveRange) {
                                $payment->start_date = $effectiveRange['start_date'];
                                $payment->end_date = $effectiveRange['end_date'];
                                $payment->current_period_name = $effectiveRange['display_label'];
                                $payment->extension_active_from = $effectiveRange['extension_active_from'];
                                $payment->is_extension_period = true;
                                $payment->extension_type = $lastEndedPeriod->extension_type;
                            } else {
                                $payment->start_date = $lastEndedPeriod->start_date;
                                $payment->end_date = $lastEndedPeriod->end_date;
                                $payment->current_period_name = $lastEndedPeriod->name;
                                $payment->is_extension_period = true;
                                $payment->extension_type = $lastEndedPeriod->extension_type;
                            }
                        } else {
                            // Base period
                            $payment->start_date = $lastEndedPeriod->start_date;
                            $payment->end_date = $lastEndedPeriod->end_date;
                            $payment->current_period_name = $lastEndedPeriod->name;
                            $payment->is_extension_period = false;
                        }
                        
                        $payment->has_active_period = false;
                        $payment->upcoming_period = false;
                        $payment->last_ended_period = true;
                    } else {
                        log_message('debug', "No periods found for payment {$payment->id}");
                        
                        // No periods found, payment is not available
                        $payment->has_active_period = false;
                        $payment->upcoming_period = false;
                        $payment->last_ended_period = false;
                        $payment->current_period_name = null;
                        $payment->is_extension_period = false;
                        // Keep original dates if any exist
                    }
                }
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error enhancing payment {$payment->id} with period data: " . $e->getMessage());
            
            // Fallback: keep original payment dates and mark as no periods
            $payment->has_active_period = false;
            $payment->upcoming_period = false;
            $payment->last_ended_period = false;
            $payment->current_period_name = null;
            $payment->is_extension_period = false;
        }
    }
    
    /**
     * Get all periods for a payment (for admin management)
     *
     * @param int $paymentId The payment ID
     * @return array Array of periods
     */
    public function getPaymentPeriods($paymentId)
    {
        return $this->periodModel->getByPaymentId($paymentId, false, false);
    }
    
    /**
     * Add a new period to a payment
     *
     * @param int $paymentId The payment ID
     * @param array $periodData Period data (name, description, start_date, end_date, parent_period_id, extension_type)
     * @return array Result with success status and message
     */
    public function addPaymentPeriod($paymentId, $periodData)
    {
        try {
            $parentPeriodId = $periodData['parent_period_id'] ?? null;
            $extensionType = $periodData['extension_type'] ?? 'continuation';
            
            // Validate dates
            $validation = $this->periodModel->validatePeriodDates(
                $paymentId,
                $periodData['start_date'],
                $periodData['end_date'],
                null,
                $parentPeriodId,
                $extensionType
            );
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            // Prepare period data
            $insertData = [
                'payment_id' => $paymentId,
                'parent_period_id' => $parentPeriodId,
                'extension_type' => $extensionType,
                'name' => $periodData['name'],
                'description' => $periodData['description'] ?? '',
                'start_date' => $periodData['start_date'],
                'end_date' => $periodData['end_date'],
                'order_number' => $this->periodModel->getNextOrderNumber($paymentId),
                'is_active' => 1,
                'is_deleted' => 0
            ];
            
            $periodId = $this->periodModel->insert($insertData);
            
            if ($periodId) {
                return [
                    'success' => true,
                    'message' => 'Period added successfully',
                    'period_id' => $periodId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add period'
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error adding period to payment {$paymentId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while adding the period'
            ];
        }
    }
    
    /**
     * Update an existing period
     *
     * @param int $periodId The period ID
     * @param array $periodData Updated period data
     * @return array Result with success status and message
     */
    public function updatePaymentPeriod($periodId, $periodData)
    {
        try {
            $period = $this->periodModel->find($periodId);
            if (!$period) {
                return [
                    'success' => false,
                    'message' => 'Period not found'
                ];
            }
            
            $parentPeriodId = $periodData['parent_period_id'] ?? $period->parent_period_id;
            $extensionType = $periodData['extension_type'] ?? $period->extension_type ?? 'continuation';
            
            // Validate dates (excluding current period from overlap check)
            $validation = $this->periodModel->validatePeriodDates(
                $period->payment_id,
                $periodData['start_date'],
                $periodData['end_date'],
                $periodId,
                $parentPeriodId,
                $extensionType
            );
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            // Update period
            $updateData = [
                'name' => $periodData['name'],
                'description' => $periodData['description'] ?? $period->description,
                'start_date' => $periodData['start_date'],
                'end_date' => $periodData['end_date'],
                'parent_period_id' => $parentPeriodId,
                'extension_type' => $extensionType
            ];
            
            if (isset($periodData['is_active'])) {
                $updateData['is_active'] = $periodData['is_active'];
            }
            
            $success = $this->periodModel->update($periodId, $updateData);
            
            return [
                'success' => $success,
                'message' => $success ? 'Period updated successfully' : 'Failed to update period'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "Error updating period {$periodId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the period'
            ];
        }
    }
    
    /**
     * Delete a period (soft delete)
     *
     * @param int $periodId The period ID
     * @return array Result with success status and message
     */
    public function deletePaymentPeriod($periodId)
    {
        try {
            $period = $this->periodModel->find($periodId);
            if (!$period) {
                return [
                    'success' => false,
                    'message' => 'Period not found'
                ];
            }
            
            // Check if period can be deleted (no extensions)
            $canDelete = $this->periodModel->canDeletePeriod($periodId);
            if (!$canDelete['can_delete']) {
                return [
                    'success' => false,
                    'message' => $canDelete['message']
                ];
            }
            
            // Soft delete the period
            $success = $this->periodModel->update($periodId, ['is_deleted' => 1]);
            
            if ($success) {
                // Reorder remaining periods
                $this->periodModel->reorderPeriods($period->payment_id);
                
                return [
                    'success' => true,
                    'message' => 'Period deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete period'
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error deleting period {$periodId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the period'
            ];
        }
    }
}