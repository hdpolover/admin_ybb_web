<!-- JAVASCRIPT -->
<script src="/assets/libs/jquery/jquery.min.js"></script>
<script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/assets/libs/node-waves/waves.min.js"></script>
<script src="/assets/libs/feather-icons/feather.min.js"></script>
<script src="/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="/assets/js/plugins.js"></script>

<!-- Sweet Alert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Program Selection Check -->
<script src="/assets/js/program-check.js"></script>

<!-- App JS (Required for theme functionality) -->
<script src="/assets/js/app.js"></script>

<!-- Mobile Sidebar Fix - Load after app.js -->
<script src="/assets/js/mobile-sidebar-fix.js"></script>

<script>
    // Additional initialization to ensure mobile menu works
    document.addEventListener("DOMContentLoaded", function() {
        if (window.innerWidth <= 767) {
            // Initialize mobile menu
            const hamburgerIcon = document.getElementById("topnav-hamburger-icon");
            if (hamburgerIcon && typeof window.toggleMobileMenu === 'function') {
                // Force reattach event listener after app.js has loaded
                setTimeout(function() {
                    hamburgerIcon.addEventListener("click", window.toggleMobileMenu);
                }, 500);
            }
        }
    });
</script>