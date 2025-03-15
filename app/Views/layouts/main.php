<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin YBB Web' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/datatables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/select2-bootstrap4.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sweetalert2.min.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?= $this->include('layouts/navbar') ?>
    <div class="container-fluid">
        <div class="row">
            <?= $this->include('layouts/sidebar') ?>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>
    <script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/datatables.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/select2.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/sweetalert2.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/custom.js') ?>"></script>
</body>

</html>