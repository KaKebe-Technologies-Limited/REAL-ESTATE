<?php
session_start();
require_once 'config.php';
require_once 'validate_subscription.php';
header('Content-Type: application/json'); 

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get action from request
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_subscriptions':
        // Get all owner subscriptions
        getOwnerSubscriptions($conn);
        break;
        
    case 'extend_subscription':
        // Extend an owner's subscription
        extendSubscription($conn);
        break;
        
    case 'get_subscription_history':
        // Get subscription payment history for an owner
        getSubscriptionHistory($conn);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();

/**
 * Get all owner subscriptions
 * 
 * @param mysqli $conn Database connection
 */
function getOwnerSubscriptions($conn) {
    // Get pagination parameters
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
    $offset = ($page - 1) * $limit;
    
    // Get search parameter
    $search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';
    
    // Build search condition
    $search_condition = '';
    if (!empty($search)) {
        $search_condition = " AND (
            o.first_name LIKE '%$search%' OR 
            o.last_name LIKE '%$search%' OR 
            o.email LIKE '%$search%' OR 
            o.username LIKE '%$search%'
        )";
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM property_owner o WHERE 1=1 $search_condition";
    $count_result = $conn->query($count_query);
    $total = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total / $limit);
    
    // Get subscriptions with pagination
    $query = "SELECT 
                o.owner_id,
                o.username,
                o.first_name,
                o.last_name,
                o.email,
                o.subscription_status,
                o.subscription_start_date,
                o.subscription_end_date,
                o.last_renewal_date,
                (SELECT COUNT(*) FROM rental_property WHERE owner_id = o.owner_id) as rental_count,
                (SELECT COUNT(*) FROM sales_property WHERE owner_id = o.owner_id) as sales_count
              FROM property_owner o
              WHERE 1=1 $search_condition
              ORDER BY 
                CASE 
                    WHEN o.subscription_status = 'expired' THEN 1
                    WHEN o.subscription_status = 'pending' THEN 2
                    ELSE 3
                END,
                o.subscription_end_date ASC
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subscriptions = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates
        $row['subscription_start_date'] = $row['subscription_start_date'] ? date('Y-m-d', strtotime($row['subscription_start_date'])) : null;
        $row['subscription_end_date'] = $row['subscription_end_date'] ? date('Y-m-d', strtotime($row['subscription_end_date'])) : null;
        $row['last_renewal_date'] = $row['last_renewal_date'] ? date('Y-m-d', strtotime($row['last_renewal_date'])) : null;
        
        // Calculate days remaining
        $row['days_remaining'] = null;
        if ($row['subscription_end_date']) {
            $end_date = new DateTime($row['subscription_end_date']);
            $today = new DateTime();
            $interval = $today->diff($end_date);
            $row['days_remaining'] = $interval->invert ? -$interval->days : $interval->days;
        }
        
        $subscriptions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'subscriptions' => $subscriptions,
        'pagination' => [
            'total' => $total,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'limit' => $limit
        ]
    ]);
}

/**
 * Extend an owner's subscription
 * 
 * @param mysqli $conn Database connection
 */
function extendSubscription($conn) {
    // Get parameters
    $owner_id = isset($_POST['owner_id']) ? intval($_POST['owner_id']) : 0;
    $months = isset($_POST['months']) ? intval($_POST['months']) : 0;
    $notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';
    
    // Validate parameters
    if ($owner_id <= 0 || $months <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    // Extend subscription
    $result = renewSubscription($owner_id, $months, 'admin_extension', 'Admin Extension');
    
    if ($result) {
        // Add admin note if provided
        if (!empty($notes)) {
            $note_query = "INSERT INTO owner_subscription_notes (owner_id, admin_id, note, created_at) 
                          VALUES (?, ?, ?, NOW())";
            $note_stmt = $conn->prepare($note_query);
            $admin_id = $_SESSION['user_id'];
            $note_stmt->bind_param('iis', $owner_id, $admin_id, $notes);
            $note_stmt->execute();
            $note_stmt->close();
        }
        
        echo json_encode(['success' => true, 'message' => "Subscription extended by $months months"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to extend subscription']);
    }
}

/**
 * Get subscription payment history for an owner
 * 
 * @param mysqli $conn Database connection
 */
function getSubscriptionHistory($conn) {
    // Get owner ID
    $owner_id = isset($_POST['owner_id']) ? intval($_POST['owner_id']) : 0;
    
    // Validate owner ID
    if ($owner_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid owner ID']);
        return;
    }
    
    // Get subscription history
    $query = "SELECT 
                s.*,
                DATE_FORMAT(s.payment_date, '%Y-%m-%d') as formatted_payment_date,
                DATE_FORMAT(s.subscription_start_date, '%Y-%m-%d') as formatted_start_date,
                DATE_FORMAT(s.subscription_end_date, '%Y-%m-%d') as formatted_end_date
              FROM owner_subscriptions s
              WHERE s.owner_id = ?
              ORDER BY s.payment_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    // Get owner details
    $owner_query = "SELECT 
                      username, 
                      CONCAT(first_name, ' ', last_name) as full_name,
                      email,
                      subscription_status,
                      DATE_FORMAT(subscription_end_date, '%Y-%m-%d') as formatted_end_date
                    FROM property_owner
                    WHERE owner_id = ?";
    
    $owner_stmt = $conn->prepare($owner_query);
    $owner_stmt->bind_param('i', $owner_id);
    $owner_stmt->execute();
    $owner_result = $owner_stmt->get_result();
    $owner = $owner_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'owner' => $owner
    ]);
}
?>
