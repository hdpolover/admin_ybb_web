<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .verification-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .verification-button {
            text-align: center;
            margin: 30px 0;
        }
        .verification-button a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
            height: auto;
        }
        .program-name {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .tagline {
            font-style: italic;
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }
        .contact-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            font-size: 13px;
        }
        .social-links {
            text-align: center;
            margin-top: 15px;
        }
        .social-links a {
            margin: 0 5px;
            color: #0066cc;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($logo_url)): ?>
        <div class="logo">
            <img src="<?= $logo_url ?>" alt="<?= $name ?? 'Company Logo' ?>">
        </div>
        <?php endif; ?>
        
        <?php if (!empty($name)): ?>
        <div class="program-name"><?= $name ?></div>
        <?php endif; ?>
        
        <?php if (!empty($tagline)): ?>
        <div class="tagline"><?= $tagline ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h2>Verify Your Email Address</h2>
        </div>
        
        <p>Hello,</p>
        
        <p>Thank you for registering. To complete your registration and activate your account, please verify your email address by clicking the button below:</p>
        
        <div class="verification-button">
            <a href="<?= $verification_url ?>">Verify Email Address</a>
        </div>
        
        <p>Or, if you prefer, you can enter this verification code on our website:</p>
        
        <div class="verification-code"><?= $verification_token ?></div>
        
        <p>If you did not create an account, please ignore this email.</p>
        
        <p>Thank you,<br><?= $name ?? 'The Support' ?> Team</p>
        
        <?php if (!empty($contact) || !empty($location) || !empty($email)): ?>
        <div class="contact-info">
            <?php if (!empty($contact)): ?><p><strong>Contact:</strong> <?= $contact ?></p><?php endif; ?>
            <?php if (!empty($location)): ?><p><strong>Location:</strong> <?= $location ?></p><?php endif; ?>
            <?php if (!empty($email)): ?><p><strong>Email:</strong> <?= $email ?></p><?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($instagram) || !empty($tiktok) || !empty($youtube) || !empty($telegram)): ?>
        <div class="social-links">
            <?php if (!empty($instagram)): ?><a href="https://instagram.com/<?= $instagram ?>">Instagram</a><?php endif; ?>
            <?php if (!empty($tiktok)): ?><a href="https://tiktok.com/@<?= $tiktok ?>">TikTok</a><?php endif; ?>
            <?php if (!empty($youtube)): ?><a href="<?= $youtube ?>">YouTube</a><?php endif; ?>
            <?php if (!empty($telegram)): ?><a href="https://t.me/<?= $telegram ?>">Telegram</a><?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>