<?php

/**
 * Main LOA Template
 * 
 * This template defines the structure for Letter of Agreement (LOA) PDFs
 * It includes the header with logo, content area, and standardized footer
 * 
 * @param string $logoImg - HTML for the logo image (either program specific or default)
 * @param string $bodyContent - The main content of the LOA with all variables replaced
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $programData['name'] ?> - Letter of Acceptance</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            position: relative;
            min-height: 100%;
        }

        .container {
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
            padding: 10px 10px 100px 10px; /* Added extra padding at bottom for footer */
            position: relative;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }       
        
        /* Logo styling */
        .loa-logo { 
            height: 50px;
            max-width: 120px;
            display: inline-block;
        }
        
        /* Signature styling */
        .loa-signature {
            height: 60px;
            max-width: 100px;
            display: inline-block;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            padding-bottom: 20px;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .preview-signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            width: 45%;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        h1 {
            font-size: 14pt;
            margin-top: 0;
            margin-bottom: 12px;
        }

        h2 {
            font-size: 12pt;
            margin-top: 0;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 11pt;
            margin-top: 0;
            margin-bottom: 8px;
        }

        ul,
        ol {
            margin-bottom: 12px;
            padding-left: 20px;
        }

        li {
            margin-bottom: 4px;
        }

        p {
            margin-bottom: 8px;
        }

        .content {
            font-size: 10pt;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-info {
            max-width: 60%;
        }

        .signature-image {
            margin-bottom: 5px;
        }

        .signature-details {
            font-size: 10pt;
            line-height: 1.3;
        }

        .signature-stamp {
            text-align: right;
        }

        /* Include Quill editor styles */
        <?= isset($quillStyles) ? $quillStyles : '' ?>
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 30%; text-align: left; vertical-align: middle;">
                        <?= $logoImg ?>
                    </td>
                    <td style="width: 40%; text-align: center; vertical-align: middle;">
                        <h2 style="margin: 0;"><?= strtoupper($programData['name']) ?></h2>
                        <p style="margin: 5px 0 0 0; font-style: italic;"><?= $programData['tagline'] ?></p>
                    </td>
                    <td style="width: 30%; text-align: right; vertical-align: middle; font-size: 9pt;">
                        <p style="margin: 0;"><?= $programData['web_url'] ?></p>
                        <p style="margin: 3px 0 0 0;"><?= $programData['email']  ?></p>
                        <p style="margin: 3px 0 0 0;"><?= $programData['contact']  ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <?= $bodyContent ?>
        </div>
        
        <div class="signature-section">
            <div class="signature-info">
                <p style="margin-bottom: 10px;">Sincerely,</p>
                <!-- Stacked signature stamp moved between "Sincerely" and signature details -->
                <div class="signature-stamp" style="margin: 8px 0 15px 0;">
                    <div style="text-align: left; position: relative;">                        
                        <!-- Overlaid signature image and logo (truly stacked on top of each other) -->
                        <div style="position: relative; width: 120px; height: 60px;">                            <div style="position: absolute; top: 0; left: 0; z-index: 2; opacity: 0.9;">
                                <?= $signatureImg ?>
                            </div>
                            <div style="position: absolute; top: 15px; left: 0; z-index: 1; opacity: 0.7;">
                                <?= $logoImg ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="signature-details">
                    <strong>Muhammad Aldi Subakti</strong><br>
                    <span>Chairman of <?= isset($programData['name']) ? $programData['name'] : 'YBB' ?></span><br>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This document is computer-generated. No physical signature required.</p>
            <p><?= isset($programData['main_name']) ? $programData['main_name'] : 'Program' ?> • Generated on <?= date('F d, Y') ?></p>
        </div>
    </div>
</body>

</html>