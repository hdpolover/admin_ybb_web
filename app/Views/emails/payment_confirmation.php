<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 3px 15px rgba(30, 32, 37, 0.06);
        }
        .header { 
            background-color: #f8f9fa; 
            padding: 20px; 
            text-align: center; 
            border-bottom: 1px solid #e9ebec;
        }
        .logo {
            margin-bottom: 15px;
        }
        .content { 
            padding: 30px 20px; 
        }
        .payment-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .payment-details ul {
            list-style: none;
            padding-left: 0;
        }
        .payment-details li {
            margin-bottom: 8px;
        }
        .footer { 
            background-color: #f8f9fa; 
            padding: 15px; 
            text-align: center; 
            font-size: 12px;
            color: #878a99;
            border-top: 1px solid #e9ebec;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <?php if (isset($logo) && !empty($logo)): ?>
                <div class="logo">
                    <img src="<?= $logo ?>" alt="Logo" height="30">
                </div>
            <?php else: ?>
                <h2>Your Brilliant Brand</h2>
            <?php endif; ?>
            <h2>Payment Confirmation</h2>
        </div>
        <div class="content">
            <p>Dear <?= $participant_name ?>,</p>
            
            <p>We are pleased to confirm that your payment has been successfully processed.</p>
            
            <div class="payment-details">
                <p><strong>Payment Details:</strong></p>
                <ul>
                    <li><strong>Program:</strong> <?= $program_name ?></li>
                    <li><strong>Transaction ID:</strong> <?= $transaction_id ?></li>
                    <li><strong>Order ID:</strong> <?= $order_id ?></li>
                    <li><strong>Amount:</strong> <?= $formatted_amount ?></li>
                    <li><strong>Date:</strong> <?= $payment_date ?></li>
                </ul>
            </div>
            
            <p>Thank you for your payment. You now have full access to the program.</p>
            
            <p>If you have any questions, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br><?= isset($program_name) ? $program_name : 'Your Brilliant Brand' ?> Team</p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> <?= isset($organization_name) ? $organization_name : 'Your Brilliant Brand' ?>. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
