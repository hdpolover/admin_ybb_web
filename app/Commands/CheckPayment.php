<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ParticipantModel;
use App\Models\PaymentModel;
use App\Models\ProgramPaymentModel;
use App\Models\UserModel;

class CheckPayment extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'YBB';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'check:payment';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Checks payment linkage for a user';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'check:payment [user_email]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'user_email' => 'The email of the participant to check',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $email = $params[0] ?? '07samha@gmail.com';
        CLI::write("Checking for email: $email", 'yellow');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            CLI::error("User with email $email not found.");
            return;
        }
        
        CLI::write("Found User ID: $user->id", 'green');

        $participantModel = new ParticipantModel();
        $participants = $participantModel->where('user_id', $user->id)->findAll();

        if (!$participants) {
            CLI::error("Participant not found for user ID $user->id.");
            return;
        }

        foreach ($participants as $participant) {
            CLI::write("\n-------------------------------------------", 'white');
            CLI::write("Participant ID: $participant->id", 'green');
            CLI::write("Program ID: $participant->program_id", 'green');
            CLI::write("Category: " . ($participant->category ?? $participant->registration_type ?? 'N/A'), 'green');

            // Fetch Payments
            $paymentModel = new PaymentModel();
            $payments = $paymentModel->where('participant_id', $participant->id)->findAll();

            CLI::write("\n--- Existing Payments ---", 'yellow');
            foreach ($payments as $p) {
                // Check if status is 1 or 2
                $statusColor = ($p->status == 2) ? 'green' : (($p->status == 1) ? 'blue' : 'red');
                CLI::write("ID: $p->id | Amount: $p->amount | Status: $p->status | ProgramPaymentID: $p->program_payment_id", $statusColor);
            }

            // Fetch Expected Program Payments
            $programPaymentModel = new ProgramPaymentModel();
            $programPayments = $programPaymentModel->where('program_id', $participant->program_id)->findAll();

            CLI::write("\n--- Requirements (Program Payments) ---", 'yellow');
            foreach ($programPayments as $pp) {
                $type = $pp->type ?? 'NULL';
                CLI::write("ID: $pp->id | Name: $pp->name | Amt: $pp->usd_amount | Category: $pp->category | ViewType: $type");
            }

            // Check Match
            CLI::write("\n--- Matching Logic ---", 'yellow');
            foreach ($programPayments as $pp) {
                $found = false;
                foreach ($payments as $p) {
                    if ($p->program_payment_id == $pp->id) {
                        $found = true;
                        $statusStr = ($p->status == 2) ? 'PAID/SUCCESS (2)' : "STATUS: $p->status";
                        CLI::write("[MATCH] Req ID $pp->id matched with Pay ID $p->id -> $statusStr", 'green');
                    }
                }
                if (!$found) {
                    // Check if there is a payment with matching amount but WRONG ID?
                    $looseMatch = false;
                    foreach ($payments as $p) {
                        if ($p->amount == $pp->usd_amount && empty($p->program_payment_id)) {
                             CLI::write("[POSSIBLE MATCH] Pay ID $p->id has amount $p->amount but NO program_payment_id!", 'magenta');
                             $looseMatch = true;
                        }
                    }
                    
                    if (!$looseMatch) {
                        // Check Visibility
                        $vType = $pp->type ?? 'all';
                        $pCat = $participant->category ?? '';
                        $visible = ($vType === 'all' || $vType === $pCat);

                        if (!$visible) {
                             CLI::write("[HIDDEN] Req ID $pp->id ($pp->name) is Unpaid but HIDDEN from user ($pCat != $vType).", 'cyan');
                        } else {
                             CLI::write("[MISSING] Req ID $pp->id ($pp->name) is Unpaid and VISIBLE.", 'red');
                        }
                    }
                }
            }
        }
    }
}
