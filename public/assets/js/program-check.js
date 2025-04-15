/**
 * Program check handler for sidebar menu
 * Shows Sweet Alert if no program is selected and user tries to access protected pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Query for all sidebar menu links - both direct links and dropdown toggles
    const menuLinks = document.querySelectorAll('.app-menu .nav-link, .app-menu .menu-link[data-bs-toggle="collapse"]');
    
    // Paths that should always be accessible
    const allowedPaths = [
        '/welcome',
        '/auth',
        '/pages-profile',
        '/pages-logout'
    ];
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        // Skip if this is an allowed path or if it's null/undefined
        if (!href || allowedPaths.some(path => href.includes(path))) {
            return;
        }
          // Add click event listener
        link.addEventListener('click', function(e) {
            // Check if the current_program session variable exists
            // We'll use a cookie to track this since we can't directly access the PHP session
            const hasProgramSelected = getCookie('has_program_selected') === 'true';
            
            console.log('Program selection check - Cookie value:', getCookie('has_program_selected'));
            
            if (!hasProgramSelected) {
                e.preventDefault();
                e.stopPropagation();
                
                // Show Sweet Alert
                Swal.fire({
                    title: 'Program Not Selected',
                    text: 'Please select a program to continue.',
                    icon: 'warning',
                    confirmButtonText: 'Go to Program Selection',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to welcome page to select a program
                        window.location.href = '/welcome';
                    }
                });
                
                return false;
            }
        });
    });
    
    // Helper function to get cookie by name
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
});
