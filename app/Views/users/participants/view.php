<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title'=>'Participant Details')); ?>
    <?= $this->include('partials/head-css') ?>
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
                    <?php echo view('partials/page-title', array('pagetitle'=>'Participants', 'title'=>'Participant Details')); ?>

                    <!-- Participant Details Section -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Participant Profile Image and Basic Info -->
                                        <div class="col-md-4">
                                            <div class="text-center border-end p-4">                                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                                    <img src="<?= !empty($participant->picture_url) ? $participant->picture_url : '/assets/images/users/avatar-1.jpg' ?>" 
                                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image" 
                                                        alt="Participant Profile">
                                                </div>
                                                <h5 class="fs-17 mb-1"><?= $participant->full_name ?></h5>
                                                <p class="text-muted mb-0">
                                                    <i class="ri-account-circle-line align-bottom text-primary me-1"></i> 
                                                    ID: <?= $participant->account_id ?>
                                                </p>
                                                <div class="mt-3">
                                                    <?php if (!empty($participant->category)): ?>
                                                        <?php if ($participant->category == 'fully_funded'): ?>
                                                            <span class="badge bg-success-subtle text-success">Fully Funded</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning-subtle text-warning">Self Funded</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>                                                <div class="hstack gap-2 justify-content-center mt-4">
                                                    <a href="<?= site_url('users/participants/edit/' . $participant->id) ?>" class="btn btn-primary">
                                                        <i class="ri-pencil-line align-bottom"></i> Edit Profile
                                                    </a>
                                                    <a href="<?= site_url('users/participants/export/' . $participant->id) ?>" class="btn btn-success">
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
                                                        <tbody>                                                            <tr>
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
                                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                                <i class="fas fa-user-circle"></i> Personal Details
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#educationDetails" role="tab">
                                                <i class="fas fa-graduation-cap"></i> Education & Experience
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#emergencyDetails" role="tab">
                                                <i class="fas fa-phone-alt"></i> Emergency Contact
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#essaysTab" role="tab">
                                                <i class="fas fa-file-alt"></i> Essays
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
                                                                <tbody>                                                                    <tr>
                                                                        <th scope="row" width="200">Birth Date</th>
                                                                        <td><?= !empty($participant->birthdate) ? date('d M Y', strtotime($participant->birthdate)) : 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Gender</th>
                                                                        <td><?= $participant->gender ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Origin Address</th>
                                                                        <td><?= $participant->origin_address ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Current Address</th>
                                                                        <td><?= $participant->current_address ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">T-Shirt Size</th>
                                                                        <td><?= $participant->tshirt_size ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Disease History</th>
                                                                        <td><?= $participant->disease_history ?? 'None' ?></td>
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
                                                                <tbody>                                                                    <tr>
                                                                        <th scope="row" width="200">Instagram</th>
                                                                        <td><?= $participant->instagram_account ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Twibbon Link</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->twibbon_link)): ?>
                                                                                <a href="<?= $participant->twibbon_link ?>" target="_blank">View Twibbon</a>
                                                                            <?php else: ?>
                                                                                N/A
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Knowledge Source</th>
                                                                        <td><?= $participant->knowledge_source ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Source Account</th>
                                                                        <td><?= $participant->source_account_name ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Account Created</th>
                                                                        <td><?= date('d M Y H:i', strtotime($participant->created_at)) ?></td>
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
                                                                <tbody>                                                                    <tr>
                                                                        <th scope="row" width="200">Education Level</th>
                                                                        <td><?= $participant->education_level ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Major</th>
                                                                        <td><?= $participant->major ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Institution</th>
                                                                        <td><?= $participant->institution ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Occupation</th>
                                                                        <td><?= $participant->occupation ?? 'N/A' ?></td>
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
                                                                <tbody>                                                                    <tr>
                                                                        <th scope="row" width="200">Organizations</th>
                                                                        <td><?= $participant->organizations ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Experiences</th>
                                                                        <td><?= $participant->experiences ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Achievements</th>
                                                                        <td><?= $participant->achievements ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Resume</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->resume_url)): ?>
                                                                                <a href="<?= $participant->resume_url ?>" target="_blank" class="btn btn-sm btn-soft-primary">
                                                                                    <i class="ri-file-text-line align-middle"></i> View Resume
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
                                        </div>
                                        
                                        <!-- Emergency Contact Tab -->
                                        <div class="tab-pane" id="emergencyDetails" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <h5 class="card-title mb-3">Emergency Contact Information</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless mb-0">
                                                                <tbody>                                                                    <tr>
                                                                        <th scope="row" width="200">Emergency Contact</th>
                                                                        <td><?= $participant->emergency_account ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Relation</th>
                                                                        <td><?= $participant->contact_relation ?? 'N/A' ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Phone Number</th>
                                                                        <td>
                                                                            <?php if (!empty($participant->emergency_country_code) && !empty($participant->emergency_phone_flag)): ?>
                                                                                <?= $participant->emergency_country_code ?> <?= $participant->emergency_account ?>
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
                                        </div>
                                          <!-- Essays Tab -->
                                        <div class="tab-pane" id="essaysTab" role="tabpanel">                                            <div class="row">
                                                <div class="col-12">
                                                    <?php if (!empty($participant->essays)): ?>
                                                        <div class="accordion" id="essaysAccordion">
                                                            <?php $essayCount = 0; foreach ($participant->essays as $essay): $essayCount++; ?>
                                                                <div class="accordion-item">
                                                                    <h2 class="accordion-header" id="heading<?= $essayCount ?>">
                                                                        <button class="accordion-button <?= $essayCount > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $essayCount ?>" aria-expanded="<?= $essayCount == 1 ? 'true' : 'false' ?>" aria-controls="collapse<?= $essayCount ?>">
                                                                            <strong>Essay #<?= $essayCount ?>:</strong> 
                                                                            <?= isset($essay['question']) ? htmlspecialchars(substr($essay['question'], 0, 100)) : 'Question ' . $essayCount ?>
                                                                            <?= isset($essay['question']) && strlen($essay['question']) > 100 ? '...' : '' ?>
                                                                        </button>
                                                                    </h2>
                                                                    <div id="collapse<?= $essayCount ?>" class="accordion-collapse collapse <?= $essayCount == 1 ? 'show' : '' ?>" aria-labelledby="heading<?= $essayCount ?>" data-bs-parent="#essaysAccordion">
                                                                        <div class="accordion-body">
                                                                            <div class="mb-4">
                                                                                <h5 class="fs-14 mb-1">Question:</h5>
                                                                                <p class="text-muted"><?= isset($essay['question']) ? nl2br(htmlspecialchars($essay['question'])) : 'No question available' ?></p>
                                                                            </div>
                                                                            <div>
                                                                                <h5 class="fs-14 mb-1">Answer:</h5>
                                                                                <p class="text-muted"><?= isset($essay['answer']) ? nl2br(htmlspecialchars($essay['answer'])) : 'No answer provided' ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-info mb-0">
                                                            <p class="mb-0">No essay data available for this participant.</p>
                                                        </div>
                                                    <?php endif; ?>
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

    <?= $this->include('partials/customizer') ?>
    <?= $this->include('partials/vendor-scripts') ?>
    
    <!-- App js -->
    <script src="/assets/js/app.js"></script>
</body>

</html>