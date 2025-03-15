<?php

/**
 * Program Card Component
 * Displays a program card with logo, name, and status
 * 
 * @param object $program - Program data object with properties: 'logo', 'name', 'status'
 */

// Status classes
$statusClasses = [
    '1' => 'bg-success',
    '0' => 'bg-danger',
];

$status = $program->is_active ?? '0';
$statusClass = isset($statusClasses[$status]) ? $statusClasses[$status] : 'bg-light';

// format status text
$statusText = [
    '1' => 'Active',
    '0' => 'Inactive',
];

$statusText = isset($statusText[$status]) ? $statusText[$status] : 'Unknown';

?>

<a href="<?= site_url('welcome/set_program/' . $program->id) ?>" class="text-decoration-none text-dark">
    <div class="program-card card shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center">
                <div class="program-logo me-3">
                    <img src="<?= esc($program->logo_url) ?>" alt="<?= esc($program->name) ?> Logo"
                        class="rounded" width="auto" height="90">
                </div>
                <div class="program-info flex-grow-1">
                    <h5 class="program-name mb-1"><?= esc($program->name) ?></h5>
                    <div class="program-date-range text-muted mb-1">
                        <small>
                            <i class="bi bi-calendar"></i>
                            <?= format_date_range($program->start_date, $program->end_date) ?>
                        </small>
                    </div>
                    <div class="program-status">
                        <span class="badge <?= $statusClass ?> text-uppercase"><?= $statusText ?></span>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</a>

<!-- End Program Card -->

<style>
    .program-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: transform 0.2s;
        cursor: pointer;
    }

    .program-card:hover {
        transform: scale(1.02);
    }

    .program-logo img {
        max-width: 100%;
        height: 150;
    }
    .program-name {
        font-size: 1.25rem;
        font-weight: 500;
    }
    .program-date-range {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .program-status {
        font-size: 0.875rem;
    }
    .program-status .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
    }
    .program-status .badge.bg-success {
        background-color: #28a745;
    }
    .program-status .badge.bg-danger {
        background-color: #dc3545;
    }
    .program-status .badge.bg-light {
        background-color: #f8f9fa;
        color: #212529;
    }
</style>


