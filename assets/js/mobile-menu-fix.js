// Mobile Menu Fix - Consolidated Script
document.addEventListener('DOMContentLoaded', function() {
    console.log('Mobile menu fix script loaded');
    
    // Get mobile menu elements
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenuWrapper = document.querySelector('.mobile-menu-wrapper');
    
    if (!mobileMenuButton || !mobileMenuWrapper) {
        console.error('Mobile menu elements not found in mobile-menu-fix.js');
        return;
    }
    
    console.log('Mobile menu elements found in mobile-menu-fix.js');
    
    // Remove any existing click event listeners by cloning and replacing the button
    const newMobileMenuButton = mobileMenuButton.cloneNode(true);
    mobileMenuButton.parentNode.replaceChild(newMobileMenuButton, mobileMenuButton);
    
    // Add click event listener to mobile menu button
    newMobileMenuButton.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Mobile menu button clicked in mobile-menu-fix.js');
        
        // Toggle active class on mobile menu wrapper
        mobileMenuWrapper.classList.toggle('active');
        console.log('Mobile menu active state:', mobileMenuWrapper.classList.contains('active'));
        
        // Change icon based on menu state
        const icon = this.querySelector('i');
        if (icon) {
            if (mobileMenuWrapper.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenuWrapper.classList.contains('active') && 
            !e.target.closest('.mobile-menu-wrapper') && 
            !e.target.closest('.mobile-menu-button')) {
            
            mobileMenuWrapper.classList.remove('active');
            
            const icon = newMobileMenuButton.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });
    
    // Close mobile menu when clicking on a menu item
    const mobileMenuItems = mobileMenuWrapper.querySelectorAll('a');
    mobileMenuItems.forEach(item => {
        item.addEventListener('click', function() {
            mobileMenuWrapper.classList.remove('active');
            
            const icon = newMobileMenuButton.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    });
});
