<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Check if property ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: properties.php');
    exit;
}

$property_id = intval($_GET['id']);

// Fetch property details
$sql = "SELECT p.*, 
        COALESCE(o.name, '') as owner_name, 
        COALESCE(o.phone, '') as owner_phone,
        COALESCE(o.email, '') as owner_email,
        COALESCE(m.name, '') as manager_name,
        COALESCE(m.phone, '') as manager_phone,
        COALESCE(m.email, '') as manager_email,
        COALESCE(c.name, '') as city_name,
        COALESCE(a.name, '') as area_name
        FROM properties p
        LEFT JOIN owners o ON p.owner_id = o.id
        LEFT JOIN managers m ON p.manager_id = m.id
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN areas a ON p.area_id = a.id
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: properties.php');
    exit;
}

$property = $result->fetch_assoc();

// Fetch property images
$imagesSql = "SELECT image_path FROM property_images WHERE property_id = ?";
$imagesStmt = $conn->prepare($imagesSql);
$imagesStmt->bind_param("i", $property_id);
$imagesStmt->execute();
$imagesResult = $imagesStmt->get_result();

$images = [];
while ($imageRow = $imagesResult->fetch_assoc()) {
    $images[] = $imageRow['image_path'];
}

// If no images, add a placeholder
if (empty($images)) {
    $images[] = 'assets/images/property-placeholder.jpg';
}

// Format price function
function formatPrice($price) {
    return number_format($price);
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - East African Land and Rentals</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/properties.css">
    <link rel="stylesheet" href="assets/css/property-detail.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header class="master_header">
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
        <!-- Property Hero Section -->
        <section class="property-hero">
            <div class="container">
                <div class="property-gallery">
                    <div class="main-image">
                        <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                        <div class="property-badges">
                            <?php if ($property['is_featured']): ?>
                                <span class="badge badge-featured">Featured</span>
                            <?php endif; ?>
                            <span class="badge badge-sale"><?php echo $property['property_type'] === 'rental' ? 'Rental' : 'Sale'; ?></span>
                        </div>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-grid">
                            <?php foreach (array_slice($images, 0, 4) as $index => $image): ?>
                                <div class="thumbnail">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Property Image <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($images) > 4): ?>
                                <div class="thumbnail more-images">
                                    <div class="overlay">
                                        <span>+<?php echo count($images) - 4; ?> more</span>
                                    </div>
                                    <img src="<?php echo htmlspecialchars($images[4]); ?>" alt="More Images">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Property Details Section -->
        <section class="property-content">
            <div class="container">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <div class="property-header">
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
                            
                            <h1 class="entry-title entry-prop"><?php echo htmlspecialchars($property['title']); ?></h1>
                            
                            <div class="property_categs">
                                <?php if (!empty($property['area_name'])): ?>
                                    <a href="#"><?php echo htmlspecialchars($property['area_name']); ?></a>,
                                <?php endif; ?>
                                <?php if (!empty($property['city_name'])): ?>
                                    <a href="#"><?php echo htmlspecialchars($property['city_name']); ?></a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="price_area">
                                <div class="price_label">Price</div>
                                <div class="price_label price_details">
                                    Ugx <?php echo formatPrice($property['price']); ?>
                                    <?php if ($property['property_type'] === 'rental'): ?>
                                        <span class="price_label">/ month</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="property-description card">
                            <div class="card-header">
                                <h2>Description</h2>
                            </div>
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                            </div>
                        </div>
                        
                        <div class="property-details card">
                            <div class="card-header">
                                <h2>Property Details</h2>
                            </div>
                            <div class="card-body">
                                <div class="details-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Property ID:</span>
                                        <span class="detail-value"><?php echo $property['id']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Property Type:</span>
                                        <span class="detail-value"><?php echo ucfirst($property['property_type']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Property Status:</span>
                                        <span class="detail-value"><?php echo $property['status']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Property Size:</span>
                                        <span class="detail-value"><?php echo $property['size']; ?> sqm</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Bedrooms:</span>
                                        <span class="detail-value"><?php echo $property['bedrooms']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Bathrooms:</span>
                                        <span class="detail-value"><?php echo $property['bathrooms']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Year Built:</span>
                                        <span class="detail-value"><?php echo $property['year_built'] ?: 'N/A'; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Garage:</span>
                                        <span class="detail-value"><?php echo $property['garage'] ? 'Yes' : 'No'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($property['amenities'])): ?>
                            <div class="property-amenities card">
                                <div class="card-header">
                                    <h2>Amenities</h2>
                                </div>
                                <div class="card-body">
                                    <div class="amenities-grid">
                                        <?php 
                                        $amenities = explode(',', $property['amenities']);
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
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="agent-info card">
                            <div class="card-header">
                                <h2>Contact Information</h2>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($property['manager_name'])): ?>
                                    <div class="agent-details">
                                        <h3>Property Manager</h3>
                                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($property['manager_name']); ?></p>
                                        <?php if (!empty($property['manager_phone'])): ?>
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($property['manager_phone']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($property['manager_email'])): ?>
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($property['manager_email']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($property['owner_name'])): ?>
                                    <div class="agent-details mt-4">
                                        <h3>Property Owner</h3>
                                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($property['owner_name']); ?></p>
                                        <?php if (!empty($property['owner_phone'])): ?>
                                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($property['owner_phone']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($property['owner_email'])): ?>
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($property['owner_email']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="contact-form card">
                            <div class="card-header">
                                <h2>Inquire About This Property</h2>
                            </div>
                            <div class="card-body">
                                <form id="property-inquiry-form" action="#" method="POST">
                                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                    <input type="hidden" name="property_title" value="<?php echo htmlspecialchars($property['title']); ?>">
                                    
                                    <div class="form-group">
                                        <label for="name">Your Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Your Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Your Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="message">Your Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" required>I am interested in <?php echo htmlspecialchars($property['title']); ?>. Please provide more information.</textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                                </form>
                            </div>
                        </div>
                    </div>
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
