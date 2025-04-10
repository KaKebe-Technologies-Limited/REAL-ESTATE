<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
require_once 'config.php';

function searchDatabase($type, $search, $page = 1, $limit = 10) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    $offset = ($page - 1) * $limit;
    $searchTerm = "%$search%";

    switch ($type) {
        case 'rentals':
            $query = "SELECT 
                r.*, 
                o.username as owner_name, 
                m.username as manager_name,
                CONCAT(r.region, ', ', r.country) as location
            FROM rental_property r
            LEFT JOIN property_owner o ON r.owner_id = o.owner_id
            LEFT JOIN property_manager m ON r.manager_id = m.manager_id
            WHERE 
                r.property_name LIKE ? OR
                o.username LIKE ? OR
                m.username LIKE ? OR
                r.region LIKE ? OR
                r.country LIKE ?
            ORDER BY r.property_name
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM rental_property r
            LEFT JOIN property_owner o ON r.owner_id = o.owner_id
            LEFT JOIN property_manager m ON r.manager_id = m.manager_id
            WHERE 
                r.property_name LIKE ? OR
                o.username LIKE ? OR
                m.username LIKE ? OR
                r.region LIKE ? OR
                r.country LIKE ?";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            $stmt->bind_param('sssssii', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $countStmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            break;

        case 'sales':
            $query = "SELECT 
                s.*, 
                o.username as owner_name, 
                m.username as manager_name,
                CONCAT(s.region, ', ', s.country) as location
            FROM sales_property s
            LEFT JOIN property_owner o ON s.owner_id = o.owner_id
            LEFT JOIN property_manager m ON s.manager_id = m.manager_id
            WHERE 
                s.property_name LIKE ? OR
                o.username LIKE ? OR
                m.username LIKE ? OR
                s.region LIKE ? OR
                s.country LIKE ?
            ORDER BY s.property_name
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM sales_property s
            LEFT JOIN property_owner o ON s.owner_id = o.owner_id
            LEFT JOIN property_manager m ON s.manager_id = m.manager_id
            WHERE 
                s.property_name LIKE ? OR
                o.username LIKE ? OR
                m.username LIKE ? OR
                s.region LIKE ? OR
                s.country LIKE ?"; 

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            $stmt->bind_param('sssssii', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $countStmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            break;

        case 'owners':
            $query = "SELECT 
                o.*,
                COUNT(DISTINCT r.property_id) + COUNT(DISTINCT s.property_id) as property_count
            FROM property_owner o
            LEFT JOIN rental_property r ON o.owner_id = r.owner_id
            LEFT JOIN sales_property s ON o.owner_id = s.owner_id
            WHERE 
                o.username LIKE ? OR
                o.email LIKE ? OR
                o.phone LIKE ?
            GROUP BY o.owner_id
            ORDER BY o.username
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM property_owner
            WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            $stmt->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $countStmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
            break;

        case 'managers':
            $query = "SELECT 
                m.*,
                COUNT(DISTINCT r.property_id) + COUNT(DISTINCT s.property_id) as property_count
            FROM property_manager m
            LEFT JOIN rental_property r ON m.manager_id = r.manager_id
            LEFT JOIN sales_property s ON m.manager_id = s.manager_id
            WHERE 
                m.username LIKE ? OR
                m.email LIKE ? OR
                m.phone LIKE ?
            GROUP BY m.manager_id
            ORDER BY m.username
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM property_manager
            WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            $stmt->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
            $countStmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
            break;

        default:
            throw new Exception('Invalid search type');
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);

    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];

    $conn->close();

    return [
        'items' => $items,
        'total' => $totalCount,
        'page' => $page,
        'total_pages' => ceil($totalCount / $limit)
    ];
}

try {
    $type = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    $result = searchDatabase($type, $search, $page, $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}