<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Participant Details')); ?>
    <?= $this->include('partials/head-css') ?>

    <!-- Custom CSS for Essays Section -->
    <style>
    .essay-content {
        background-color: #f8f9fa;
        border-radius: 8px;
        position: relative;
    }

    .essay-text {
        font-size: 0.95rem;
        line-height: 1.6;
        text-align: justify;
    }

    .essay-collection .card {
        transition: all 0.3s ease;
        border-left: 4px solid #0ab39c !important;
    }

    .essay-collection .card:hover {
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Participants', 'title' => 'Participant Details')); ?>

                    <!-- Participant Details Section -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Participant Profile Image and Basic Info -->
                                        <div class="col-md-4">
                                            <div class="text-center border-end p-4">
                                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                                    <img src="<?= !empty($participant->picture_url) ? $participant->picture_url : '/assets/images/users/avatar-1.jpg' ?>"
                                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                                        alt="Participant Profile">
                                                </div>
                                                <h5 class="fs-17 mb-1"><?= $participant->full_name ?></h5>
                                                <p class="text-muted mb-0">
                                                    <i
                                                        class="ri-account-circle-line align-bottom text-primary me-1"></i>
                                                    ID: <?= $participant->account_id ?>
                                                </p>
                                                <div class="mt-3">
                                                    <?php if (!empty($participant->category)): ?>
                                                    <?php if ($participant->category == 'fully_funded'): ?>
                                                    <span class="badge bg-success-subtle text-success">Fully
                                                        Funded</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning">Self
                                                        Funded</span>
                                                    <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="hstack gap-2 justify-content-center mt-4">
                                                    <a href="<?= site_url('users/participants/edit/' . $participant->id) ?>"
                                                        class="btn btn-primary">
                                                        <i class="ri-pencil-line align-bottom"></i> Edit Profile
                                                    </a>
                                                    <a href="<?= site_url('users/participants/export/' . $participant->id) ?>"
                                                        class="btn btn-success">
                                                        <i class="ri-file-excel-2-line align-bottom"></i> Export Data
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Participant Quick Information -->
                                        <div class="col-md-8">
                                            <div class="p-4">
                                                <h5 class="card-title mb-4">Contact Information</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Full Name</th>
                                                                <td><?= $participant->full_name ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Email</th>
                                                                <td><?= $participant->user->email ?? 'N/A' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Phone</th>
                                                                <td><?= $participant->phone_number ?? 'N/A' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Nationality</th>
                                                                <td><?= $participant->nationality ?? 'N/A' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Gender</th>
                                                                <td><?= $participant->gender ?? 'N/A' ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Institution</th>
                                                                <td><?= $participant->institution ?? 'N/A' ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Section -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0"
                                        role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails"
                                                role="tab">
                                                <i class="fas fa-user-circle"></i> Personal Details
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#educationDetails"
                                                role="tab">
                                                <i class="fas fa-graduation-cap"></i> Education & Experience
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#emergencyDetails"
                                                role="tab">
                                                <i class="fas fa-phone-alt"></i> Emergency Contact
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#essaysTab" role="tab">
                                                <i class="fas fa-file-alt"></i> Essays
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#scoresTab" role="tab">
                                                <i class="fas fa-file-alt"></i> Scores
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="card-body p-4">
                                    <div class="tab-content">
                                        <!-- Personal Details Tab -->
                                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Personal Information</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="200">Birth Date</th>
                                                                        <td><?= !empty($participant->birthdate) ? date('d M Y', strtotime($participant->birthdate)) : 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Gender</th>
                                                                        <td><?= $participant->gender ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Origin Address</th>
                                                                        <td><?= $participant->origin_address ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Current Address</th>
                                                                        <td><?= $participant->current_address ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">T-Shirt Size</th>
                                                                        <td><?= $participant->tshirt_size ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Disease History</th>
                                                                        <td><?= $participant->disease_history ?? 'None' ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Social Media & Others</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="200">Instagram</th>
                                                                        <td><?= $participant->instagram_account ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Twibbon Link</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->twibbon_link)): ?>
                                                                            <a href="<?= $participant->twibbon_link ?>"
                                                                                target="_blank">View Twibbon</a>
                                                                            <?php else: ?>
                                                                            N/A
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Knowledge Source</th>
                                                                        <td><?= $participant->knowledge_source ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Source Account</th>
                                                                        <td><?= $participant->source_account_name ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Account Created</th>
                                                                        <td><?= date('d M Y H:i', strtotime($participant->created_at)) ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Education & Experience Tab -->
                                        <div class="tab-pane" id="educationDetails" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Education</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="200">Education Level</th>
                                                                        <td><?= $participant->education_level ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Major</th>
                                                                        <td><?= $participant->major ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Institution</th>
                                                                        <td><?= $participant->institution ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Occupation</th>
                                                                        <td><?= $participant->occupation ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Experience & Achievements</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="200">Organizations</th>
                                                                        <td><?= $participant->organizations ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Experiences</th>
                                                                        <td><?= $participant->experiences ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Achievements</th>
                                                                        <td><?= $participant->achievements ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Resume</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->resume_url)): ?>
                                                                            <a href="<?= $participant->resume_url ?>"
                                                                                target="_blank"
                                                                                class="btn btn-sm btn-soft-primary">
                                                                                <i
                                                                                    class="ri-file-text-line align-middle"></i>
                                                                                View Resume
                                                                            </a>
                                                                            <?php else: ?>
                                                                            Not uploaded
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <h5 class="card-title mb-3">Assessment Form</h5>
                                                    <form action="<?= base_url('scorings/fully_funded/saveScore') ?>"
                                                        method="post">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Component</th>
                                                                    <th>Weight (%)</th>
                                                                    <th width="15%">Score</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($participant->scores1)): ?>
                                                                <?php 
                                                                $no = 0;
                                                                foreach ($participant->scores1 as $score): 
                                                                $no++;
                                                                ?>
                                                                <tr>
                                                                    <td>A.<?=$no?>
                                                                        &emsp;<?= esc($score->component_name) ?>
                                                                    </td>
                                                                    <td><?= esc($score->weight)*100 ?>%</td>
                                                                    <td>
                                                                        <input type="hidden" name="participant_id"
                                                                            value="<?= $participant->id ?>">
                                                                        <input type="hidden" name="score_weight_id[]"
                                                                            value="<?= $score->component_id ?>">
                                                                        <input type="hidden" name="weight[]"
                                                                            value="<?= $score->weight ?>">
                                                                        <input type="hidden" name="weight2[]"
                                                                            value="<?= $score->weight2 ?>">
                                                                        <input type="number" class="form-control"
                                                                            id="basiInput" name="score_input[]"
                                                                            value="<?= esc($score->value) ?>">
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach ?>
                                                                <tr>
                                                                    <td colspan="2"></td>
                                                                    <td>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Submit</button>
                                                                    </td>
                                                                </tr>
                                                                <?php else: ?>
                                                                <tr>
                                                                    <td colspan="3" class="text-center">Belum ada
                                                                        komponen
                                                                        penilaian</td>
                                                                </tr>
                                                                <?php endif ?>
                                                            </tbody>
                                                        </table>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Emergency Contact Tab -->
                                        <div class="tab-pane" id="emergencyDetails" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Emergency Contact Information</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="200">Emergency Contact
                                                                        </th>
                                                                        <td><?= $participant->emergency_account ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Relation</th>
                                                                        <td><?= $participant->contact_relation ?? 'N/A' ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Phone Number</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->emergency_country_code) && !empty($participant->emergency_phone_flag)): ?>
                                                                            <?= $participant->emergency_country_code ?>
                                                                            <?= $participant->emergency_account ?>
                                                                            <?php else: ?>
                                                                            <?= $participant->emergency_account ?? 'N/A' ?>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- Essays Tab -->
                                        <div class="tab-pane" id="essaysTab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <!-- Subtheme Section with Icon -->
                                                    <?php if (!empty($participant->subtheme)): ?>
                                                    <div class="card border mb-4">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0 me-3">
                                                                    <i class="ri-focus-2-line text-primary fs-24"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h5 class="fs-16 mb-1">Selected Subtheme</h5>
                                                                    <p class="mb-0">
                                                                        <span
                                                                            class="badge bg-primary-subtle text-primary fs-13"><?= htmlspecialchars($participant->subtheme->subtheme_name) ?></span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>

                                                    <!-- Essays Collection -->
                                                    <?php if (!empty($participant->essays)): ?>
                                                    <h5 class="mb-3">Essay Responses</h5>
                                                    <div class="essay-collection">
                                                        <?php $essayCount = 1; ?>
                                                        <?php foreach ($participant->essays as $essay): ?>
                                                        <div class="card border mb-4">
                                                            <div class="card-header bg-light">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="avatar-sm">
                                                                            <div
                                                                                class="avatar-title rounded-circle bg-primary text-white">
                                                                                <?= $essayCount ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h5 class="card-title mb-0">
                                                                            <?= isset($essay['question']) ? nl2br(htmlspecialchars($essay['question'])) : 'No question available' ?>
                                                                        </h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (isset($essay['answer']) && !empty($essay['answer'])): ?>
                                                                <div class="essay-content p-2">
                                                                    <div class="position-relative">
                                                                        <div class="essay-icon position-absolute"
                                                                            style="top: 0; left: -10px; opacity: 0.1">
                                                                            <i
                                                                                class="ri-double-quotes-l fs-35 text-primary"></i>
                                                                        </div>
                                                                        <div class="ps-4 pt-2 pb-2 essay-text">
                                                                            <?= nl2br(htmlspecialchars($essay['answer'])) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Word count indicator -->
                                                                <div class="d-flex justify-content-end mt-3">
                                                                    <span class="badge bg-soft-secondary text-dark">
                                                                        <i class="ri-text me-1"></i>
                                                                        <?= str_word_count($essay['answer']) ?> words
                                                                    </span>
                                                                </div>
                                                                <?php else: ?>
                                                                <div class="alert alert-warning mb-0">
                                                                    <i class="ri-error-warning-line me-2"></i>No answer
                                                                    provided
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php $essayCount++; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php else: ?>
                                                    <div class="alert alert-info d-flex align-items-center mb-0"
                                                        role="alert">
                                                        <i class="ri-information-line me-3 fs-16"></i>
                                                        <div>
                                                            No essay data available for this participant.
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7">
                                                    <h5 class="card-title mb-3">Assessment Form</h5>
                                                    <form action="<?= base_url('scorings/fully_funded/saveScore') ?>"
                                                        method="post">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Component</th>
                                                                    <th>Weight (%)</th>
                                                                    <th width="15%">Score</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($participant->scores2)): ?>
                                                                <?php 
                                                                $no = 0;
                                                                foreach ($participant->scores2 as $score): 
                                                                $no++;
                                                                ?>
                                                                <tr>
                                                                    <td>B.<?=$no?>
                                                                        &emsp;<?= esc($score->component_name) ?>
                                                                    </td>
                                                                    <td><?= esc($score->weight)*100 ?>%</td>
                                                                    <td>
                                                                        <input type="hidden" name="participant_id"
                                                                            value="<?= $participant->id ?>">
                                                                        <input type="hidden" name="score_weight_id[]"
                                                                            value="<?= $score->component_id ?>">
                                                                        <input type="hidden" name="weight[]"
                                                                            value="<?= $score->weight ?>">
                                                                        <input type="hidden" name="weight2[]"
                                                                            value="<?= $score->weight2 ?>">
                                                                        <input type="number" class="form-control"
                                                                            id="basiInput" name="score_input[]"
                                                                            value="<?= esc($score->value) ?>">
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach ?>
                                                                <tr>
                                                                    <td colspan="2"></td>
                                                                    <td>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Submit</button>
                                                                    </td>
                                                                </tr>
                                                                <?php else: ?>
                                                                <tr>
                                                                    <td colspan="3" class="text-center">Belum ada
                                                                        komponen
                                                                        penilaian</td>
                                                                </tr>
                                                                <?php endif ?>
                                                            </tbody>
                                                        </table>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="scoresTab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Assessment Form</h5>
                                                        <div class="table">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" width="120">
                                                                            Score
                                                                        </th>
                                                                        <td>
                                                                            <?php if (!empty($participant->scores1)): ?>
                                                                            <?php 
                                                                                $no = 0;
                                                                                foreach ($participant->scores1 as $score): 
                                                                                $no++;
                                                                                ?>
                                                                            <div class="row mb-1">
                                                                                <label for="nama"
                                                                                    class="col-2 col-xl-2 col-form-label">
                                                                                    A.<?=$no?>
                                                                                    <!-- &emsp;<?= esc($score->component_name) ?> -->
                                                                                </label>
                                                                                <div class="col-4 col-xl-4">
                                                                                    <input type="text"
                                                                                        class="form-control" id="nama"
                                                                                        placeholder="score"
                                                                                        value="<?= esc($score->calc) ?>"
                                                                                        name="a<?=$no?>">
                                                                                </div>
                                                                                <label for="nama"
                                                                                    class="col-6 col-xl-6 col-form-label">(Score
                                                                                    Input *
                                                                                    <?=esc($score->weight)*100?>%) *
                                                                                    <?=esc($score->weight2)*100?>%
                                                                                </label>
                                                                            </div>

                                                                            <?php endforeach ?>
                                                                            <?php else: ?>

                                                                            Belum ada komponen penilaian
                                                                            <?php endif ?>
                                                                            <?php if (!empty($participant->scores2)): ?>
                                                                            <?php 
                                                                                $no = 0;
                                                                                foreach ($participant->scores2 as $score): 
                                                                                $no++;
                                                                                ?>
                                                                            <div class="row mb-1">
                                                                                <label for="nama"
                                                                                    class="col-2 col-xl-2 col-form-label">
                                                                                    B.<?=$no?>
                                                                                    <!-- &emsp;<?= esc($score->component_name) ?> -->
                                                                                </label>
                                                                                <div class="col-4 col-xl-4">
                                                                                    <input type="text"
                                                                                        class="form-control" id="nama"
                                                                                        placeholder="score"
                                                                                        value="<?= esc($score->calc) ?>"
                                                                                        name="b<?=$no?>">
                                                                                </div>
                                                                                <label for="nama"
                                                                                    class="col-6 col-xl-6 col-form-label">(Score
                                                                                    Input *
                                                                                    <?=esc($score->weight)*100?>%) *
                                                                                    <?=esc($score->weight2)*100?>%
                                                                                </label>
                                                                            </div>

                                                                            <?php endforeach ?>
                                                                            <?php else: ?>

                                                                            Belum ada komponen penilaian
                                                                            <?php endif ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Total</th>
                                                                        <td>
                                                                            <div class="row mb-1">
                                                                                <label for="nama"
                                                                                    class="col-2 col-xl-2 col-form-label">
                                                                                </label>
                                                                                <div class="col-4 col-xl-4">
                                                                                    <input type="text"
                                                                                        class="form-control" id="nama"
                                                                                        placeholder="score"
                                                                                        value="<?=$participant->score_total?>">
                                                                                </div>
                                                                                <label for="nama"
                                                                                    class="col-6 col-xl-6 col-form-label">
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Status</th>
                                                                        <td>
                                                                            <div class="row mb-1">
                                                                                <label for="nama"
                                                                                    class="col-2 col-xl-2 col-form-label">
                                                                                </label>
                                                                                <div class="col-4 col-xl-4">
                                                                                    <span
                                                                                        class="badge bg-success-subtle text-success"><b><?=$participant->score_status?></b></span>
                                                                                </div>
                                                                                <label for="nama"
                                                                                    class="col-6 col-xl-6 col-form-label">
                                                                                </label>
                                                                            </div>

                                                                        </td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?= $this->include('partials/footer') ?>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <?= $this->include('partials/vendor-scripts') ?>

    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>