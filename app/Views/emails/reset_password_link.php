<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
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
        .reset-button {
            text-align: center;
            margin: 25px 0;
        }
        .reset-button a {
            display: inline-block;
            padding: 12px 24px;
            background-color: #405189;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
        }
        .reset-link {
            word-break: break-all;
            margin: 20px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
            font-size: 14px;
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
        .expiry-info {
            font-size: 13px;
            color: #666;
            font-style: italic;
            text-align: center;
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
            <h2>Password Reset Request</h2>
        </div>
        
        <p>Hello,</p>
        
        <p>We received a request to reset your password. Click the button below to reset your password:</p>
        
        <div class="reset-button">
            <a href="<?= $reset_link ?>" target="_blank">Reset Password</a>
        </div>
        
        <p>If the button doesn't work, copy and paste the following URL into your browser:</p>
        
        <div class="reset-link"><?= $reset_link ?></div>
        
        <p class="expiry-info">This password reset link will expire in 24 hours.</p>
        
        <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
        
        <p>Thank you,<br><?= $name ?? 'The Support' ?> Team</p>
        
        <?php if (!empty($contact) || !empty($location) || !empty($email_contact)): ?>
        <div class="contact-info">
            <?php if (!empty($contact)): ?><p><strong>Contact:</strong> <?= $contact ?></p><?php endif; ?>
            <?php if (!empty($location)): ?><p><strong>Location:</strong> <?= $location ?></p><?php endif; ?>
            <?php if (!empty($email_contact)): ?><p><strong>Email:</strong> <?= $email_contact ?></p><?php endif; ?>
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