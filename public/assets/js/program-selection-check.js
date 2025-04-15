/**
 * Program Selection Check
 * 
 * This script checks if a program has been selected before allowing navigation
 * to sidebar menu items. If no program is selected, a Sweet Alert is shown.
 */

document.addEventListener("DOMContentLoaded", function() {
    // Get the current program session value from a data attribute set in the template
    const hasProgramSelected = document.body.getAttribute('data-has-program') === 'true';
    
    // Select all sidebar navigation links except the dashboard and welcome links
    const sidebarLinks = document.querySelectorAll('.app-menu .nav-link');
    
    // Add click event listeners to each sidebar link
    sidebarLinks.forEach(function(link) {
        // Ignore links that should work without program selection (welcome link, dashboard, etc.)
        const href = link.getAttribute('href');
        if (href && !href.includes('welcome') && !href.includes('dashboard')) {
            link.addEventListener('click', function(e) {
                // If no program is selected, show Sweet Alert
                if (!hasProgramSelected) {
                    e.preventDefault();
                    
                    // Show Sweet Alert
                    Swal.fire({
                        title: 'Program Selection Required',
                        text: 'Please select a program first to view this section.',
                        icon: 'warning',
                        confirmButtonText: 'Select Program',
                        confirmButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to program selection page
                            window.location.href = '/welcome';
                        }
                    });
                    
                    return false;
                }
            });
        }
    });
});
