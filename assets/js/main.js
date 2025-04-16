// Add this code to your existing main.js or create it if it doesn't exist

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

    if (scrollPosition > 50) { // Reduced threshold for quicker response
        headerWrapper.classList.add('scrolled');
        topBarWrapper.classList.add('hidden');

        // Set background to white on mobile explicitly
        if (window.innerWidth <= 768) {
            headerWrapper.style.background = 'white';
        }
    } else {
        headerWrapper.classList.remove('scrolled');
        topBarWrapper.classList.remove('hidden');

        // Set background to transparent for all screen sizes
        headerWrapper.style.background = 'transparent';
    }
});

// Also run the header check on page load
document.addEventListener('DOMContentLoaded', function() {
    const headerWrapper = document.querySelector('.header_wrapper');
    const topBarWrapper = document.querySelector('.top_bar_wrapper');
    const scrollPosition = window.scrollY;

    if (scrollPosition > 50) {
        headerWrapper.classList.add('scrolled');
        topBarWrapper.classList.add('hidden');

        if (window.innerWidth <= 768) {
            headerWrapper.style.background = 'white';
        }
    } else {
        headerWrapper.style.background = 'transparent';
    }
});

// Add event listener to newsletter form if it exists
const newsletterForm = document.getElementById('newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const form = e.target;
        const formData = new FormData(form);

        // Send the form data to Formspree
        fetch(form.action, {
            method: form.method,
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                // Show success message
                alert('Thank you for subscribing to our newsletter!');
                form.reset(); // Reset the form
            } else {
                // Show error message
                alert('Oops! There was a problem submitting your form.');
            }
        }).catch(error => {
            // Handle network errors
            alert('Oops! There was a problem submitting your form.');
        });
    });
}
