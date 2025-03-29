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

// Search wrapper toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchToggle = document.getElementById('searchToggle');
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const searchWrapper = document.getElementById('search_wrapper');

    function toggleSearch(button) {
        searchWrapper.classList.toggle('show');
        const icon = button.querySelector('i');
        
        if (searchWrapper.classList.contains('show')) {
            icon.className = 'fas fa-times';
            button.querySelector('span') ? button.querySelector('span').textContent = ' Close' : button.textContent = ' Close';
        } else {
            icon.className = 'fas fa-search';
            button.querySelector('span') ? button.querySelector('span').textContent = ' Search' : button.textContent = ' Search';
        }
    }

    searchToggle.addEventListener('click', () => toggleSearch(searchToggle));
    mobileSearchToggle.addEventListener('click', () => toggleSearch(mobileSearchToggle));
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

// Add mobile header scroll behavior
let lastScroll = 0;
const header = document.querySelector('.master_header');

window.addEventListener('scroll', () => {
    if (window.innerWidth <= 768) {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.classList.remove('nav-up');
            header.classList.add('nav-down');
            return;
        }
        
        if (currentScroll > lastScroll && currentScroll > 60) {
            // Scrolling down & past header height
            header.classList.remove('nav-down');
            header.classList.add('nav-up');
        } else {
            // Scrolling up
            header.classList.remove('nav-up');
            header.classList.add('nav-down');
        }
        lastScroll = currentScroll;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Get all filter buttons and property listings
    const filterButtons = document.querySelectorAll('.filter-btn');
    const propertyListings = document.querySelectorAll('.property_listing');

    // Add click event listener to each filter button
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');

            // Show/hide properties based on filter
            propertyListings.forEach(property => {
                if (filterValue === 'all') {
                    property.style.display = 'block';
                } else {
                    if (property.getAttribute('data-category') === filterValue) {
                        property.style.display = 'block';
                    } else {
                        property.style.display = 'none';
                    }
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Pagination variables
    const itemsPerPage = 12;
    let currentPage = 1;
    
    function setupPagination() {
        const propertyGrid = document.querySelector('.property-grid');
        const properties = propertyGrid.getElementsByClassName('property_listing');
        const totalPages = Math.ceil(properties.length / itemsPerPage);
        
        // Generate page numbers
        const pageNumbers = document.querySelector('.page-numbers');
        pageNumbers.innerHTML = '';
        
        for (let i = 1; i <= totalPages; i++) {
            const pageNumber = document.createElement('button');
            pageNumber.className = `page-number ${i === currentPage ? 'active' : ''}`;
            pageNumber.textContent = i;
            pageNumber.addEventListener('click', () => goToPage(i));
            pageNumbers.appendChild(pageNumber);
        }
        
        // Update prev/next buttons
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        
        // Show/hide properties for current page
        Array.from(properties).forEach((property, index) => {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            
            if (index >= start && index < end) {
                property.style.display = 'block';
            } else {
                property.style.display = 'none';
            }
        });
    }
    
    function goToPage(page) {
        currentPage = page;
        setupPagination();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Add event listeners for prev/next buttons
    document.querySelector('.prev-btn').addEventListener('click', () => {
        if (currentPage > 1) {
            goToPage(currentPage - 1);
        }
    });
    
    document.querySelector('.next-btn').addEventListener('click', () => {
        const totalPages = Math.ceil(
            document.getElementsByClassName('property_listing').length / itemsPerPage
        );
        if (currentPage < totalPages) {
            goToPage(currentPage + 1);
        }
    });
    
    // Initialize pagination
    setupPagination();
    
    // Update pagination when filters are applied
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            currentPage = 1;
            setupPagination();
        });
    });
});

document.getElementById('newsletter-form').addEventListener('submit', function (e) {
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
