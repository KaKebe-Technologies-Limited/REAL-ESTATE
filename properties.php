<?php
// Define a constant to indicate this file is including fetch_properties.php
define('INCLUDED_IN_PROPERTIES', true);

// Include the properties fetching script
include 'fetch_properties.php';

// Helper function to format price
function formatPrice($price) {
    return number_format($price);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - East African Land and Rentals</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/properties.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header class="master_header ">
        <!-- Main Header -->
        <div class="header_wrapper">
            <div class="header_wrapper_inside">
                <div class="logo">
                    <a href="index.html">
                        <img src="assets/images/logo1.png" alt="Logo" class="img-responsive">
                    </a>
                </div>
                <nav class="main_menu">
                    <ul class="menu">
                        <li><a href="index.html">Home</a></li>
                        <li class="active"><a href="properties.php">Properties</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </nav>
                <div class="user_menu">
                    <button id="searchToggle" class="search-toggle-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
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
                    <li><a href="index.html">Home</a></li>
                    <li class="active"><a href="properties.php">Properties</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                </ul>
                <div class="mobile-buttons">
                    <button id="mobileSearchToggle" class="search-toggle-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="login.html" class="login-btn">
                        <i class="fas fa-user"></i> Login
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <!-- Search wrapper -->
        <div class="search_wrapper search_wr_4 with_search_on_end without_search_form_float" id="search_wrapper">
            <div id="search_wrapper_color"></div>
            <div class="adv-search-1" id="adv-search-1">
                <div id="adv-search-header-1">Advanced Search</div>
                <form role="search" method="get" action="properties.php">
                    <div class="adv4-holder">
                        <!-- Cities -->
                        <div class="col-md-3 cities" style="width: 100%;">
                            <div class="dropdown form-control">
                                <div data-toggle="dropdown" id="advanced_city" class="filter_menu_trigger" data-value="all">
                                    Cities<span class="caret caret_filter"></span>
                                </div>
                                <input type="hidden" name="city" value="">
                                <ul id="adv-search-city" class="dropdown-menu filter_menu" role="menu">
                                    <li role="presentation" data-value="all">Cities</li>
                                    <li role="presentation" data-value="bugolobi">Bugolobi</li>
                                    <li role="presentation" data-value="bujuko">Bujuko</li>
                                    <!-- Add other cities -->
                                </ul>
                            </div>
                        </div>

                        <!-- Areas -->
                        <div class="col-md-3 areas">
                            <div class="dropdown form-control">
                                <div data-toggle="dropdown" id="advanced_area" class="filter_menu_trigger" data-value="all">
                                    Areas<span class="caret caret_filter"></span>
                                </div>
                                <input type="hidden" name="area" value="">
                                <ul id="adv-search-area" class="dropdown-menu filter_menu">
                                    <li role="presentation" data-value="all">Areas</li>
                                    <li role="presentation" data-value="bakuli">Bakuli</li>
                                    <li role="presentation" data-value="bugolobi">Bugolobi</li>
                                    <!-- Add other areas -->
                                </ul>
                            </div>
                        </div>

                        <!-- Types -->
                        <div class="col-md-3 types">
                            <div class="dropdown form-control">
                                <div data-toggle="dropdown" id="adv_actions" class="filter_menu_trigger" data-value="all">
                                    Types<span class="caret caret_filter"></span>
                                </div>
                                <input type="hidden" name="type" value="">
                                <ul id="actionslist" class="dropdown-menu filter_menu">
                                    <li role="presentation" data-value="all">Types</li>
                                    <li role="presentation" data-value="rent">Rentals</li>
                                    <li role="presentation" data-value="sale">Sales</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Bedrooms -->
                        <div class="col-md-3 property_bedrooms">
                            <div class="dropdown form-control">
                                <div data-toggle="dropdown" id="bedrooms" class="filter_menu_trigger" data-value="all">
                                    Bedrooms<span class="caret caret_filter"></span>
                                </div>
                                <input type="hidden" name="bedrooms" value="">
                                <ul id="search-bedrooms" class="dropdown-menu filter_menu">
                                    <li role="presentation" data-value="all">Bedrooms</li>
                                    <li data-value="1">1</li>
                                    <li data-value="2">2</li>
                                    <li data-value="3">3</li>
                                    <li data-value="4">4</li>
                                    <li data-value="5">5+</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Bathrooms -->
                        <div class="col-md-3 property_bathrooms">
                            <div class="dropdown form-control">
                                <div data-toggle="dropdown" id="bathrooms" class="filter_menu_trigger" data-value="all">
                                    Bathrooms<span class="caret caret_filter"></span>
                                </div>
                                <input type="hidden" name="bathrooms" value="">
                                <ul id="search-bathrooms" class="dropdown-menu filter_menu">
                                    <li role="presentation" data-value="all">Bathrooms</li>
                                    <li data-value="1">1</li>
                                    <li data-value="2">2</li>
                                    <li data-value="3">3</li>
                                    <li data-value="4">4</li>
                                    <li data-value="5">5+</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="col-md-3 property_bathrooms">
                            <input name="submit" type="submit" class="wpresidence_button advanced_submit_4" value="Search Properties">
                        </div>
                    </div>

                    <div id="results">
                        <div class="results_header">
                            We found <span id="results_no">0</span> results.
                            <span id="preview_view_all">View results</span>
                        </div>
                        <div id="results_wrapper"></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Content Section -->
        <section id="content-section" class="content-section">
            <div class="section-header">
                <h2 class="section-title">Available Properties</h2>
                <div class="property-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="sale">Sales</button>
                    <button class="filter-btn" data-filter="rental">Rentals</button>
                </div>
            </div>
            <div class="property-grid">
                <div class="no-properties" style="display: <?php echo empty($allProperties) ? 'block' : 'none'; ?>">
                    <p>No properties found matching your criteria. Please try a different filter or check back later.</p>
                </div>
                <?php if (!empty($allProperties)): ?>
                    <?php foreach ($allProperties as $property): ?>
                        <div class="property_listing property_card_default" data-category="<?php echo $property['property_type']; ?>">
                            <div class="listing-unit-img-wrapper">
                                <div class="prop_new_details">
                                    <div class="prop_new_details_back"></div>
                                    <div class="property_media">
                                        <i class="fas fa-camera"></i> <?php echo !empty($property['all_images']) ? count($property['all_images']) : '0'; ?>
                                    </div>
                                    <div class="property_location_image">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <a href="#" rel="tag"><?php echo htmlspecialchars($property['location']); ?></a>
                                    </div>
                                    <div class="featured_gradient"></div>
                                </div>
                                <a href="property-detail.php?id=<?php echo $property['property_id']; ?>&type=<?php echo $property['property_type']; ?>" target="_self">
                                    <img fetchpriority="high" decoding="async" width="525" height="328" src="<?php echo htmlspecialchars($property['image']); ?>" class="lazyload img-responsive wp-post-image image" alt="<?php echo htmlspecialchars($property['property_name']); ?>" onerror="this.src='assets/images/property-placeholder.jpg'">
                                </a>
                                <div class="tag-wrapper">
                                    <?php if (isset($property['is_featured']) && $property['is_featured']): ?>
                                    <div class="featured_div">Featured</div>
                                    <?php endif; ?>
                                    <div class="status-wrapper">
                                        <div class="action_tag_wrapper <?php echo ucfirst($property['property_type']); ?>"><?php echo ucfirst($property['property_type']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="property-unit-information-wrapper">
                                <h4>
                                    <a href="property-detail.php?id=<?php echo $property['property_id']; ?>&type=<?php echo $property['property_type']; ?>" style="text-decoration: none; color: #222222;">
                                        <?php echo htmlspecialchars($property['property_name']); ?>
                                    </a>
                                </h4>
                                <div class="listing_unit_price_wrapper"  style="text-decoration: none; color: #222222;">
                                    <?php
                                        if (is_numeric($property['price'])) {
                                            echo 'UGX ' . $property['formatted_price'];
                                        } else {
                                            echo htmlspecialchars($property['price']);
                                        }
                                    ?>
                                    <span class="price_label"></span>
                                </div>
                                <div class="listing_details the_grid_view">
                                    <?php echo htmlspecialchars(substr($property['property_class'], 0, 100)); ?>
                                </div>
                                <div class="property_listing_details">
                                    <span class="inforoom">
                                        <i class="fas fa-bed" style="width: 25px;"></i>
                                        3
                                    </span>
                                    <span class="infobath">
                                        <i class="fas fa-bath" style="width: 25px;"></i>
                                        5
                                    </span>
                                    <span class="infosize">
                                        <i class="fas fa-ruler-combined" style="width: 25px;"></i>
                                        <?php echo htmlspecialchars($property['property_size']); ?> sqm
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination">
                    <button type="button" class="page-btn prev-btn" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="page-numbers">
                        <!-- Page numbers will be dynamically inserted here -->
                    </div>
                    <button type="button" class="page-btn next-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

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
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> 123 Main Street, Kampala, Uganda</li>
                    <li><i class="fas fa-phone"></i> +256 123 456 789</li>
                    <li><i class="fas fa-envelope"></i> info@eastafricanlandandrentals.net</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 East African Land and Rentals. All rights reserved.</p>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/256123456789" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/properties-php.js"></script>
</body>
</html>


