<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Abstract - <?= esc($abstract->title) ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .field {
            margin-bottom: 15px;
        }
        
        .field-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .field-content {
            color: #555;
            text-align: justify;
        }
        
        .authors-list {
            margin-top: 10px;
        }
        
        .author-item {
            margin-bottom: 5px;
            padding: 5px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-submitted { background-color: #007bff; color: white; }
        .status-accepted { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-under_review { background-color: #ffc107; color: black; }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .keywords {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 6px;
            margin: 2px;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Abstract Submission</h1>
        <div class="subtitle">Youth Break the Boundaries Foundation</div>
    </div>

    <div class="field">
        <div class="field-label">Title:</div>
        <div class="field-content"><?= esc($abstract->title) ?></div>
    </div>

    <div class="field">
        <div class="field-label">Status:</div>
        <div class="field-content">
            <span class="status-badge status-<?= $abstract->status ?>">
                <?= ucfirst(str_replace('_', ' ', $abstract->status)) ?>
            </span>
        </div>
    </div>

    <div class="field">
        <div class="field-label">Submission Date:</div>
        <div class="field-content"><?= date('F j, Y', strtotime($abstract->created_at)) ?></div>
    </div>

    <?php if ($version): ?>
    <div class="field">
        <div class="field-label">Abstract Content (Version <?= $version->version_number ?>):</div>
        <div class="field-content"><?= nl2br(esc($version->content)) ?></div>
    </div>

    <?php if (!empty($version->keywords)): ?>
    <div class="field">
        <div class="field-label">Keywords:</div>
        <div class="field-content">
            <?php foreach (explode(',', $version->keywords) as $keyword): ?>
                <span class="keywords"><?= trim(esc($keyword)) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($authors)): ?>
    <div class="field">
        <div class="field-label">Authors:</div>
        <div class="authors-list">
            <?php foreach ($authors as $author): ?>
            <div class="author-item">
                <strong><?= esc($author->full_name) ?></strong>
                <?php if (!empty($author->institution)): ?>
                    <br><em><?= esc($author->institution) ?></em>
                <?php endif; ?>
                <?php if (!empty($author->email)): ?>
                    <br><?= esc($author->email) ?>
                <?php endif; ?>
                <?php if ($author->is_participant): ?>
                    <br><small><strong>Participant</strong></small>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="field">
        <div class="field-label">Participant Information:</div>
        <div class="field-content">
            <strong>Name:</strong> <?= esc($abstract->participant_name ?? 'N/A') ?><br>
            <strong>Institution:</strong> <?= esc($abstract->institution ?? 'N/A') ?><br>
            <strong>Email:</strong> <?= esc($abstract->email ?? 'N/A') ?>
        </div>
    </div>

    <div class="footer">
        Generated on <?= date('F j, Y \a\t g:i A') ?> | Youth Break the Boundaries Foundation | Abstract ID: <?= $abstract->id ?>
    </div>
</body>
</html>
