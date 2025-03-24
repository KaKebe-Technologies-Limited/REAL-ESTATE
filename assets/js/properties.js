document.addEventListener('DOMContentLoaded', function() {
    const filterMenuTriggers = document.querySelectorAll('.filter_menu_trigger');
    
    filterMenuTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const menu = this.parentElement.querySelector('.filter_menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
    });

    document.querySelectorAll('.filter_menu li').forEach(item => {
        item.addEventListener('click', function() {
            const value = this.dataset.value;
            const trigger = this.closest('.dropdown').querySelector('.filter_menu_trigger');
            const input = this.closest('.dropdown').querySelector('input[type="hidden"]');
            
            trigger.textContent = this.textContent;
            trigger.appendChild(document.createElement('span')).className = 'caret caret_filter';
            input.value = value;
            
            this.closest('.filter_menu').style.display = 'none';
        });
    });

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.filter_menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenuWrapper = document.querySelector('.mobile-menu-wrapper');
    
    mobileMenuButton.addEventListener('click', function() {
        mobileMenuWrapper.classList.toggle('active');
        // Change icon based on menu state
        const icon = this.querySelector('i');
        if (mobileMenuWrapper.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.mobile-menu-wrapper') && 
            !e.target.closest('.mobile-menu-button') && 
            mobileMenuWrapper.classList.contains('active')) {
            mobileMenuWrapper.classList.remove('active');
            const icon = mobileMenuButton.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    });

    // Add Newsletter Form Handler
    document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        
        // Show success message
        const button = this.querySelector('button');
        const originalText = button.innerText;
        button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
        button.classList.add('bg-green-600');
        
        // Reset form after 2 seconds
        setTimeout(() => {
            this.reset();
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600');
        }, 2000);
        
        // Here you would typically send the email to your backend
        console.log('Newsletter subscription for:', email);
    });
});

// Add scroll event handler for header
window.addEventListener('scroll', function() {
    const headerWrapper = document.querySelector('.header_wrapper');
    const topBarWrapper = document.querySelector('.top_bar_wrapper');
    const scrollPosition = window.scrollY;

    if (scrollPosition > 100) {
        headerWrapper.classList.add('scrolled');
        headerWrapper.style.background = 'white';
        topBarWrapper.classList.add('hidden');
    } else {
        headerWrapper.classList.remove('scrolled');
        topBarWrapper.classList.remove('hidden');
    }
});

 // Back to top button functionality
const backToTopButton = document.getElementById('backToTop');

window.onscroll = function() {
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        backToTopButton.style.display = "block";
    } else {
        backToTopButton.style.display = "none";
    }
};

backToTopButton.onclick = function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};
