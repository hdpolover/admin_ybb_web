/**
 * Mobile sidebar fix for the YBB Admin Panel
 * This script ensures the hamburger menu can be clicked on mobile devices
 * and properly toggles the sidebar
 */
document.addEventListener("DOMContentLoaded", function() {
    // Define the toggleMobileMenu function outside to avoid reference errors
    function toggleMobileMenu(event) {
        // Prevent any default action
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Toggle the vertical-sidebar-enable class on the body
        document.body.classList.toggle("vertical-sidebar-enable");
        
        // Toggle the open class on the hamburger icon
        document.querySelector(".hamburger-icon").classList.toggle("open");
        
        // Make sure the sidebar is properly shown on mobile
        if (window.innerWidth <= 767) {
            document.documentElement.setAttribute("data-sidebar-size", "lg");
        }
        
        // Make sure the vertical overlay is also toggled
        const overlay = document.querySelector(".vertical-overlay");
        if (overlay) {
            if (document.body.classList.contains("vertical-sidebar-enable")) {
                overlay.style.display = "block";
                overlay.style.opacity = "1";
                overlay.style.visibility = "visible";
            } else {
                setTimeout(function() {
                    overlay.style.display = "none";
                }, 300);
                overlay.style.opacity = "0";
                overlay.style.visibility = "hidden";
            }
        }
    }
    
    // Get the hamburger icon element
    const hamburgerIcon = document.getElementById("topnav-hamburger-icon");
    
    // Check if hamburger exists
    if (hamburgerIcon) {
        // First, remove the event listener from app.js
        hamburgerIcon.removeEventListener("click", window.toggleHamburgerMenu);
        
        // Clear all existing click events (a more aggressive approach)
        hamburgerIcon.replaceWith(hamburgerIcon.cloneNode(true));
        
        // Get the fresh reference after cloning
        const freshHamburger = document.getElementById("topnav-hamburger-icon");
        
        // Add our own click listener
        freshHamburger.addEventListener("click", toggleMobileMenu);

        // Add a backup click event for the menu button in the sidebar
        const verticalHoverBtn = document.getElementById("vertical-hover");
        if (verticalHoverBtn) {
            verticalHoverBtn.addEventListener("click", function() {
                if (window.innerWidth <= 767) {
                    document.body.classList.toggle("vertical-sidebar-enable");
                }
            });
        }
    }
      // Add click event on the overlay to close the menu
    const verticalOverlay = document.querySelector(".vertical-overlay");
    if (verticalOverlay) {
        verticalOverlay.removeEventListener("click", closeMenu);
        verticalOverlay.addEventListener("click", closeMenu);
        
        function closeMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            document.body.classList.remove("vertical-sidebar-enable");
            document.querySelector(".hamburger-icon").classList.remove("open");
            
            // Hide overlay with animation
            verticalOverlay.style.opacity = "0";
            verticalOverlay.style.visibility = "hidden";
            setTimeout(function() {
                verticalOverlay.style.display = "none";
            }, 300);
        }
    }
      // Add event listener to all menu items to close menu when clicked on mobile
    const menuItems = document.querySelectorAll(".app-menu a.nav-link");
    if (menuItems.length > 0) {
        menuItems.forEach(item => {
            item.addEventListener("click", function() {
                if (window.innerWidth <= 767) {
                    document.body.classList.remove("vertical-sidebar-enable");
                    document.querySelector(".hamburger-icon").classList.remove("open");
                    
                    // Hide overlay
                    if (verticalOverlay) {
                        verticalOverlay.style.opacity = "0";
                        verticalOverlay.style.visibility = "hidden";
                        setTimeout(function() {
                            verticalOverlay.style.display = "none";
                        }, 300);
                    }
                }
            });
        });
    }
    
    // Override the app.js toggleHamburgerMenu for mobile view
    // This ensures our implementation is used instead of the default one
    window.addEventListener('load', function() {
        // Save a reference to the original function if needed
        if (typeof window.originalToggleHamburgerMenu === 'undefined' && typeof window.toggleHamburgerMenu === 'function') {
            window.originalToggleHamburgerMenu = window.toggleHamburgerMenu;
        }
        
        // Replace the original function with a wrapper that calls our function for mobile
        window.toggleHamburgerMenu = function(e) {
            if (window.innerWidth <= 767) {
                toggleMobileMenu(e);
            } else if (typeof window.originalToggleHamburgerMenu === 'function') {
                window.originalToggleHamburgerMenu(e);
            }
        };
        
        // Make toggleMobileMenu available globally
        window.toggleMobileMenu = toggleMobileMenu;
        
        // Force check if sidebar should be visible on page load for mobile
        if (window.innerWidth <= 767) {
            // Ensure the menu is properly initialized
            const appMenu = document.querySelector('.app-menu');
            if (appMenu) {
                appMenu.style.visibility = 'hidden';
                appMenu.style.transform = 'translateX(-100%)';
            }
        }
    });
});
