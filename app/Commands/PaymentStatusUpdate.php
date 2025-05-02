<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\PaymentModel;
use App\Models\PaymentMethodModel;

/**
 * Command to update pending payment statuses to cancelled if they have not been updated to success in 1 hour
 * For gateway type payment methods only
 */
class PaymentStatusUpdate extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Payments';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'payments:update-status';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Updates pending payment statuses to cancelled if they have not been updated to success in 1 hour (for gateway payment methods only)';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'payments:update-status';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--dry-run' => 'Show payments that would be cancelled without actually updating them',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $dryRun = array_key_exists('dry-run', $params);
        
        $paymentModel = new PaymentModel();
        $paymentMethodModel = new PaymentMethodModel();
        
        // Get all gateway payment methods
        $gatewayPaymentMethods = $paymentMethodModel->where('type', 'gateway')
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->findAll();
        
        if (empty($gatewayPaymentMethods)) {
            CLI::write('No gateway payment methods found.', 'yellow');
            return;
        }
        
        // Extract gateway payment method IDs
        $gatewayPaymentMethodIds = array_map(function($method) {
            return $method->id;
        }, $gatewayPaymentMethods);
        
        // Get pending payments that are older than 1 hour and from gateway payment methods
        $hourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $pendingPayments = $paymentModel->builder()
            ->select('payments.*')
            ->where('payments.status', 1) // Pending status (1)
            ->where('payments.created_at <', $hourAgo)
            ->whereIn('payments.payment_method_id', $gatewayPaymentMethodIds)
            ->get()->getResult();
        
        if (empty($pendingPayments)) {
            CLI::write('No pending payments found that need to be cancelled.', 'green');
            return;
        }
        
        CLI::write('Found ' . count($pendingPayments) . ' pending payment(s) to process:', 'yellow');
        
        $table = [];
        foreach ($pendingPayments as $payment) {
            $table[] = [
                'ID' => $payment->id,
                'Method ID' => $payment->payment_method_id,
                'Amount' => $payment->amount,
                'Created At' => $payment->created_at,
                'Status' => 'Pending -> Cancelled'
            ];
            
            if (!$dryRun) {
                // Update payment status to cancelled (3)
                $notes = "Payment automatically cancelled after 1 hour of pending status";
                $paymentModel->updatePaymentStatus($payment->id, 3, $notes);
            }
        }
        
        // Display the results
        CLI::table($table, ['ID', 'Method ID', 'Amount', 'Created At', 'Status']);
        
        if ($dryRun) {
            CLI::write('DRY RUN - No payments were actually cancelled', 'yellow');
        } else {
            CLI::write('Successfully cancelled ' . count($pendingPayments) . ' pending payment(s)', 'green');
        }
    }
}
