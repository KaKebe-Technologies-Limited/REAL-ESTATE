// Mobile Sidebar Enhancement Script
document.addEventListener('DOMContentLoaded', function() {
    console.log('Mobile sidebar enhancement script loaded');

    // Add close button to sidebar
    const sidebar = document.getElementById('sidebar');
    console.log('Sidebar element found:', !!sidebar);

    // Check if we're on mobile
    const isMobile = window.innerWidth <= 991;
    console.log('Is mobile view:', isMobile);

    // Ensure sidebar is in the correct initial state on mobile
    if (sidebar && isMobile) {
        // Make sure the sidebar starts in the correct state (hidden)
        sidebar.classList.remove('collapsed');

        // Hide the overlay
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('active');
        }
    }
    if (sidebar && isMobile) {
        // Create close button if it doesn't exist and we're on mobile
        if (!document.querySelector('.sidebar-close')) {
            const closeButton = document.createElement('button');
            closeButton.className = 'sidebar-close';
            closeButton.innerHTML = '<i class="fas fa-times"></i>';
            closeButton.setAttribute('aria-label', 'Close sidebar');
            sidebar.prepend(closeButton);

            // Add event listener to close button
            closeButton.addEventListener('click', function() {
                toggleSidebar();
            });
        }
    }

    // Enhance sidebar overlay
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            toggleSidebar();
        });
    }

    // Enhance sidebar toggle button
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            toggleSidebar();
        });
    }

    // Function to toggle sidebar
    function toggleSidebar() {
        if (sidebar) {
            console.log('Toggling sidebar, current state:', sidebar.classList.contains('collapsed'));

            // Toggle collapsed class
            sidebar.classList.toggle('collapsed');

            // Toggle overlay
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('active');
            }

            // Toggle body scroll
            if (sidebar.classList.contains('collapsed')) {
                document.body.style.overflow = 'hidden';
                console.log('Sidebar is now open');
            } else {
                document.body.style.overflow = '';
                console.log('Sidebar is now closed');
            }

            // Force repaint to ensure CSS changes are applied
            sidebar.style.display = 'none';
            sidebar.offsetHeight; // Force reflow
            sidebar.style.display = '';
        } else {
            console.error('Sidebar element not found');
        }
    }

    // Close sidebar when clicking on a menu item on mobile
    const menuItems = document.querySelectorAll('.sidebar-nav .nav-link:not(.has-submenu), .submenu-link:not([onclick*="togglePropertySubmenu"])');
    if (menuItems) {
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 991) {
                    // Check if this is a final action item, not a submenu toggle
                    const isSubmenuToggle =
                        this.parentElement.classList.contains('has-submenu') ||
                        this.querySelector('.property-submenu-arrow') ||
                        this.closest('.has-submenu');

                    // Check if this is a property submenu toggle
                    const isPropertySubmenuToggle =
                        this.querySelector('.property-submenu-arrow') ||
                        this.getAttribute('onclick')?.includes('togglePropertySubmenu');

                    // Only close sidebar if it's a final action item
                    if (!isSubmenuToggle && !isPropertySubmenuToggle) {
                        toggleSidebar();
                    } else {
                        // Make sure sidebar stays open for submenu toggles
                        if (!sidebar.classList.contains('collapsed')) {
                            sidebar.classList.add('collapsed');
                        }
                        if (sidebarOverlay && !sidebarOverlay.classList.contains('active')) {
                            sidebarOverlay.classList.add('active');
                        }
                        document.body.style.overflow = 'hidden';
                        e.preventDefault();
                    }
                }
            });
        });
    }

    // Add active class to current menu item based on URL
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuItems = document.querySelectorAll('.sidebar-nav .nav-link, .submenu-link');

        menuItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href) && href !== '#') {
                // Remove active class from all items
                menuItems.forEach(i => i.parentElement.classList.remove('active'));

                // Add active class to current item
                item.parentElement.classList.add('active');

                // If it's a submenu item, open the parent menu
                const parentSubmenu = item.closest('.submenu');
                if (parentSubmenu) {
                    parentSubmenu.parentElement.classList.add('open');
                }
            }
        });
    }

    // Call setActiveMenuItem on page load
    setActiveMenuItem();

    // Enhance submenu toggles for better touch feedback
    const submenuToggles = document.querySelectorAll('.has-submenu > .nav-link');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });

        toggle.addEventListener('touchend', function() {
            this.classList.remove('touch-active');
        });
    });

    // Add ripple effect for touch feedback
    function createRipple(event) {
        const button = event.currentTarget;

        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
        circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
        circle.classList.add('ripple');

        const ripple = button.querySelector('.ripple');
        if (ripple) {
            ripple.remove();
        }

        button.appendChild(circle);
    }

    const buttons = document.querySelectorAll('.sidebar-nav .nav-link, .submenu-link');
    buttons.forEach(button => {
        button.addEventListener('click', createRipple);
    });

    // Handle window resize events to adjust sidebar behavior
    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth <= 991;
        console.log('Window resized, is mobile:', isMobile);

        if (sidebar) {
            if (isMobile) {
                // If transitioning to mobile, make sure sidebar is hidden
                if (!sidebar.classList.contains('collapsed')) {
                    // Hide the overlay
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                    document.body.style.overflow = '';
                }

                // Add close button if it doesn't exist
                if (!document.querySelector('.sidebar-close')) {
                    const closeButton = document.createElement('button');
                    closeButton.className = 'sidebar-close';
                    closeButton.innerHTML = '<i class="fas fa-times"></i>';
                    closeButton.setAttribute('aria-label', 'Close sidebar');
                    sidebar.prepend(closeButton);

                    // Add event listener to close button
                    closeButton.addEventListener('click', function() {
                        toggleSidebar();
                    });
                }
            } else {
                // If transitioning to desktop, make sure body scroll is enabled
                document.body.style.overflow = '';

                // Hide the overlay
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }

                // Remove close button if it exists
                const closeButton = document.querySelector('.sidebar-close');
                if (closeButton) {
                    closeButton.remove();
                }
            }
        }
    });
});
