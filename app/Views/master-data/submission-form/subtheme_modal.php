<!-- Add SubTheme Modal -->
<div class="modal fade" id="addSubthemeModal" tabindex="-1" aria-labelledby="addSubthemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubthemeModalLabel">Add Sub Theme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSubthemeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subthemeName" class="form-label">Sub Theme Name</label>
                        <input type="text" class="form-control" id="subthemeName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="subthemeDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="subthemeDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="subthemeIsActive" name="is_active" checked>
                        <label class="form-check-label" for="subthemeIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit SubTheme Modal -->
<div class="modal fade" id="editSubthemeModal" tabindex="-1" aria-labelledby="editSubthemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubthemeModalLabel">Edit Sub Theme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubthemeForm">
                <input type="hidden" id="editSubthemeId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editSubthemeName" class="form-label">Sub Theme Name</label>
                        <input type="text" class="form-control" id="editSubthemeName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSubthemeDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editSubthemeDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editSubthemeIsActive" name="is_active">
                        <label class="form-check-label" for="editSubthemeIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
