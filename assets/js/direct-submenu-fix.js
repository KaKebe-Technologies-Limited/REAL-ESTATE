// Direct submenu fix - overrides all previous attempts
document.addEventListener('DOMContentLoaded', function() {
    console.log('Direct submenu fix loaded');
    
    // Function to check if we're on mobile
    function isMobileView() {
        return window.innerWidth <= 991;
    }
    
    // Remove all existing event listeners from submenu toggles (brute force approach)
    function removeAllEventListeners(element) {
        const clone = element.cloneNode(true);
        element.parentNode.replaceChild(clone, element);
        return clone;
    }
    
    // Process all main submenu toggles
    document.querySelectorAll('.has-submenu > .nav-link').forEach(function(toggle) {
        // Remove all existing event listeners
        const newToggle = removeAllEventListeners(toggle);
        
        // Add our own click event listener
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Main submenu clicked');
            
            // Get parent li element
            const parentLi = this.parentElement;
            
            // Force toggle open class
            if (parentLi.classList.contains('open')) {
                parentLi.classList.remove('open');
                console.log('Removed open class');
            } else {
                parentLi.classList.add('open');
                console.log('Added open class');
                
                // Close other open submenus
                document.querySelectorAll('.has-submenu.open').forEach(function(item) {
                    if (item !== parentLi) {
                        item.classList.remove('open');
                    }
                });
            }
            
            // Keep sidebar open on mobile
            if (isMobileView()) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('collapsed');
                }
                
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) {
                    overlay.classList.add('active');
                }
                
                document.body.style.overflow = 'hidden';
            }
            
            console.log('Submenu open state after click:', parentLi.classList.contains('open'));
        });
    });
    
    // Process all property submenu toggles
    document.querySelectorAll('.submenu-link[data-toggle="property-submenu"]').forEach(function(toggle) {
        // Remove all existing event listeners
        const newToggle = removeAllEventListeners(toggle);
        
        // Add our own click event listener
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Property submenu clicked');
            
            // Get the submenu
            const submenu = this.nextElementSibling;
            
            // Force toggle show class
            if (submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    console.log('Removed show class');
                } else {
                    submenu.classList.add('show');
                    console.log('Added show class');
                    
                    // Close other open property submenus
                    document.querySelectorAll('.property-submenu.show').forEach(function(item) {
                        if (item !== submenu) {
                            item.classList.remove('show');
                        }
                    });
                }
                
                // Toggle arrow rotation
                const arrow = this.querySelector('.property-submenu-arrow');
                if (arrow) {
                    arrow.classList.toggle('rotate');
                }
                
                // Keep sidebar open on mobile
                if (isMobileView()) {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar) {
                        sidebar.classList.add('collapsed');
                    }
                    
                    const overlay = document.getElementById('sidebar-overlay');
                    if (overlay) {
                        overlay.classList.add('active');
                    }
                    
                    document.body.style.overflow = 'hidden';
                }
                
                console.log('Property submenu show state after click:', submenu.classList.contains('show'));
            }
        });
    });
    
    // Add debug info
    console.log('Found main submenu toggles:', document.querySelectorAll('.has-submenu > .nav-link').length);
    console.log('Found property submenu toggles:', document.querySelectorAll('.submenu-link[data-toggle="property-submenu"]').length);
    
    // Force all submenus to be closed initially
    document.querySelectorAll('.has-submenu').forEach(function(item) {
        item.classList.remove('open');
    });
    
    document.querySelectorAll('.property-submenu').forEach(function(item) {
        item.classList.remove('show');
    });
});
