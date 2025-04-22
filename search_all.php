<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'config.php';

// Log the search request for debugging
error_log('Search request: ' . print_r($_GET, true));
error_log('Session data: ' . print_r($_SESSION, true));

function searchDatabase($type, $search, $page = 1, $limit = 10) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    $offset = ($page - 1) * $limit;
    $searchTerm = "%$search%";

    switch ($type) {
        case 'rentals':
            // Check if user is an owner and restrict to their properties
            $userCondition = '';
            $userParams = [];
            $userTypes = '';

            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner' && isset($_SESSION['user_id'])) {
                $userCondition = ' AND r.owner_id = ?';
                $userParams[] = $_SESSION['user_id'];
                $userTypes = 'i';
            } else if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'manager' && isset($_SESSION['user_id'])) {
                $userCondition = ' AND r.manager_id = ?';
                $userParams[] = $_SESSION['user_id'];
                $userTypes = 'i';
            }

            $query = "SELECT
                r.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(r.parish, ', ', r.ward) as location,
                r.price as rent,
                status
            FROM rental_property r
            LEFT JOIN property_owner o ON r.owner_id = o.owner_id
            LEFT JOIN property_manager m ON r.manager_id = m.manager_id
            WHERE
                (r.property_name LIKE ? OR
                CONCAT(o.first_name, ' ', o.last_name) LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ? OR
                r.parish LIKE ? OR
                r.ward LIKE ?)
                $userCondition
            ORDER BY r.property_name
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM rental_property r
            LEFT JOIN property_owner o ON r.owner_id = o.owner_id
            LEFT JOIN property_manager m ON r.manager_id = m.manager_id
            WHERE
                (r.property_name LIKE ? OR
                CONCAT(o.first_name, ' ', o.last_name) LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ? OR
                r.parish LIKE ? OR
                r.ward LIKE ?)
                $userCondition";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            // Combine parameters
            $queryParams = array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm], $userParams, [$limit, $offset]);
            $countParams = array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm], $userParams);

            // Create types string
            $queryTypes = 'sssss' . $userTypes . 'ii';
            $countTypes = 'sssss' . $userTypes;

            $stmt->bind_param($queryTypes, ...$queryParams);
            $countStmt->bind_param($countTypes, ...$countParams);
            break;

        case 'sales':
            // Check if user is an owner and restrict to their properties
            $userCondition = '';
            $userParams = [];
            $userTypes = '';

            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner' && isset($_SESSION['user_id'])) {
                $userCondition = ' AND s.owner_id = ?';
                $userParams[] = $_SESSION['user_id'];
                $userTypes = 'i';
            } else if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'manager' && isset($_SESSION['user_id'])) {
                $userCondition = ' AND s.manager_id = ?';
                $userParams[] = $_SESSION['user_id'];
                $userTypes = 'i';
            }

            $query = "SELECT
                s.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(s.parish, ', ', s.ward) as location,
                status
            FROM sales_property s
            LEFT JOIN property_owner o ON s.owner_id = o.owner_id
            LEFT JOIN property_manager m ON s.manager_id = m.manager_id
            WHERE
                (s.property_name LIKE ? OR
                CONCAT(o.first_name, ' ', o.last_name) LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ? OR
                s.parish LIKE ? OR
                s.ward LIKE ?)
                $userCondition
            ORDER BY s.property_name
            LIMIT ? OFFSET ?";

            $countQuery = "SELECT COUNT(*) as total FROM sales_property s
            LEFT JOIN property_owner o ON s.owner_id = o.owner_id
            LEFT JOIN property_manager m ON s.manager_id = m.manager_id
            WHERE
                (s.property_name LIKE ? OR
                CONCAT(o.first_name, ' ', o.last_name) LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ? OR
                s.parish LIKE ? OR
                s.ward LIKE ?)
                $userCondition";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            // Combine parameters
            $queryParams = array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm], $userParams, [$limit, $offset]);
            $countParams = array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm], $userParams);

            // Create types string
            $queryTypes = 'sssss' . $userTypes . 'ii';
            $countTypes = 'sssss' . $userTypes;

            $stmt->bind_param($queryTypes, ...$queryParams);
            $countStmt->bind_param($countTypes, ...$countParams);
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
            // For owners, only show managers who manage their properties
            $userParams = [];
            $userTypes = '';
            $whereCondition = '';

            // Temporarily disable the owner filter for debugging
            /*
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner' && isset($_SESSION['user_id'])) {
                // Use a subquery instead of a join to avoid duplicate table aliases
                $whereCondition = ' AND m.manager_id IN (SELECT DISTINCT manager_id FROM rental_property WHERE owner_id = ?)';
                $userParams[] = $_SESSION['user_id'];
                $userTypes = 'i';
            }
            */

            // Log the search parameters for debugging
            error_log("Manager search: search term = '$search', user type = '{$_SESSION['user_type']}', user id = '{$_SESSION['user_id']}'");

            // Simplified query for managers
            $query = "SELECT
                m.*,
                IFNULL((SELECT COUNT(*) FROM rental_property rp WHERE rp.manager_id = m.manager_id), 0) +
                IFNULL((SELECT COUNT(*) FROM sales_property sp WHERE sp.manager_id = m.manager_id), 0) as property_count
            FROM property_manager m
            WHERE
                (m.username LIKE ? OR
                m.email LIKE ? OR
                m.phone LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ?)
                $whereCondition
            GROUP BY m.manager_id
            ORDER BY m.username
            LIMIT ? OFFSET ?";

            // Log the final query for debugging
            $debugQuery = str_replace('?', "'%$search%'", $query);
            error_log("Manager search query: $debugQuery");

            $countQuery = "SELECT COUNT(DISTINCT m.manager_id) as total
            FROM property_manager m
            WHERE
                (m.username LIKE ? OR
                m.email LIKE ? OR
                m.phone LIKE ? OR
                CONCAT(m.first_name, ' ', m.last_name) LIKE ?)
                $whereCondition
            ";

            $stmt = $conn->prepare($query);
            $countStmt = $conn->prepare($countQuery);

            // Combine parameters - now with 4 search terms (username, email, phone, full name)
            $queryParams = array_merge($userParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
            $countParams = array_merge($userParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

            // Create types string
            $queryTypes = $userTypes . 'ssssii';
            $countTypes = $userTypes . 'ssss';

            $stmt->bind_param($queryTypes, ...$queryParams);
            $countStmt->bind_param($countTypes, ...$countParams);
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

    error_log("Searching for '$search' in '$type', page $page, limit $limit");

    $result = searchDatabase($type, $search, $page, $limit);

    // Log the result for debugging
    error_log('Search result count: ' . count($result['items']));

    $response = [
        'success' => true,
        'data' => $result
    ];

    echo json_encode($response);
    error_log('Response sent: ' . json_encode($response));

} catch (Exception $e) {
    error_log('Search error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}