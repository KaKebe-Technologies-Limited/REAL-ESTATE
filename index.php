<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>ALLEA - East African Land and Rentals</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/custom-loader.css">
    <link rel="stylesheet" href="assets/css/mobile-menu-fix.css">
    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/custom-loader.js" defer></script>
</head>
<body>
<header class="master_header wide header_transparent">
    <!-- Top Bar -->
    <div class="top_bar_wrapper">
        <div class="top_bar">
            <div class="left-top-widet">
                <div class="social_sidebar_internal">
                    <a href="https://www.facebook.com/profile.php?id=61573780542915"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="right-top-widet">
                <div class="contact_info">
                    <span><i class="far fa-envelope"></i> <a href="mailto:info@example.com">info@eastafricanlandandrentals.net</a></span>
                    <span><i class="fas fa-phone-square-alt"></i> <a href="tel:+256764502805">+256764502805</a></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="header_wrapper">
        <div class="header_wrapper_inside">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo1.png" alt="Logo" class="img-responsive">
                </a>
            </div>
            <nav class="main_menu">
                <ul class="menu">
                    <li class="active"><a href="index.php">Home</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                </ul>
            </nav>
            <div class="user_menu">
                <a href="login.html" class="login-btn">
                    <i class="fas fa-user"></i> Login
                </a>
            </div>
            <button class="mobile-menu-button">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Add Mobile Menu -->
    <div class="mobile-menu-wrapper">
        <nav>
            <ul class="menu">
                <li class="active"><a href="index.php">Home</a></li>
                <li><a href="properties.php">Properties</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
            <a href="login.html" class="login-btn">
                <i class="fas fa-user"></i> Login
            </a>
        </nav>
    </div>
</header>
    <section class="hero-section">
    <div class="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/images/property10.png" alt="APARTMENTS & LAND LINKS">
                <div class="carousel-caption">
                    <div class="caption-content">
                        <h1 class="text-3xl md:text-4xl font-bold mb-4">Welcome to ALLEA PROPERTIES !!</h1>
                        <p class="text-lg md:text-xl leading-relaxed mb-6">
                            Here We connect you from City to City, Town to Town, Village to Village for properties and take no Commission from your transactions.
                        </p>
                        <div class="cta-buttons">
                            <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/property21.png" alt="Cost-Effective Solutions">
                <div class="carousel-caption">
                    <div class="caption-content">
                        <h1>Cost-Effective Solutions</h1>
                        <p>Competitive pricing for both property listings and direct calls, minimizing transaction costs.</p>
                        <div class="cta-buttons">
                            <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/property22.png" alt="Cost-Effective Solutions">
                <div class="carousel-caption">
                    <div class="caption-content">
                        <h1>Cost-Effective Solutions</h1>
                        <p>Competitive pricing for both property listings and direct calls, minimizing transaction costs.</p>
                        <div class="cta-buttons">
                            <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/images/property23.png" alt="Cost-Effective Solutions">
                <div class="carousel-caption">
                    <div class="caption-content">
                        <h1>Cost-Effective Solutions</h1>
                        <p>Competitive pricing for both property listings and direct calls, minimizing transaction costs.</p>
                        <div class="cta-buttons">
                            <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="about-section bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="about-image relative">
                <img src="assets/images/property10.jpeg" alt="About Us" class="rounded-lg shadow-lg w-full h-[500px] object-cover">
                <div class="experience-badge absolute -bottom-6 -right-6 bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-4xl font-bold text-blue-600">15+</h3>
                    <p class="text-gray-600">Years of Experience</p>
                </div>
            </div>
            <div class="about-content">
                <h6 class="text-blue-600 font-semibold mb-2">ABOUT US</h6>
                <h2 class="text-4xl font-bold mb-6">We Help You Find Your Dream Home</h2>
                <p class="text-gray-600 mb-6">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="achievement">
                        <h4 class="text-2xl font-bold text-blue-600">500+</h4>
                        <p class="text-gray-600">Properties Sold</p>
                    </div>
                    <div class="achievement">
                        <h4 class="text-2xl font-bold text-blue-600">300+</</h4>
                        <p class="text-gray-600">Happy Clients</p>
                    </div>
                </div>
                <a href="#" class="btn btn-primary px-8 py-3 rounded-lg">Learn More</a>
            </div>
        </div>
    </div>
</section>

