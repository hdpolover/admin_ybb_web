<?= $this->include('partials/main') ?>

<head>
    <?php echo view('partials/title-meta', array('title' => 'Edit Participant')); ?>
    <?= $this->include('partials/head-css') ?>
</head>

<body>
    <div id="layout-wrapper">
        <?= $this->include('partials/menu') ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php echo view('partials/page-title', array('pagetitle' => 'Participants', 'title' => 'Edit Participant')); ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('users/participants/update/' . $participant['id']) ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="ri-user-line me-2"></i>Personal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control text-uppercase" id="full_name" name="full_name"
                                                        value="<?= htmlspecialchars($participant['full_name'] ?? '') ?>" required maxlength="25"
                                                        oninput="this.value = this.value.toUpperCase()">
                                                    <small class="text-muted">Max 25 characters. Appears on all certificates.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="nickname">Nickname / Preferred Name</label>
                                                    <input type="text" class="form-control text-uppercase" id="nickname" name="nickname"
                                                        value="<?= htmlspecialchars($participant['nickname'] ?? '') ?>" maxlength="10"
                                                        oninput="this.value = this.value.toUpperCase()">
                                                    <small class="text-muted">Max 10 characters. Appears on ID card and name tag.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="birthdate">Birth Date</label>
                                                    <input type="date" class="form-control" id="birthdate" name="birthdate"
                                                        value="<?= !empty($participant['birthdate']) ? date('Y-m-d', strtotime($participant['birthdate'])) : '' ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="gender">Gender</label>
                                                    <select class="form-select" id="gender" name="gender">
                                                        <option value="">Select gender</option>
                                                        <option value="male" <?= ($participant['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                                        <option value="female" <?= ($participant['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                                        <option value="other" <?= ($participant['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                                                        <option value="prefer-not" <?= ($participant['gender'] ?? '') == 'prefer-not' ? 'selected' : '' ?>>Prefer not to say</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="nationality">Nationality</label>
                                                    <input type="text" class="form-control" id="nationality" name="nationality"
                                                        value="<?= htmlspecialchars($participant['nationality'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="tshirt_size">T-Shirt Size</label>
                                                    <select class="form-select" id="tshirt_size" name="tshirt_size">
                                                        <option value="">Select size</option>
                                                        <?php foreach (['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'] as $size): ?>
                                                            <option value="<?= $size ?>" <?= ($participant['tshirt_size'] ?? '') == $size ? 'selected' : '' ?>><?= strtoupper($size) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="origin_address">Origin Address</label>
                                                    <textarea class="form-control" id="origin_address" name="origin_address" rows="3"><?= htmlspecialchars($participant['origin_address'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="current_address">Current Address</label>
                                                    <textarea class="form-control" id="current_address" name="current_address" rows="3"><?= htmlspecialchars($participant['current_address'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="phone_number">Phone Number</label>
                                                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                                                        value="<?= htmlspecialchars($participant['phone_number'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="disease_history">Disease History</label>
                                                    <textarea class="form-control" id="disease_history" name="disease_history" rows="3"><?= htmlspecialchars($participant['disease_history'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Education & Professional -->
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="ri-graduation-cap-line me-2"></i>Education & Professional</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="education_level">Education Level</label>
                                                    <select class="form-select" id="education_level" name="education_level">
                                                        <option value="">Select level</option>
                                                        <?php
                                                        $levels = ['senior_high_school' => 'Senior High School', 'diploma' => 'Diploma', 'bachelor' => 'Bachelor', 'master' => 'Master', 'doctorate' => 'Doctorate', 'other' => 'Other'];
                                                        foreach ($levels as $val => $label):
                                                        ?>
                                                            <option value="<?= $val ?>" <?= ($participant['education_level'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="major">Major / Field of Study</label>
                                                    <input type="text" class="form-control" id="major" name="major"
                                                        value="<?= htmlspecialchars($participant['major'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="institution">Institution</label>
                                                    <input type="text" class="form-control" id="institution" name="institution"
                                                        value="<?= htmlspecialchars($participant['institution'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="occupation">Occupation</label>
                                                    <input type="text" class="form-control" id="occupation" name="occupation"
                                                        value="<?= htmlspecialchars($participant['occupation'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="organizations">Organizations</label>
                                                    <textarea class="form-control" id="organizations" name="organizations" rows="3"><?= htmlspecialchars($participant['organizations'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="experiences">Experiences</label>
                                                    <textarea class="form-control" id="experiences" name="experiences" rows="3"><?= htmlspecialchars($participant['experiences'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="achievements">Achievements</label>
                                                    <textarea class="form-control" id="achievements" name="achievements" rows="3"><?= htmlspecialchars($participant['achievements'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="ri-phone-line me-2"></i>Emergency Contact</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="emergency_account">Emergency Contact Phone</label>
                                                    <input type="text" class="form-control" id="emergency_account" name="emergency_account"
                                                        value="<?= htmlspecialchars($participant['emergency_account'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="contact_relation">Relationship</label>
                                                    <select class="form-select" id="contact_relation" name="contact_relation">
                                                        <option value="">Select relationship</option>
                                                        <?php
                                                        $relations = ['parent' => 'Parent', 'spouse' => 'Spouse', 'sibling' => 'Sibling', 'relative' => 'Other Relative', 'friend' => 'Friend', 'guardian' => 'Legal Guardian', 'other' => 'Other'];
                                                        foreach ($relations as $val => $label):
                                                        ?>
                                                            <option value="<?= $val ?>" <?= ($participant['contact_relation'] ?? '') == $val ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media & Misc -->
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><i class="ri-share-line me-2"></i>Social Media & Misc</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="instagram_account">Instagram</label>
                                                    <input type="text" class="form-control" id="instagram_account" name="instagram_account"
                                                        value="<?= htmlspecialchars($participant['instagram_account'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="twibbon_link">Twibbon Link</label>
                                                    <input type="url" class="form-control" id="twibbon_link" name="twibbon_link"
                                                        value="<?= htmlspecialchars($participant['twibbon_link'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="knowledge_source">Knowledge Source</label>
                                                    <input type="text" class="form-control" id="knowledge_source" name="knowledge_source"
                                                        value="<?= htmlspecialchars($participant['knowledge_source'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="source_account_name">Source Account Name</label>
                                                    <input type="text" class="form-control" id="source_account_name" name="source_account_name"
                                                        value="<?= htmlspecialchars($participant['source_account_name'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-lg-12 mb-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="ri-save-line align-bottom me-1"></i> Save Changes
                                    </button>
                                    <a href="<?= site_url('users/participants/view/' . $participant['id']) ?>" class="btn btn-light">
                                        <i class="ri-arrow-left-line align-bottom me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

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
    <script src="/assets/js/app.js"></script>
</body>

</html>
