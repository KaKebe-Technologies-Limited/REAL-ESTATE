// Direct submenu click fix for mobile
// This script directly handles submenu clicks on mobile devices

document.addEventListener('DOMContentLoaded', function() {
    console.log('Submenu click fix loaded');

    // Direct event handlers for submenu toggles
    document.querySelectorAll('.has-submenu > .nav-link[data-toggle="submenu"]').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Main submenu clicked directly');

            // Get parent li element
            const parentLi = this.parentElement;

            // Toggle open class
            if (parentLi.classList.contains('open')) {
                parentLi.classList.remove('open');
                console.log('Removing open class');
            } else {
                parentLi.classList.add('open');
                console.log('Adding open class');
            }

            // Keep sidebar open on mobile
            const sidebar = document.getElementById('sidebar');
            if (sidebar && window.innerWidth <= 991) {
                sidebar.classList.add('collapsed');

                // Show overlay
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.classList.add('active');
                }

                // Prevent scrolling
                document.body.style.overflow = 'hidden';
            }

            console.log('Submenu open state after click:', parentLi.classList.contains('open'));
        });
    });

    // Direct event handlers for property submenu toggles
    document.querySelectorAll('.submenu-link[data-toggle="property-submenu"]').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Property submenu clicked directly');

            // Get the submenu
            const submenu = this.nextElementSibling;

            // Toggle show class
            if (submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    console.log('Removing show class');
                } else {
                    submenu.classList.add('show');
                    console.log('Adding show class');
                }

                // Toggle arrow rotation
                const arrow = this.querySelector('.property-submenu-arrow');
                if (arrow) {
                    arrow.classList.toggle('rotate');
                }

                // Keep sidebar open on mobile
                const sidebar = document.getElementById('sidebar');
                if (sidebar && window.innerWidth <= 991) {
                    sidebar.classList.add('collapsed');

                    // Show overlay
                    const overlay = document.getElementById('sidebar-overlay');
                    if (overlay) {
                        overlay.classList.add('active');
                    }

                    // Prevent scrolling
                    document.body.style.overflow = 'hidden';
                }

                console.log('Property submenu show state after click:', submenu.classList.contains('show'));
            }
        });
    });

    // Add debug info
    console.log('Found main submenu toggles:', document.querySelectorAll('.has-submenu > .nav-link[data-toggle="submenu"]').length);
    console.log('Found property submenu toggles:', document.querySelectorAll('.submenu-link[data-toggle="property-submenu"]').length);
});