<!-- After About Us Section and before Services Section -->
<section class="company-overview bg-white py-20">
    <div class="container mx-auto px-4">
        <!-- Mission & Vision -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20">
            <div class="mission-card" data-aos="fade-right">
                <div class="bg-blue-50 rounded-lg p-8 h-full transform hover:-translate-y-2 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bullseye text-2xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold ml-4 text-gray-800">Our Mission</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Our mission is to simplify real estate transactions in East Africa by providing an accessible and cost-effective platform for property owners to list their properties and for renters or buyers to connect directly with them, thereby eliminating unnecessary middlemen and reducing costs for all parties.
                    </p>
                </div>
            </div>

            <div class="vision-card" data-aos="fade-left">
                <div class="bg-emerald-50 rounded-lg p-8 h-full transform hover:-translate-y-2 transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-emerald-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-eye text-2xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold ml-4 text-gray-800">Our Vision</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        To be the leading online marketplace in East Africa for real estate, empowering property owners and prospective tenants or investors to connect seamlessly and directly, fostering transparency and efficiency in property transactions.
                    </p>
                </div>
            </div>
        </div>

        <!-- Company Overview -->
        <div class="text-center mb-16">
            <h6 class="text-blue-600 font-semibold mb-2">COMPANY OVERVIEW</h6>
            <h2 class="text-4xl font-bold mb-4">Why Choose ALL-EA?</h2>
            <p class="text-gray-600 max-w-3xl mx-auto">
                We bring together the best talent and technology to provide exceptional real estate services that exceed expectations.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="overview-card" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-award text-2xl text-blue-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Cost-Effective Solutions</h4>
                    <p class="text-gray-600">: Competitive pricing for both property listings and direct calls, minimizing transaction costs compared to traditional brokers.</p>
                </div>
            </div>

            <div class="overview-card" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-handshake text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">User-Friendly Interface</h4>
                    <p class="text-gray-600">An intuitive platform that simplifies the property search and listing process for all users.</p>
                </div>
            </div>

            <div class="overview-card" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-2xl text-purple-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Endless Listings</h4>
                    <p class="text-gray-600">Allows property owners to list properties without restrictions, providing maximum exposure according to their needs.</p>
                </div>
            </div>

            <div class="overview-card" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-orange-600"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-2">Comprehensive Coverage</h4>
                    <p class="text-gray-600">Covers all East African countries and includes remote villages and urban centers, catering to a diverse audience.</p>
                </div>
            </div>
        </div>

        <!-- Company Stats -->
        <div class="stats-section mt-20 bg-gray-50 rounded-xl p-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="stat-item text-center" data-aos="zoom-in">
                    <div class="text-4xl font-bold text-blue-600 mb-2">1500+</div>
                    <p class="text-gray-600">Properties Sold</p>
                </div>
                <div class="stat-item text-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-4xl font-bold text-blue-600 mb-2">98%</div>
                    <p class="text-gray-600">Client Satisfaction</p>
                </div>
                <div class="stat-item text-center" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-4xl font-bold text-blue-600 mb-2">12+</div>
                    <p class="text-gray-600">Years Experience</p>
                </div>
                <div class="stat-item text-center" data-aos="zoom-in" data-aos-delay="300">
                    <div class="text-4xl font-bold text-blue-600 mb-2">200+</</div>
                    <p class="text-gray-600">Expert Agents</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h6 class="text-blue-600 font-semibold mb-2">OUR SERVICES</h6>
            <h2 class="text-4xl font-bold">What We Offer</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="service-card p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                <div class="icon-box mb-4 text-blue-600">
                    <i class="fas fa-home text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Property Listings</h3>
                <p class="text-gray-600">:Unlimited listing options for landlords and sellers.</p>
            </div>
            <div class="service-card p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                <div class="icon-box mb-4 text-blue-600">
                    <i class="fas fa-key text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Search Functionality</h3>
                <p class="text-gray-600">Customized search options to filter listings based on specifications (price, location, type of property, etc.).</p>
            </div>
            <div class="service-card p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                <div class="icon-box mb-4 text-blue-600">
                    <i class="fas fa-chart-line text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Market Insights</h3>
                <p class="text-gray-600">Providing users with information about market trends, pricing, and neighborhood analysis.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="features-content">
                <h6 class="text-blue-600 font-semibold mb-2">WHY CHOOSE US</h6>
                <h2 class="text-4xl font-bold mb-8">The Right Choice for Your Property Needs</h2>
                <div class="features space-y-6">
                    <div class="feature flex items-start space-x-4">
                        <div class="icon text-blue-600">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold mb-2">Expert Team</h4>
                            <p class="text-gray-600">Our experienced professionals are here to guide you.</p>
                        </div>
                    </div>
                    <div class="feature flex items-start space-x-4">
                        <div class="icon text-blue-600">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold mb-2">Trusted Service</h4>
                            <p class="text-gray-600">Building trust through transparency and reliability.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="features-image">
                <img src="assets/images/property23.jpeg" alt="Why Choose Us" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h6 class="text-blue-600 font-semibold mb-2">TESTIMONIALS</h6>
            <h2 class="text-4xl font-bold">What Our Clients Say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="testimonial-card p-6 bg-white rounded-lg shadow-lg">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-4xl text-blue-600"></i>
                </div>
                <p class="text-gray-600 mb-6">"The property filter system is fantastic - I could narrow down exactly what I was looking for in seconds. Found my dream home because of it!"</p>
                <div class="flex items-center">
                    <img src="assets/images/profile4.jpeg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h5 class="font-bold">Kansime Jackson</h5>
                        <p class="text-gray-600">Property Buyer</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card p-6 bg-white rounded-lg shadow-lg">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-4xl text-blue-600"></i>
                </div>
                <p class="text-gray-600 mb-6">"Excellent service! They helped me find my dream home within my budget."</p>
                <div class="flex items-center">
                    <img src="assets/images/profile5.jpeg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h5 class="font-bold">Oludi Tonny Blair</h5>
                        <p class="text-gray-600">Property Buyer</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card p-6 bg-white rounded-lg shadow-lg">
                <div class="mb-4">
                    <i class="fas fa-quote-left text-4xl text-blue-600"></i>
                </div>
                <p class="text-gray-600 mb-6">"Excellent service! They helped me find my dream home within my budget."</p>
                <div class="flex items-center">
                    <img src="assets/images/profile6.jpeg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h5 class="font-bold">Bukenya John</h5>
                        <p class="text-gray-600">Property Buyer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <!-- Newsletter Section -->
    <div class="newsletter-section bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="newsletter-content text-center">
                <h2 class="text-3xl font-bold mb-4">Subscribe to Our Newsletter</h2>
                <p class="text-lg mb-8 max-w-2xl mx-auto">Stay updated with our latest property listings, market trends, and exclusive offers.</p>
                <form id="newsletter-form" class="newsletter-form flex flex-col md:flex-row gap-4 max-w-lg mx-auto" action="https://formspree.io/f/your-formspree-id" method="POST">
                    <input type="email" name="email" placeholder="Your email address" required class="flex-grow py-3 px-4 rounded-lg">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition duration-300">
                        Subscribe <span>&rarr;</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-content">
        <div class="footer-section">
            <h3>About Us</h3>
            <p>East African Land and Rentals is a premier real estate company specializing in property sales and rentals across East Africa.</p>
            <div class="social-links mt-4">
                <a href="https://www.facebook.com/profile.php?id=61573780542915"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="properties.php">Properties</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> Jinja Camp-A, Lira City West (U)</li>
                <li><i class="fas fa-phone"></i> +256764502805</li>
                <li><i class="fas fa-envelope"></i> info@eastafricanlandandrentals.net</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> East African Land and Rentals. All rights reserved.</p>
    </div>
