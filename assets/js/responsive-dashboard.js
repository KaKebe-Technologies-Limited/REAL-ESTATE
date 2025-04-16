// Responsive Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    console.log('Responsive dashboard script loaded');
    console.log('Sidebar toggle element:', sidebarToggle);
    console.log('Sidebar element:', sidebar);

    // Fix for sidebar toggle button
    if (sidebarToggle) {
        // Remove any existing click event listeners
        const newSidebarToggle = sidebarToggle.cloneNode(true);
        if (sidebarToggle.parentNode) {
            sidebarToggle.parentNode.replaceChild(newSidebarToggle, sidebarToggle);
        }

        // Add new event listener
        newSidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Sidebar toggle clicked');

            if (sidebar) {
                // For mobile devices
                if (window.innerWidth < 992) {
                    sidebar.classList.toggle('collapsed');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('active');
                    }

                    if (sidebar.classList.contains('collapsed')) {
                        document.body.style.overflow = 'hidden'; // Prevent scrolling
                    } else {
                        document.body.style.overflow = ''; // Allow scrolling
                    }
                } else {
                    // For desktop
                    sidebar.classList.toggle('desktop-collapsed');
                    if (mainContent) {
                        mainContent.classList.toggle('expanded');
                    }
                }
            }
        });
    }

    // Close sidebar when clicking on overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('collapsed');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = ''; // Allow scrolling
        });
    }

    // Function to handle property submenu toggle
    function handlePropertySubmenuToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Property submenu toggle clicked');

        // Get the submenu element
        const submenu = this.nextElementSibling;
        if (submenu) {
            submenu.classList.toggle('show');

            // Toggle arrow rotation
            const arrow = this.querySelector('.property-submenu-arrow');
            if (arrow) {
                arrow.classList.toggle('rotate');
            }

            // Ensure sidebar stays open on mobile
            if (window.innerWidth < 992) {
                const sidebar = document.getElementById('sidebar');
                const sidebarOverlay = document.getElementById('sidebar-overlay');

                if (sidebar && !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }

                if (sidebarOverlay && !sidebarOverlay.classList.contains('active')) {
                    sidebarOverlay.classList.add('active');
                }

                document.body.style.overflow = 'hidden';
            }
        }
    }

    // Function to handle main submenu toggle
    function handleMainSubmenuToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Main submenu toggle clicked');

        // Toggle the parent li's open class
        const parentLi = this.parentElement;
        if (parentLi) {
            parentLi.classList.toggle('open');

            // Ensure sidebar stays open on mobile
            if (window.innerWidth < 992) {
                const sidebar = document.getElementById('sidebar');
                const sidebarOverlay = document.getElementById('sidebar-overlay');

                if (sidebar && !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }

                if (sidebarOverlay && !sidebarOverlay.classList.contains('active')) {
                    sidebarOverlay.classList.add('active');
                }

                document.body.style.overflow = 'hidden';
            }
        }
    }

    // Handle submenu toggles on mobile
    const propertySubmenuToggles = document.querySelectorAll('.submenu-link[onclick*="togglePropertySubmenu"]');
    if (propertySubmenuToggles.length > 0) {
        console.log('Found property submenu toggles:', propertySubmenuToggles.length);
        propertySubmenuToggles.forEach(toggle => {
            // Clone and replace to remove any existing event listeners
            const newToggle = toggle.cloneNode(true);
            if (toggle.parentNode) {
                toggle.parentNode.replaceChild(newToggle, toggle);
            }

            // Remove the original onclick attribute
            newToggle.removeAttribute('onclick');

            // Add new event listener
            newToggle.addEventListener('click', handlePropertySubmenuToggle);
        });
    }

    // Handle main submenu toggles
    const mainSubmenuToggles = document.querySelectorAll('.nav-link[onclick*="toggleSubmenu"]');
    if (mainSubmenuToggles.length > 0) {
        console.log('Found main submenu toggles:', mainSubmenuToggles.length);
        mainSubmenuToggles.forEach(toggle => {
            // Clone and replace to remove any existing event listeners
            const newToggle = toggle.cloneNode(true);
            if (toggle.parentNode) {
                toggle.parentNode.replaceChild(newToggle, toggle);
            }

            // Remove the original onclick attribute
            newToggle.removeAttribute('onclick');

            // Add new event listener
            newToggle.addEventListener('click', handleMainSubmenuToggle);
        });
    }

    // Fallback for submenu toggles that might not have been found
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a submenu toggle
        if (e.target.closest('.submenu-link')) {
            const submenuLink = e.target.closest('.submenu-link');
            if (submenuLink.querySelector('.property-submenu-arrow')) {
                handlePropertySubmenuToggle.call(submenuLink, e);
            }
        }

        // Check if the clicked element is a main submenu toggle
        if (e.target.closest('.nav-link')) {
            const navLink = e.target.closest('.nav-link');
            if (navLink.querySelector('.submenu-arrow')) {
                handleMainSubmenuToggle.call(navLink, e);
            }
        }
    });

    // Close sidebar when clicking on a direct menu item (not submenu toggles) on mobile
    const directMenuLinks = document.querySelectorAll('.sidebar-nav .nav-link:not([onclick*="toggle"]), .submenu-link:not([onclick*="toggle"])');
    if (directMenuLinks.length > 0 && sidebar && sidebarOverlay) {
        directMenuLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('collapsed');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = ''; // Allow scrolling
                }
            });
        });
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            // Reset styles for desktop
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';

            // Reset sidebar classes for desktop
            if (sidebar) {
                sidebar.classList.remove('collapsed');
            }

            // Adjust main content
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        } else {
            // Hide sidebar on mobile by default
            if (sidebar) {
                sidebar.classList.remove('collapsed');
            }

            // Reset main content
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        }
    });

    // Initialize sidebar and submenu state
    function initializeSidebarState() {
        // Ensure sidebar is visible on desktop
        if (window.innerWidth >= 992 && sidebar) {
            sidebar.style.transform = 'translateX(0)';
        }

        // Check if there's an active menu item
        const activeMenuItem = document.querySelector('.sidebar-nav .nav-item.active');
        if (activeMenuItem) {
            // If the active item is inside a submenu, open that submenu
            const parentSubmenu = activeMenuItem.closest('.submenu');
            if (parentSubmenu) {
                const parentLi = parentSubmenu.closest('.has-submenu');
                if (parentLi) {
                    parentLi.classList.add('open');
                }
            }
        }
    }

    // Call initialization function
    initializeSidebarState();

    // Add a slight delay to ensure sidebar is visible after page load
    setTimeout(function() {
        if (sidebar && window.innerWidth >= 992) {
            sidebar.style.transform = 'translateX(0)';
        }
    }, 100);
});
