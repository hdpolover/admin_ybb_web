<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= $pageTitle ?></h3>
            </div>
            <div class="card-body">
                <p><?= $content ?></p>
                
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Current User Information</h5>
                    <p><strong>Name:</strong> <?= $currentUser->name ?? 'N/A' ?></p>
                    <p><strong>Email:</strong> <?= $currentUser->email ?? 'N/A' ?></p>
                    <p><strong>Role:</strong> <?= ucfirst($userRole) ?></p>
                    <p><strong>User Type:</strong> <?= ucfirst($userType) ?></p>
                </div>

                <div class="mt-3">
                    <h5>Test Access Links:</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="/menu-test" class="btn btn-sm btn-primary">General Access Test</a>
                            <span class="text-muted ml-2">- All authenticated users</span>
                        </li>
                        <li class="list-group-item">
                            <a href="/menu-test/super-only" class="btn btn-sm btn-danger">Super Admin Only</a>
                            <span class="text-muted ml-2">- Super admin role required</span>
                        </li>
                        <li class="list-group-item">
                            <a href="/menu-test/program-admin" class="btn btn-sm btn-warning">Program Admin Access</a>
                            <span class="text-muted ml-2">- Program admin or super admin</span>
                        </li>
                        <li class="list-group-item">
                            <a href="/menu-test/editor" class="btn btn-sm btn-success">Editor Access</a>
                            <span class="text-muted ml-2">- Editor role or higher</span>
                        </li>
                        <li class="list-group-item">
                            <a href="/menu-test/user-info" class="btn btn-sm btn-info">User Information</a>
                            <span class="text-muted ml-2">- View detailed user and menu info</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
