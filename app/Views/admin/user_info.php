<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current User Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Name</th>
                        <td><?= $currentUser->name ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= $currentUser->email ?? 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?= ucfirst($userRole) ?></td>
                    </tr>
                    <tr>
                        <th>User Type</th>
                        <td><?= ucfirst($userType) ?></td>
                    </tr>
                    <tr>
                        <th>Program ID</th>
                        <td><?= $currentUser->program_id ?? 'N/A' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Roles</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Admin Roles:</h6>
                    <ul class="list-group">
                        <?php foreach ($availableRoles as $role): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= ucfirst($role) ?>
                                <?php if ($role === $userRole): ?>
                                    <span class="badge badge-primary badge-pill">Current</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div>
                    <h6>Reviewer Roles:</h6>
                    <ul class="list-group">
                        <?php foreach ($reviewerRoles as $role): ?>
                            <li class="list-group-item">
                                <?= ucfirst($role) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current Menu Structure</h3>
            </div>
            <div class="card-body">
                <div class="tree">
                    <?php $this->renderMenuStructure($menuItems); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current Breadcrumb</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($breadcrumb)): ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <?php foreach ($breadcrumb as $index => $item): ?>
                                <?php if ($index == count($breadcrumb) - 1): ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?= $item['label'] ?></li>
                                <?php else: ?>
                                    <li class="breadcrumb-item">
                                        <?php if (isset($item['url'])): ?>
                                            <a href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
                                        <?php else: ?>
                                            <?= $item['label'] ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php else: ?>
                    <p class="text-muted">No breadcrumb available for this page.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
function renderMenuStructure($items, $level = 0) {
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    foreach ($items as $item) {
        $activeClass = ($item['is_active'] ?? false) ? 'text-primary font-weight-bold' : '';
        $hasActiveChild = ($item['has_active_child'] ?? false) ? 'text-info' : '';
        $class = $activeClass ?: $hasActiveChild;
        
        echo '<div class="' . $class . '">';
        echo $indent;
        if (isset($item['icon'])) {
            echo '<i class="' . $item['icon'] . '"></i> ';
        }
        echo $item['label'];
        if (isset($item['url'])) {
            echo ' <small class="text-muted">(' . $item['url'] . ')</small>';
        }
        echo '</div>';
        
        if (isset($item['children'])) {
            renderMenuStructure($item['children'], $level + 1);
        }
    }
}
?>

<?= $this->endSection() ?>
