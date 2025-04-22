<!-- Add Essay Modal -->
<div class="modal fade" id="addEssayModal" tabindex="-1" aria-labelledby="addEssayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEssayModalLabel">Add Essay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEssayForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="essayQuestion" class="form-label">Essay Question</label>
                        <textarea class="form-control" id="essayQuestion" name="questions" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="essayWordLimit" class="form-label">Word Limit</label>
                        <input type="number" class="form-control" id="essayWordLimit" name="max_word_count" min="1">
                        <small class="text-muted">Leave blank if there's no word limit</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="essayIsActive" name="is_active" checked>
                        <label class="form-check-label" for="essayIsActive">Active</label>
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

<!-- Edit Essay Modal -->
<div class="modal fade" id="editEssayModal" tabindex="-1" aria-labelledby="editEssayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEssayModalLabel">Edit Essay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEssayForm">
                <input type="hidden" id="editEssayId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editEssayQuestion" class="form-label">Essay Question</label>
                        <textarea class="form-control" id="editEssayQuestion" name="questions" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editEssayWordLimit" class="form-label">Word Limit</label>
                        <input type="number" class="form-control" id="editEssayWordLimit" name="max_word_count" min="1">
                        <small class="text-muted">Leave blank if there's no word limit</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editEssayIsActive" name="is_active">
                        <label class="form-check-label" for="editEssayIsActive">Active</label>
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