</footer>
<!-- Add WhatsApp Float Button -->
<a href="https://wa.me/256764502805" class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Back to top button -->
<button id="backToTop" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Include AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom alerts must be loaded before other scripts -->
<script src="assets/js/custom-alerts.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/mobile-menu-fix.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 1200,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Set current year in footer if the element exists
    const currentYearElement = document.getElementById('currentYear');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }

    let currentSlide = 0;
    const items = document.querySelectorAll('.carousel-item');

    function nextSlide() {
        items[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % items.length;
        items[currentSlide].classList.add('active');
    }

    // Start with first slide active
    items[0].classList.add('active');

    // Auto advance slides every 5 seconds
    setInterval(nextSlide, 5000);

    // Updated Property filtering with smooth scroll
    const filterButtons = document.querySelectorAll('.category-btn');
    const propertyCards = document.querySelectorAll('.property_listing');
    const contentSection = document.getElementById('content-section');

    if (filterButtons.length > 0 && propertyCards.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');

                propertyCards.forEach(card => {
                    if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'block';
                        card.style.opacity = '1';
                    } else {
                        card.style.display = 'none';
                        card.style.opacity = '0';
                    }
                });

                // Smooth scroll to content section if it exists
                if (contentSection) {
                    contentSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    // Back to top button functionality
    const backToTopButton = document.getElementById('backToTop');

    if (backToTopButton) {
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
    }

    // Toggle advanced search
    const advancedSearchBtn = document.getElementById('advanced-search-btn');
    if (advancedSearchBtn) {
        advancedSearchBtn.addEventListener('click', function() {
            const searchWrapper = document.getElementById('search-wrapper');
            const contentSection = document.getElementById('content-section');

            if (searchWrapper) searchWrapper.classList.toggle('show');
            this.classList.toggle('active');

            // Ensure content section is always visible
            if (contentSection) {
                contentSection.style.display = 'block';
                contentSection.style.visibility = 'visible';
                contentSection.style.opacity = '1';
            }
        });
    }

    // Handle search form submission
    const advancedSearchForm = document.getElementById('advanced-search-form');
    if (advancedSearchForm) {
        advancedSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would normally handle the search logic
            // For demo, just update results count
            const resultsCount = document.getElementById('results-count');
            if (resultsCount) resultsCount.textContent = '5';
        });
    }

    // Add header scroll effect
    let lastScrollPosition = 0;

    window.addEventListener('scroll', function() {
        const headerWrapper = document.querySelector('.header_wrapper');
        const topBarWrapper = document.querySelector('.top_bar_wrapper');
        const currentScroll = window.pageYOffset;

        // Add scrolled class to header wrapper if elements exist
        if (headerWrapper && topBarWrapper) {
            if (currentScroll > 50) {
                headerWrapper.classList.add('scrolled');
                topBarWrapper.classList.add('hidden');
            } else {
                headerWrapper.classList.remove('scrolled');
                topBarWrapper.classList.remove('hidden');
            }
        }

        lastScrollPosition = currentScroll;
    });

    // ...rest of existing script...
</script>
<script src="assets/js/loader.js"></script>
</body>
</html>