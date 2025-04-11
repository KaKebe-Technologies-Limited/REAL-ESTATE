<?php
// Only set Content-Type header when accessed directly or when JSON is requested
if (!defined('INCLUDED_IN_PROPERTIES') && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) {
    // Don't set Content-Type header when included in properties.php
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}

$conn->set_charset("utf8mb4");

function formatPropertyPrice($price) {
    // Check if price is numeric
    if (is_numeric($price)) {
        return number_format((float)$price);
    }
    // Return the original string if it's not numeric
    return $price;
}

function getProperties($conn, $type = null, $searchParams = []) {
    $properties = [];
    $whereConditions = [];
    $params = [];
    $types = '';

    // Process search parameters
    if (!empty($searchParams)) {
        // City filter
        if (!empty($searchParams['city']) && $searchParams['city'] !== 'all') {
            $whereConditions[] = 'country = ?';
            $params[] = $searchParams['city'];
            $types .= 's';
        }

        // Area filter
        if (!empty($searchParams['area']) && $searchParams['area'] !== 'all') {
            $whereConditions[] = 'parish = ?';
            $params[] = $searchParams['area'];
            $types .= 's';
        }

        // Property Size filter
        if (!empty($searchParams['property_size'])) {
            $whereConditions[] = 'property_size >= ?';
            $params[] = intval($searchParams['property_size']);
            $types .= 'i';
        }

        // Utilities filter
        if (!empty($searchParams['utilities'])) {
            $whereConditions[] = 'utilities LIKE ?';
            $params[] = '%' . $searchParams['utilities'] . '%';
            $types .= 's';
        }
    }

    if ($type === null || $type === 'rent') {
        // Fetch rental properties
        $rentalSql = "SELECT
            r.property_id,
            r.property_name,
            r.price,
            r.property_class as property_class,
            r.property_size,
            r.utilities,
            r.amenities,
            r.images,
            r.country,
            r.parish,
            CONCAT(r.parish, ', ', r.ward) as location,
            CONCAT(o.first_name, ' ', o.last_name) as owner_name,
            o.phone as owner_phone,
            o.email as owner_email,
            CONCAT(m.first_name, ' ', m.last_name) as manager_name,
            m.phone as manager_phone,
            m.email as manager_email,
            'rental' as property_type
            FROM rental_property r
            LEFT JOIN property_owner o ON r.owner_id = o.owner_id
            LEFT JOIN property_manager m ON r.manager_id = m.manager_id";

        // Add WHERE clause if we have conditions
        if (!empty($whereConditions)) {
            $rentalSql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $rentalSql .= " ORDER BY r.property_id DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($rentalSql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($rentalSql);
        }

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['formatted_price'] = formatPropertyPrice($row['price']);
                $row['utilities'] = $row['utilities'] ? explode(',', $row['utilities']) : [];
                $row['amenities'] = $row['amenities'] ? explode(',', $row['amenities']) : [];

                // Process images
                if (!empty($row['images'])) {
                    $imageArray = explode(',', $row['images']);
                    $row['image'] = !empty($imageArray[0]) ? trim($imageArray[0]) : 'assets/images/property-placeholder.jpg';
                    $row['all_images'] = $imageArray;
                } else {
                    $row['image'] = 'assets/images/property-placeholder.jpg';
                    $row['all_images'] = [];
                }

                $properties[] = $row;
            }

            if (!empty($stmt)) {
                $stmt->close();
            }
        } else {
            error_log("Rental query error: {$conn->error}");
        }
    }

    if ($type === null || $type === 'sale') {
        // Fetch sales properties
        $saleSql = "SELECT
            s.property_id,
            s.property_name,
            s.price,
            s.property_type as property_class,
            s.property_size,
            s.utilities,
            s.amenities,
            s.images,
            s.country,
            s.parish,
            CONCAT(s.parish, ', ', s.ward) as location,
            CONCAT(o.first_name, ' ', o.last_name) as owner_name,
            o.phone as owner_phone,
            o.email as owner_email,
            CONCAT(m.first_name, ' ', m.last_name) as manager_name,
            m.phone as manager_phone,
            m.email as manager_email,
            'sale' as property_type
            FROM sales_property s
            LEFT JOIN property_owner o ON s.owner_id = o.owner_id
            LEFT JOIN property_manager m ON s.manager_id = m.manager_id";

        // Add WHERE clause if we have conditions
        if (!empty($whereConditions)) {
            $saleSql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $saleSql .= " ORDER BY s.property_id DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($saleSql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($saleSql);
        }

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['formatted_price'] = formatPropertyPrice($row['price']);
                $row['utilities'] = $row['utilities'] ? explode(',', $row['utilities']) : [];
                $row['amenities'] = $row['amenities'] ? explode(',', $row['amenities']) : [];

                // Process images
                if (!empty($row['images'])) {
                    $imageArray = explode(',', $row['images']);
                    $row['image'] = !empty($imageArray[0]) ? trim($imageArray[0]) : 'assets/images/property-placeholder.jpg';
                    $row['all_images'] = $imageArray;
                } else {
                    $row['image'] = 'assets/images/property-placeholder.jpg';
                    $row['all_images'] = [];
                }

                $properties[] = $row;
            }

            if (!empty($stmt)) {
                $stmt->close();
            }
        } else {
            error_log("Sale query error: {$conn->error}");
        }
    }

    return $properties;
}

// Get search parameters
$searchParams = [];

if (isset($_GET['city']) && !empty($_GET['city'])) {
    $searchParams['city'] = $_GET['city'];
}

if (isset($_GET['area']) && !empty($_GET['area'])) {
    $searchParams['area'] = $_GET['area'];
}

if (isset($_GET['property_size']) && !empty($_GET['property_size'])) {
    $searchParams['property_size'] = $_GET['property_size'];
}

if (isset($_GET['utilities']) && !empty($_GET['utilities'])) {
    $searchParams['utilities'] = $_GET['utilities'];
}

// Get property type filter
$propertyType = null;
if (isset($_GET['type']) && !empty($_GET['type'])) {
    if ($_GET['type'] === 'rent') {
        $propertyType = 'rent';
    } elseif ($_GET['type'] === 'sale') {
        $propertyType = 'sale';
    }
}

// Get all properties, rentals, and sales with search filters
$allProperties = getProperties($conn, null, $searchParams);
$rentalProperties = getProperties($conn, 'rent', $searchParams);
$saleProperties = getProperties($conn, 'sale', $searchParams);

// Close connection
$conn->close();

// Return JSON if requested via AJAX
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    // Set Content-Type header for JSON responses
    header('Content-Type: application/json');
    echo json_encode([
        'all' => $allProperties,
        'rent' => $rentalProperties,
        'sale' => $saleProperties
    ]);
    exit;
}