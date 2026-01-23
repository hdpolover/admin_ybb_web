<?php
define('FCPATH', __DIR__ . '/public');
define('APPPATH', __DIR__ . '/app');

// Simulate CodeIgniter environment
require_once __DIR__ . '/public/index.php';

use App\Models\ParticipantModel;
use App\Models\PaymentModel;
use App\Models\ProgramPaymentModel;

$userId = 211244; // This is actually Participant ID based on context, let's verify.
// In previous turns, 211244 was referred to as ID. Email 07samha@gmail.com.

$participantModel = new ParticipantModel();
$participant = $participantModel->find($userId);

if (!$participant) {
    echo "Participant ID $userId not found.\n";
    // Try by email
    $participant = $participantModel->where('email', '07samha@gmail.com')->first();
    if ($participant) {
        echo "Found participant by email. ID: " . $participant->id . "\n";
        $userId = $participant->id;
    } else {
        exit("Participant not found.\n");
    }
}

echo "Participant Details:\n";
print_r($participant);

$paymentModel = new PaymentModel();
$payments = $paymentModel->where('participant_id', $userId)->findAll();

echo "\nParticipant Payments:\n";
foreach ($payments as $p) {
    echo "ID: " . $p->id . ", ProgramPaymentID: " . $p->program_payment_id . ", Status: " . $p->status . ", Amount: " . $p->amount . "\n";
}

// Check which Program Payments are expected for this participant's program
$programId = $participant->program_id;
$programPaymentModel = new ProgramPaymentModel();
$programPayments = $programPaymentModel->where('program_id', $programId)->findAll();

echo "\nExpected Program Payments for Program ID $programId:\n";
foreach ($programPayments as $pp) {
    echo "ID: " . $pp->id . ", Name: " . $pp->name . ", Category: " . $pp->category . ", Amount: " . $pp->usd_amount . "\n";
}

// Check matching
echo "\nMatching Logic Check:\n";
foreach ($programPayments as $pp) {
    $matchFound = false;
    foreach ($payments as $p) {
        if ($p->program_payment_id == $pp->id) {
            echo "[MATCH] Payment " . $p->id . " matches Requirement " . $pp->id . ". Status: " . $p->status . "\n";
            $matchFound = true;
        }
    }
    if (!$matchFound) {
        echo "[MISSING] No payment found for Requirement " . $pp->id . " (" . $pp->name . ")\n";
    }
}
