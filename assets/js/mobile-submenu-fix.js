// Mobile Submenu Fix
// This script makes submenu links clickable on mobile and prevents the sidebar from closing

document.addEventListener('DOMContentLoaded', function() {
    console.log('Mobile submenu fix script loaded');

    // Function to check if we're on mobile
    function isMobileView() {
        return window.innerWidth <= 991;
    }

    // Define global toggle functions to ensure they're available
    window.toggleSubmenu = function(el, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        console.log('toggleSubmenu called');

        const parent = el.parentElement;
        parent.classList.toggle('open');

        // Toggle arrow rotation
        const arrow = el.querySelector('.submenu-arrow');
        if (arrow) {
            arrow.style.transform = parent.classList.contains('open') ? 'rotate(180deg)' : '';
        }

        // Ensure sidebar stays open on mobile
        if (isMobileView()) {
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
    };

    window.togglePropertySubmenu = function(el, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        console.log('togglePropertySubmenu called');

        const submenu = el.nextElementSibling;
        if (submenu) {
            submenu.classList.toggle('show');

            // Toggle arrow rotation
            const arrow = el.querySelector('.property-submenu-arrow');
            if (arrow) {
                arrow.classList.toggle('rotate');
            }

            // Ensure sidebar stays open on mobile
            if (isMobileView()) {
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
    };

    // Get all submenu toggle links
    const submenuToggles = document.querySelectorAll('.has-submenu > .nav-link');
    const propertySubmenuToggles = document.querySelectorAll('.submenu-link[onclick*="togglePropertySubmenu"]');

    console.log('Found submenu toggles:', submenuToggles.length);
    console.log('Found property submenu toggles:', propertySubmenuToggles.length);

    // Remove existing onclick attributes to prevent conflicts
    submenuToggles.forEach(toggle => {
        if (toggle.hasAttribute('onclick')) {
            toggle.removeAttribute('onclick');
        }
    });

    propertySubmenuToggles.forEach(toggle => {
        if (toggle.hasAttribute('onclick')) {
            toggle.removeAttribute('onclick');
        }
    });

    // Add click event listeners to main submenu toggles
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Submenu toggle clicked');

            // Toggle the open class on the parent
            const parent = this.parentElement;
            parent.classList.toggle('open');

            // Toggle arrow rotation
            const arrow = this.querySelector('.submenu-arrow');
            if (arrow) {
                arrow.style.transform = parent.classList.contains('open') ? 'rotate(180deg)' : '';
            }

            // Close other open submenus at the same level
            const siblings = Array.from(parent.parentElement.children).filter(el =>
                el.classList.contains('has-submenu') && el !== parent
            );

            siblings.forEach(sibling => {
                sibling.classList.remove('open');
                const siblingArrow = sibling.querySelector('.submenu-arrow');
                if (siblingArrow) {
                    siblingArrow.style.transform = '';
                }
            });

            // Ensure sidebar stays open on mobile when clicking submenu toggles
            if (isMobileView()) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && !sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                }

                // Prevent the sidebar overlay from being hidden
                const sidebarOverlay = document.getElementById('sidebar-overlay');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('active');
                }

                // Prevent body from scrolling when sidebar is open
                document.body.style.overflow = 'hidden';
            }

            console.log('Submenu open state:', parent.classList.contains('open'));
        });
    });

    // Add click event listeners to property submenu toggles
    propertySubmenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Property submenu toggle clicked');

            // Toggle the show class on the next element sibling (the submenu)
            const submenu = this.nextElementSibling;
            if (submenu) {
                submenu.classList.toggle('show');

                // Toggle arrow rotation
                const arrow = this.querySelector('.property-submenu-arrow');
                if (arrow) {
                    arrow.classList.toggle('rotate');
                }

                // Close other property submenus
                const allPropertySubmenus = document.querySelectorAll('.property-submenu');
                const allPropertyArrows = document.querySelectorAll('.property-submenu-arrow');

                allPropertySubmenus.forEach(menu => {
                    if (menu !== submenu && menu.classList.contains('show')) {
                        menu.classList.remove('show');
                    }
                });

                allPropertyArrows.forEach(arr => {
                    if (arr !== arrow && arr.classList.contains('rotate')) {
                        arr.classList.remove('rotate');
                    }
                });

                // Ensure sidebar stays open on mobile when clicking property submenu toggles
                if (isMobileView()) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar && !sidebar.classList.contains('collapsed')) {
                        sidebar.classList.add('collapsed');
                    }

                    // Prevent the sidebar overlay from being hidden
                    const sidebarOverlay = document.getElementById('sidebar-overlay');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('active');
                    }

                    // Prevent body from scrolling when sidebar is open
                    document.body.style.overflow = 'hidden';
                }

                console.log('Property submenu show state:', submenu.classList.contains('show'));
            }
        });
    });

    // Add visual indicators for clickable submenu items on mobile
    function updateMobileSubmenuIndicators() {
        if (isMobileView()) {
            // Add a class to indicate clickable submenu items
            submenuToggles.forEach(toggle => {
                toggle.classList.add('mobile-clickable');
            });

            propertySubmenuToggles.forEach(toggle => {
                toggle.classList.add('mobile-clickable');
            });
        } else {
            // Remove the class on desktop
            document.querySelectorAll('.mobile-clickable').forEach(el => {
                el.classList.remove('mobile-clickable');
            });
        }
    }

    // Initial call
    updateMobileSubmenuIndicators();

    // Update on resize
    window.addEventListener('resize', updateMobileSubmenuIndicators);
});
