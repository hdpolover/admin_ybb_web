  <!-- Modal for updating program category information -->
  <div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="updateCategoryModalLabel">Update Program Category Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="updateCategoryForm" method="post" enctype="multipart/form-data" action="<?= base_url('master-data/program-details/category/' . $currentProgramCategory->id . '/update') ?>">
                  <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                      <input type="hidden" name="program_category_id" id="edit_program_category_id" value="<?= $currentProgramCategory->id ?? '' ?>">

                      <!-- Basic Information Section -->
                      <div class="border-bottom pb-3 mb-4">
                          <h5 class="mb-3">Basic Information</h5>
                          <div class="row">
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_name" class="form-label">Program Category Name</label>
                                      <input type="text" class="form-control" id="edit_name" name="name" value="<?= $currentProgramCategory->name ?? '' ?>" required>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <label for="edit_program_type_id" class="form-label">Program Type</label>
                                  <select class="form-select" id="edit_program_type_id" name="program_type_id">
                                      <option value="">Select Program Type</option>
                                      <?php if (isset($programTypes) && is_array($programTypes)): ?>
                                          <?php foreach ($programTypes as $type): ?>
                                              <option value="<?= $type->id ?>" <?= ($currentProgramCategory->program_type_id ?? '') == $type->id ? 'selected' : '' ?>>
                                                  <?= $type->name ?>
                                              </option>
                                          <?php endforeach; ?>
                                      <?php endif; ?>
                                  </select>
                              </div>
                              <div class="col-md-12">
                                  <div class="mb-3">
                                      <label for="edit_tagline" class="form-label">Tagline</label>
                                      <input type="text" class="form-control" id="edit_tagline" name="tagline" value="<?= $currentProgramCategory->tagline ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-12">
                                  <div class="mb-3">
                                      <label for="edit_description" class="form-label">Description</label>
                                      <textarea class="form-control" id="edit_description" name="description" rows="3"><?= $currentProgramCategory->description ?? '' ?></textarea>
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
                                      <label for="edit_logo" class="form-label">Logo Image</label>
                                      <input type="file" class="form-control" id="edit_logo" name="logo">
                                      <?php if (!empty($currentProgramCategory->logo_url)) : ?>
                                          <div class="mt-2">
                                              <img src="<?= $currentProgramCategory->logo_url ?>" alt="Current Logo" class="img-thumbnail" style="height: 50px;">
                                              <input type="hidden" name="current_logo" value="<?= $currentProgramCategory->logo_url ?>">
                                          </div>
                                      <?php endif; ?>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_main_banner" class="form-label">Main Banner Image</label>
                                      <input type="file" class="form-control" id="edit_main_banner" name="main_banner">
                                      <?php if (!empty($currentProgramCategory->main_banner_url)) : ?>
                                          <div class="mt-2">
                                              <img src="<?= $currentProgramCategory->main_banner_url ?>" alt="Current Banner" class="img-thumbnail" style="height: 50px;">
                                              <input type="hidden" name="current_main_banner" value="<?= $currentProgramCategory->main_banner_url ?>">
                                          </div>
                                      <?php endif; ?>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <!-- Rich Content Section -->
                      <div class="border-bottom pb-3 mb-4">
                          <h5 class="mb-3">Website Content</h5>

                          <!-- About -->
                          <div class="row mb-4">
                              <div class="col-12">
                                  <label for="edit_about" class="form-label">About</label>
                                  <input type="hidden" name="about" id="about_hidden">
                                  <div id="edit_about" style="height: 200px; margin-bottom: 30px;"><?= $currentProgramCategory->about ?? '' ?></div>
                              </div>
                          </div>

                          <!-- Core Values -->
                          <div class="row mb-4">
                              <div class="col-12">
                                  <label for="edit_core_values" class="form-label">Core Values</label>
                                  <input type="hidden" name="core_values" id="core_values_hidden">
                                  <div id="edit_core_values" style="height: 200px; margin-bottom: 30px;"><?= $currentProgramCategory->core_values ?? '' ?></div>
                              </div>
                          </div>

                          <!-- Objectives -->
                          <div class="row mb-4">
                              <div class="col-12">
                                  <label for="edit_objectives" class="form-label">Objectives</label>
                                  <input type="hidden" name="objectives" id="objectives_hidden">
                                  <div id="edit_objectives" style="height: 200px; margin-bottom: 30px;"><?= $currentProgramCategory->objectives ?? '' ?></div>
                              </div>
                          </div>

                          <!-- Benefits -->
                          <div class="row mb-4">
                              <div class="col-12">
                                  <label for="edit_benefits" class="form-label">Benefits</label>
                                  <input type="hidden" name="benefits" id="benefits_hidden">
                                  <div id="edit_benefits" style="height: 200px; margin-bottom: 30px;"><?= $currentProgramCategory->benefits ?? '' ?></div>
                              </div>
                          </div>
                      </div>

                      <!-- Contact Information Section -->
                      <div class="border-bottom pb-3 mb-4">
                          <h5 class="mb-3">Contact Information</h5>
                          <div class="row">
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_contact" class="form-label">Contact</label>
                                      <input type="text" class="form-control" id="edit_contact" name="contact" value="<?= $currentProgramCategory->contact ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_email" class="form-label">Email</label>
                                      <input type="email" class="form-control" id="edit_email" name="email" value="<?= $currentProgramCategory->email ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_location" class="form-label">Location</label>
                                      <input type="text" class="form-control" id="edit_location" name="location" value="<?= $currentProgramCategory->location ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_web_url" class="form-label">Website URL</label>
                                      <input type="text" class="form-control" id="edit_web_url" name="web_url" value="<?= $currentProgramCategory->web_url ?? '' ?>">
                                  </div>
                              </div>
                          </div>
                      </div>

                      <!-- Social Media Section -->
                      <div>
                          <h5 class="mb-3">Social Media & External Links</h5>
                          <div class="row">
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_instagram" class="form-label">Instagram</label>
                                      <input type="text" class="form-control" id="edit_instagram" name="instagram" value="<?= $currentProgramCategory->instagram ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_tiktok" class="form-label">TikTok</label>
                                      <input type="text" class="form-control" id="edit_tiktok" name="tiktok" value="<?= $currentProgramCategory->tiktok ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_youtube" class="form-label">YouTube</label>
                                      <input type="text" class="form-control" id="edit_youtube" name="youtube" value="<?= $currentProgramCategory->youtube ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_telegram" class="form-label">Telegram</label>
                                      <input type="text" class="form-control" id="edit_telegram" name="telegram" value="<?= $currentProgramCategory->telegram ?? '' ?>">
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="edit_sponsor_url" class="form-label">Sponsor URL</label>
                                      <input type="text" class="form-control" id="edit_sponsor_url" name="sponsor_url" value="<?= $currentProgramCategory->sponsor_url ?? '' ?>">
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Update Information</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          // Image preview functionality for logo
          const logoInput = document.getElementById('edit_logo');
          logoInput.addEventListener('change', function() {
              previewImage(this, 'logo_preview');
          });

          // Image preview functionality for main banner
          const mainBannerInput = document.getElementById('edit_main_banner');
          mainBannerInput.addEventListener('change', function() {
              previewImage(this, 'main_banner_preview');
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
          }

          // Handle form submission with AJAX
          const categoryForm = document.getElementById('updateCategoryForm');
          if (categoryForm) {
              categoryForm.addEventListener('submit', function(e) {
                  e.preventDefault();

                  // Transfer Quill editor content to hidden fields
                  const editors = ['about', 'core_values', 'objectives', 'benefits'];
                  editors.forEach(editorName => {
                      const quillEditor = document.querySelector(`#edit_${editorName} .ql-editor`);
                      if (quillEditor) {
                          document.getElementById(`${editorName}_hidden`).value = quillEditor.innerHTML;
                      }
                  });

                  // Show loading state
                  Swal.fire({
                      title: 'Updating program category details...',
                      html: 'Please wait while we process your request.',
                      allowOutsideClick: false,
                      didOpen: () => {
                          Swal.showLoading();
                      }
                  });

                  // close modal if open
                  const modal = bootstrap.Modal.getInstance(document.getElementById('updateCategoryModal'));
                  if (modal) {
                      modal.hide();
                  }

                  // Accept URLs as-is without modifying them - no protocol validation needed

                  // Submit form via AJAX
                  const formData = new FormData(this);

                  fetch(this.action, {
                          method: 'POST',
                          body: formData,
                          headers: {
                              'X-Requested-With': 'XMLHttpRequest'
                          }
                      })
                      .then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              Swal.fire({
                                  icon: 'success',
                                  title: 'Success!',
                                  text: data.message || 'Program category details updated successfully',
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
                                  text: data.message || 'Failed to update program category details',
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