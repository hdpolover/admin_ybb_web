<!-- Modal for updating specific program information -->
<div class="modal fade" id="updateProgramModal" tabindex="-1" aria-labelledby="updateProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProgramModalLabel">Update Program Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateProgramForm" method="post" enctype="multipart/form-data" action="<?= base_url('master-data/program-details/program/' . $currentProgram->id . '/update') ?>">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <input type="hidden" name="program_id" id="edit_program_id" value="<?= $currentProgram->id ?? '' ?>">

                    <!-- Basic Information Section -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_program_name" class="form-label">Program Name</label>
                                    <input type="text" class="form-control" id="edit_program_name" name="name" value="<?= $currentProgram->name ?? '' ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_theme" class="form-label">Theme</label>
                                    <input type="text" class="form-control" id="edit_theme" name="theme" value="<?= $currentProgram->theme ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="edit_program_description" class="form-label">Description</label>
                                    <input type="hidden" name="description" id="description_hidden">
                                    <div id="edit_program_description" style="height: 200px; margin-bottom: 30px;"><?= $currentProgram->description ?? '' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Section -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Media</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_banner" class="form-label">Banner Image</label>
                                    <input type="file" class="form-control" id="edit_banner" name="banner">
                                    <?php if (!empty($currentProgram->banner_url)) : ?>
                                        <div class="mt-2">
                                            <img src="<?= $currentProgram->banner_url ?>" alt="Current Banner" class="img-thumbnail" style="height: 50px;">
                                            <input type="hidden" name="current_banner" value="<?= $currentProgram->banner_url ?>">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Program Details -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Program Details</h5>
                        <div class="row">

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="edit_start_date" name="start_date" value="<?= isset($currentProgram->start_date) ? date('Y-m-d', strtotime($currentProgram->start_date)) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="edit_end_date" name="end_date" value="<?= isset($currentProgram->end_date) ? date('Y-m-d', strtotime($currentProgram->end_date)) : '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_guideline" class="form-label">Program Guideline URL</label>
                                    <input type="text" class="form-control" id="edit_guideline" name="guideline" value="<?= $currentProgram->guideline ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Essay Section -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Essay Information</h5>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_main_essay_question" class="form-label">Main Essay Question</label>
                                    <textarea class="form-control" id="edit_main_essay_question" name="main_essay_question" rows="3"><?= $currentProgram->main_essay_question ?? '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_essay_guideline_url" class="form-label">Essay Guideline URL</label>
                                    <input type="text" class="form-control" id="edit_essay_guideline_url" name="essay_guideline_url" value="<?= $currentProgram->essay_guideline_url ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Resources -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Media Resources</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_twibbon" class="form-label">Twibbon URL</label>
                                    <input type="text" class="form-control" id="edit_twibbon" name="twibbon" value="<?= $currentProgram->twibbon ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_twibbon_video_url" class="form-label">Twibbon Video URL</label>
                                    <input type="text" class="form-control" id="edit_twibbon_video_url" name="twibbon_video_url" value="<?= $currentProgram->twibbon_video_url ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_registration_video_url" class="form-label">Registration Video URL</label>
                                    <input type="text" class="form-control" id="edit_registration_video_url" name="registration_video_url" value="<?= $currentProgram->registration_video_url ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_tshirt_chart_url" class="form-label">T-Shirt Size Chart URL</label>
                                    <input type="text" class="form-control" id="edit_tshirt_chart_url" name="tshirt_chart_url" value="<?= $currentProgram->tshirt_chart_url ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messaging Section -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="mb-3">Messaging & Descriptions</h5>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_share_desc" class="form-label">Share Description</label>
                                    <input type="hidden" name="share_desc" id="share_desc_hidden">
                                    <div id="edit_share_desc" style="height: 200px; margin-bottom: 30px;"><?= $currentProgram->share_desc ?? '' ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_confirmation_desc" class="form-label">Confirmation Description</label>
                                    <input type="hidden" name="confirmation_desc" id="confirmation_desc_hidden">
                                    <div id="edit_confirmation_desc" style="height: 200px; margin-bottom: 30px;"><?= $currentProgram->confirmation_desc ?? '' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Settings -->
                    <div>
                        <h5 class="mb-3">Status Settings</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1" <?= isset($currentProgram->is_active) && $currentProgram->is_active == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="edit_is_active">Program Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_is_registration_open" name="is_registration_open" value="1" <?= isset($currentProgram->is_registration_open) && $currentProgram->is_registration_open == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="edit_is_registration_open">Registration Open</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview functionality for banner
        const bannerInput = document.getElementById('edit_banner');
        bannerInput.addEventListener('change', function() {
            previewImage(this, 'banner_preview');
        });

        // Function to handle image preview
        function previewImage(input, previewId) {
            // Check if a preview container already exists, if not create one
            let previewContainer = document.getElementById(previewId);
            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.id = previewId;
                previewContainer.className = 'mt-2';
                input.parentNode.appendChild(previewContainer);
            }

            // Clear previous preview
            previewContainer.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Image Preview';
                    img.className = 'img-thumbnail';
                    img.style.height = '50px';
                    previewContainer.appendChild(img);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }        // Handle form submission with AJAX
        const programForm = document.getElementById('updateProgramForm');
        if (programForm) {
            programForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Transfer Quill editor content to hidden fields
                // Get content from the editors in a safer way
                const descEditor = document.querySelector('#edit_program_description .ql-editor');
                const shareDescEditor = document.querySelector('#edit_share_desc .ql-editor');
                const confirmationDescEditor = document.querySelector('#edit_confirmation_desc .ql-editor');
                
                if (descEditor) document.getElementById('description_hidden').value = descEditor.innerHTML;
                if (shareDescEditor) document.getElementById('share_desc_hidden').value = shareDescEditor.innerHTML;
                if (confirmationDescEditor) document.getElementById('confirmation_desc_hidden').value = confirmationDescEditor.innerHTML;

                // Show loading state
                Swal.fire({
                    title: 'Updating program details...',
                    html: 'Please wait while we process your request.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Close modal if open
                const modal = bootstrap.Modal.getInstance(document.getElementById('updateProgramModal'));
                if (modal) {
                    modal.hide();
                }

                // Handle checkbox values for is_active and is_registration_open
                const isActiveCheckbox = document.getElementById('edit_is_active');
                const isRegistrationOpenCheckbox = document.getElementById('edit_is_registration_open');

                // Add hidden fields with values 0 if checkboxes are not checked
                if (!isActiveCheckbox.checked) {
                    const hiddenIsActive = document.createElement('input');
                    hiddenIsActive.type = 'hidden';
                    hiddenIsActive.name = 'is_active';
                    hiddenIsActive.value = '0';
                    this.appendChild(hiddenIsActive);
                }

                if (!isRegistrationOpenCheckbox.checked) {
                    const hiddenIsRegistrationOpen = document.createElement('input');
                    hiddenIsRegistrationOpen.type = 'hidden';
                    hiddenIsRegistrationOpen.name = 'is_registration_open';
                    hiddenIsRegistrationOpen.value = '0';
                    this.appendChild(hiddenIsRegistrationOpen);
                }                // Submit form via AJAX
                const formData = new FormData(this);
                
                console.log('Submitting form to:', this.action);                // Log form data for debugging
                console.log('Form data entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (pair[1] instanceof File ? 'File: ' + pair[1].name : pair[1]));
                }
                
                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                            // IMPORTANT: Do NOT set Content-Type header when sending FormData with files
                            // The browser will set it automatically with the correct multipart boundary
                        },
                        cache: 'no-cache',
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message || 'Program details updated successfully',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                // Reload the page to show updated data
                                window.location.reload();
                            });
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to update program details',
                                confirmButtonColor: '#3085d6',
                                html: data.errors ? Object.values(data.errors).join('<br>') : (data.message || 'An error occurred')
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            confirmButtonColor: '#3085d6'
                        });
                    });
            });
        }
    });
</script>