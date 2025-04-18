// FAQ AJAX handlers for CRUD operations
$(document).ready(function() {
    // Helper functions for SweetAlert
    function showSuccessAlert(message, callback) {
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0ab39c'
        }).then((result) => {
            if (callback && typeof callback === 'function') {
                callback(result);
            }
        });
    }
    
    function showErrorAlert(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#f06548'
        });
    }

    // Handle Add FAQ form submission via AJAX
    $("#add-faq-form").on('submit', function(e) {
        e.preventDefault();
        
        // Check form validity
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close the modal
                    $('#add-faq-modal').modal('hide');
                    
                    // Show success message
                    showSuccessAlert(response.message || 'FAQ created successfully', function() {
                        // Reload the page to refresh the table
                        window.location.reload();
                    });
                    
                    // Reset form
                    $('#add-faq-form')[0].reset();
                    $('#add-faq-form').removeClass('was-validated');
                } else {
                    showErrorAlert(response.message || 'Failed to create FAQ');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr, status, error);
                showErrorAlert('Failed to create FAQ. Please try again.');
            }
        });
    });

    // Handle Edit FAQ form submission via AJAX
    $("#edit-faq-form").on('submit', function(e) {
        e.preventDefault();
        
        // Check form validity
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close the modal
                    $('#edit-faq-modal').modal('hide');
                    
                    // Show success message
                    showSuccessAlert(response.message || 'FAQ updated successfully', function() {
                        // Reload the page to refresh the table
                        window.location.reload();
                    });
                } else {
                    showErrorAlert(response.message || 'Failed to update FAQ');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr, status, error);
                showErrorAlert('Failed to update FAQ. Please try again.');
            }
        });
    });

    // Handle Delete FAQ form submission via AJAX
    $("#delete-faq-form").on('submit', function(e) {
        e.preventDefault();
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close the modal
                    $('#delete-faq-modal').modal('hide');
                    
                    // Show success message
                    showSuccessAlert(response.message || 'FAQ deleted successfully', function() {
                        // Reload the page to refresh the table
                        window.location.reload();
                    });
                } else {
                    showErrorAlert(response.message || 'Failed to delete FAQ');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr, status, error);
                showErrorAlert('Failed to delete FAQ. Please try again.');
            }
        });
    });
});
