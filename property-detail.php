<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}

// Set character set
$conn->set_charset("utf8mb4");

// Check if property ID and type are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['type']) || empty($_GET['type'])) {
    header('Location: properties.php');
    exit;
}

$property_id = intval($_GET['id']);
$property_type = $_GET['type'];

// Validate property type
if ($property_type !== 'rental' && $property_type !== 'sale') {
    header('Location: properties.php');
    exit;
}

// Initialize property array
$property = [];
$images = [];

// Fetch property details based on type
if ($property_type === 'rental') {
    // Fetch rental property details
    $sql = "SELECT
        r.property_id as id,
        r.property_name,
        r.price,
        r.property_class,
        r.property_size as size,
        r.utilities,
        r.amenities,
        r.images,
        r.parking as garage,
        CONCAT(r.country, ', ', r.region, ', ', r.subregion, ', ', r.parish, ', ', r.ward) as location,
        CONCAT(o.first_name, ' ', o.last_name) as owner_name,
        o.phone as owner_phone,
        o.email as owner_email,
        CONCAT(m.first_name, ' ', m.last_name) as manager_name,
        m.phone as manager_phone,
        m.email as manager_email,
        m.profile_picture as profile_picture,
        m.experience as experience,
        'rental' as property_type,
        r.property_type as property_category,
        r.country,
        r.region,
        r.subregion,
        r.parish,
        r.ward,
        r.cell,
        r.security,
        r.convenience,
        r.description,
        r.bedrooms,
        r.bathrooms,
        r.status
        FROM rental_property r
        LEFT JOIN property_owner o ON r.owner_id = o.owner_id
        LEFT JOIN property_manager m ON r.manager_id = m.manager_id
        WHERE r.property_id = ?";
} else {
    // Fetch sales property details
    $sql = "SELECT
        s.property_id as id,
        s.property_name,
        s.price,
        s.property_type as property_class,
        s.property_size as size,
        s.utilities,
        s.amenities,
        s.images,
        0 as garage,
        CONCAT(s.country, ', ', s.region, ', ', s.subregion, ', ', s.parish, ', ', s.ward) as location,
        CONCAT(o.first_name, ' ', o.last_name) as owner_name,
        o.phone as owner_phone,
        o.email as owner_email,
        CONCAT(m.first_name, ' ', m.last_name) as manager_name,
        m.phone as manager_phone,
        m.email as manager_email,
        m.profile_picture as profile_picture,
        m.experience as experience,
        'sale' as property_type,
        s.property_type as property_category,
        s.country,
        s.region,
        s.subregion,
        s.parish,
        s.ward,
        s.cell,
        '' as security,
        s.title,
        s.description,
        s.bedrooms,
        s.bathrooms,
        s.status
        FROM sales_property s
        LEFT JOIN property_owner o ON s.owner_id = o.owner_id
        LEFT JOIN property_manager m ON s.manager_id = m.manager_id
        WHERE s.property_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: properties.php');
    exit;
}

$property = $result->fetch_assoc();

// Process images from the comma-separated list
if (!empty($property['images'])) {
    $images = array_map('trim', explode(',', $property['images']));
} else {
    // If no images, add a placeholder
    $images[] = 'uploads/contact.jpeg';
}

// Format price function
function formatPrice($price) {
    return number_format($price);
}

// Status (hardcoded for now, could be added to the database tables later)
$property['is_featured'] = true; // Set to true for demonstration
$property['year_built'] = 2022; // Default value

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['property_name']); ?> - East African Land and Rentals</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/fonts.css">
    <link rel="stylesheet" href="assets/css/properties.css">
    <link rel="stylesheet" href="assets/css/property-detail.css">
    <link rel="stylesheet" href="assets/css/custom-loader.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/custom-loader.js" defer></script>
