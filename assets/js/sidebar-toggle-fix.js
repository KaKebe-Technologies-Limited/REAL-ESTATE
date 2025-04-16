// Sidebar Toggle Fix
// This script ensures the sidebar toggle button works correctly on all dashboard pages

document.addEventListener('DOMContentLoaded', function() {
    console.log('Sidebar toggle fix script loaded');

    // Get sidebar elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (!sidebar || !sidebarToggle) {
        console.error('Sidebar elements not found');
        return;
    }

    console.log('Sidebar elements found, adding event listeners');

    // Remove any existing click event listeners from the toggle button
    const newSidebarToggle = sidebarToggle.cloneNode(true);
    sidebarToggle.parentNode.replaceChild(newSidebarToggle, sidebarToggle);

    // Add click event listener to the toggle button
    newSidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Sidebar toggle button clicked');

        // Toggle the collapsed class on the sidebar
        sidebar.classList.toggle('collapsed');

        // Toggle the overlay
        if (sidebarOverlay) {
            sidebarOverlay.classList.toggle('active');
        }

        // Toggle body scroll
        if (sidebar.classList.contains('collapsed')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }

        console.log('Sidebar collapsed state:', sidebar.classList.contains('collapsed'));
    });

    // Add click event listener to the overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('collapsed');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close sidebar when clicking on a menu item on mobile, but not on submenu toggles
    const menuItems = document.querySelectorAll('.sidebar-nav .nav-link:not(.has-submenu), .submenu-link:not([onclick*="togglePropertySubmenu"])');
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (window.innerWidth <= 991) {
                // Check if this is a final action item, not a submenu toggle
                const isSubmenuToggle =
                    this.parentElement.classList.contains('has-submenu') ||
                    this.classList.contains('submenu-link') ||
                    this.querySelector('.property-submenu-arrow') ||
                    this.closest('.has-submenu');

                // Check if this is a property submenu toggle
                const isPropertySubmenuToggle =
                    this.querySelector('.property-submenu-arrow') ||
                    this.getAttribute('onclick')?.includes('togglePropertySubmenu');

                console.log('Menu item clicked:', this.textContent.trim());
                console.log('Is submenu toggle:', !!isSubmenuToggle);
                console.log('Is property submenu toggle:', !!isPropertySubmenuToggle);

                // Only close sidebar if it's a final action item
                if (!isSubmenuToggle && !isPropertySubmenuToggle) {
                    console.log('Closing sidebar because this is a final action item');
                    sidebar.classList.remove('collapsed');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                    document.body.style.overflow = '';
                } else {
                    console.log('Keeping sidebar open because this is a submenu toggle');
                    // Make sure sidebar stays open
                    if (!sidebar.classList.contains('collapsed')) {
                        sidebar.classList.add('collapsed');
                    }
                    if (sidebarOverlay && !sidebarOverlay.classList.contains('active')) {
                        sidebarOverlay.classList.add('active');
                    }
                    // Prevent default action to ensure our custom handlers work
                    e.preventDefault();
                }
            }
        });
    });

    // Add special handling for final submenu items (those without further submenus)
    const finalSubmenuItems = document.querySelectorAll('.submenu-link[data-form]');
    finalSubmenuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                console.log('Final submenu item clicked:', this.textContent.trim());
                // This is a final action item, so close the sidebar
                sidebar.classList.remove('collapsed');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('active');
                }
                document.body.style.overflow = '';
            }
        });
    });
});