</head>
<body>
    <header class="master_header">
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
                        <li><a href="index.php">Home</a></li>
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
                    <li><a href="index.php">Home</a></li>
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

    <section class="property-content">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-12 breadcrumb_container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="properties.php">Properties</a>, <a href="#"><?php echo htmlspecialchars($property['property_type']); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['property_name']); ?></li>
                    </ol>
                </div>

                <div class="notice_area col-md-12">
                    <div class="single_property_labels">
                        <div class="property_title_label">
                            <a href="properties.php?type=<?php echo $property['property_type'] === 'rental' ? 'rent' : 'sale'; ?>">
                                <?php echo $property['property_type'] === 'rental' ? 'Rental' : 'For Sale'; ?>
                            </a>
                        </div>
                        <?php if (!empty($property['property_category'])): ?>
                            <div class="property_title_label actioncat">
                                <a href="#"><?php echo htmlspecialchars($property['property_category']); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h1 class="entry-title entry-prop"><?php echo htmlspecialchars($property['property_name']); ?></h1>

                    <div class="property_categs">
                        <i class="fas fa-map-marker-alt"></i>
                        <a href="#"><?php echo htmlspecialchars($property['location']); ?></a>
                    </div>
                    <div class="prop_social">
                        <div class="share_unit" style="display: none;">
                            <a href="#" class="social_facebook">Facebook</a>
                            <a href="#" class="social_tweet">Twitter</a>
                            <a href="#" class="social_pinterest">Pinterest</a>
                            <a href="#" class="social_whatsup">WhatsApp</a>
                            <a href="#" class="social_email">Email</a>
                        </div>
                        <div class="title_share share_list single_property_action">
                            <i class="fas fa-share-alt"></i> Share
                        </div>
                        <div class="title_share single_property_action isnotfavorite">
                            <i class="far fa-heart"></i> Favorite
                        </div>
                        <div class="title_share single_property_action">
                            <i class="fas fa-print"></i> Print
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="gallery_wrapper">
                        <!-- Main Gallery Image -->
                        <div class="main-gallery-image" style="background-image:url('<?php echo htmlspecialchars($images[0] ?? 'uploads/contact.jpeg'); ?>')">
                            <div class="img_listings_overlay"></div>
                        </div>

                        <!-- Side Images (2 images) -->
                        <div class="side-images">
                            <?php if (count($images) > 1): ?>
                                <div class="gallery-item" style="background-image:url('<?php echo htmlspecialchars($images[1]); ?>')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-item" style="background-image:url('uploads/contact.jpeg')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php endif; ?>

                            <?php if (count($images) > 2): ?>
                                <div class="gallery-item" style="background-image:url('<?php echo htmlspecialchars($images[2]); ?>')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-item" style="background-image:url('uploads/contact.jpeg')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Bottom Images (3 images) -->
                        <div class="bottom-images">
                            <?php if (count($images) > 3): ?>
                                <div class="gallery-item" style="background-image:url('<?php echo htmlspecialchars($images[3]); ?>')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-item" style="background-image:url('uploads/contact.jpeg')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php endif; ?>

                            <?php if (count($images) > 4): ?>
                                <div class="gallery-item" style="background-image:url('<?php echo htmlspecialchars($images[4]); ?>')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-item" style="background-image:url('uploads/contact.jpeg')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php endif; ?>

                            <?php if (count($images) > 5): ?>
                                <div class="gallery-item" style="background-image:url('<?php echo htmlspecialchars($images[5]); ?>')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-item" style="background-image:url('uploads/contact.jpeg')">
                                    <div class="img_listings_overlay"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="property-description card">
                        <h2>Description</h2>
                        <p><?php echo !empty($property['description']) ? nl2br(htmlspecialchars($property['description'])) : 'No description available.'; ?></p>
                    </div>
                    <?php if (!empty($property['amenities'])): ?>
                            <div class="property-amenities card">
                                <div class="card-header">
                                    <h2>Amenities</h2>
                                </div>
                                <div class="card-body">
                                    <div class="amenities-grid">
                                        <?php
                                        $amenities = is_array($property['amenities']) ? $property['amenities'] : explode(',', $property['amenities']);
                                        foreach ($amenities as $amenity):
                                            $amenity = trim($amenity);
                                            if (empty($amenity)) continue;
                                        ?>
                                            <div class="amenity-item">
                                                <i class="fas fa-check"></i>
                                                <span><?php echo htmlspecialchars($amenity); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property['utilities'])): ?>
                            <div class="property-utilities card">
                                <div class="card-header">
                                    <h2>Utilities</h2>
                                </div>
                                <div class="card-body">
                                    <div class="amenities-grid">
                                        <?php
                                        $utilities = is_array($property['utilities']) ? $property['utilities'] : explode(',', $property['utilities']);
                                        foreach ($utilities as $utility):
                                            $utility = trim($utility);
                                            if (empty($utility)) continue;
                                        ?>
                                            <div class="amenity-item">
                                                <i class="fas fa-check"></i>
                                                <span><?php echo htmlspecialchars($utility); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property['security'])): ?>
                            <div class="property-security card">
                                <div class="card-header">
                                    <h2>Security Features</h2>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($property['security'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property['location'])): ?>
                            <div class="property-location card">
                                <div class="card-header">
                                    <h2>Location</h2>
                                </div>
                                <div class="card-body">
                                    <div class="location-map">
                                        <iframe
                                            width="100%"
                                            height="400"
                                            frameborder="0"
                                            scrolling="no"
                                            marginheight="0"
                                            marginwidth="0"
                                            src="https://maps.google.com/maps?q=<?php echo urlencode($property['location']); ?>&output=embed">
                                        </iframe>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="property-sidebar">
                        <div class="widget contact-agent-widget">
                            <div class="agent-info">
                                <div class="agent-image">
                                    <img src="<?php echo htmlspecialchars($property['profile_picture']); ?>" alt="<?php echo htmlspecialchars($property['manager_name']); ?>">
                                </div>
                                <div class="agent-details">
                                    <h4><?php echo htmlspecialchars($property['manager_name']); ?></h4>
                                    <p>Professional Real Estate Agent</p>
                                    <div class="agent-stats">
                                        <div class="stat">
                                            <span class="number"><?php echo htmlspecialchars($property['experience']); ?>+</span>
                                            <span class="label">Properties</span>
                                        </div>
                                        <div class="stat">
                                            <span class="number"><?php echo htmlspecialchars($property['experience']); ?>+</span>
                                            <span class="label">Years Exp.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="contact-form">
                                <form>
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Your Name">
                                    </div>
                                    <div class="form-group">
                                        <input type="email" class="form-control" placeholder="Your Email">
                                    </div>
                                    <div class="form-group">
                                        <input type="tel" class="form-control" placeholder="Your Phone">
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" rows="4" placeholder="Your Message">I am interested in <?php echo htmlspecialchars($property['title']); ?>. Please provide more information.</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                                </form>
                            </div>
                            <div class="quick-contacts">
                                <a href="tel:+256764502805" class="quick-contact-btn phone">
                                    <i class="fas fa-phone"></i>
                                    <span>Call Agent</span>
                                </a>
                                <a href="https://wa.me/256764502805" class="quick-contact-btn whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>

                        <div class="widget property-details-widget">
                            <h3>Property Details</h3>
                            <ul class="details-list">
                                <li>
                                    <span class="label">Property ID:</span>
                                    <span class="value"><?php echo $property['id']; ?></span>
                                </li>
                                <li>
                                    <span class="label">Property Type:</span>
                                    <span class="value"><?php echo ucfirst($property['property_type']); ?></span>
                                </li>
                                <li>
                                    <span class="label">Property Status:</span>
                                    <span class="value"><?php echo ucfirst($property['status']); ?></span>
                                </li>
                                <li>
                                    <span class="label">Property Size:</span>
                                    <span class="value"><?php echo $property['size']; ?> sq ft</span>
                                </li>
                                <li>
                                    <span class="label">Bedrooms:</span>
                                    <span class="value"><?php echo $property['bedrooms']; ?></span>
                                </li>
                                <li>
                                    <span class="label">Bathrooms:</span>
                                    <span class="value"><?php echo $property['bathrooms']; ?></span>
                                </li>
                                <li>
                                    <span class="label">Garage:</span>
                                    <span class="value"><?php echo $property['garage'] ? 'Yes' : 'No'; ?></span>
                                </li>
                                <li>
                                    <span class="label">Year Built:</span>
                                    <span class="value"><?php echo $property['year_built'] ?: 'N/A'; ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

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
                    <a href="https://fb.me/a6fCZzsAQ"><i class="fab fa-facebook-f"></i></a>
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
                    <li><i class="fas fa-phone"></i> +256 764502805 </li>
                    <li><i class="fas fa-envelope"></i> info@eastafricanlandandrentals.net</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> East African Land and Rentals. All rights reserved.</p>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/250123456789" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/properties-php.js"></script>
    <script src="assets/js/property-gallery.js"></script>
    <script src="assets/js/loader.js"></script>
    <script src="assets/js/mobile-menu-fix.js"></script>
</body>
</html>
