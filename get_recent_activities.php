<?php
require_once 'config.php'; // Include database configuration
function getRecentActivities($limit = 5) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        return [];
    }

    $query = "SELECT * FROM activities ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $activities;
}

function getTimeAgo($timestamp) {
    if (!$timestamp) {
        return 'invalid timestamp';
    }

    $time_ago = strtotime($timestamp);
    if ($time_ago === false) {
        return 'invalid date format';
    }

    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    // Handle future dates
    if ($time_difference < 0) {
        return 'just now';
    }

    $seconds = $time_difference;
    $minutes = floor($seconds / 60);
    $hours = floor($seconds / 3600);
    $days = floor($seconds / 86400);
    $weeks = floor($days / 7);
    $months = floor($days / 30.44);
    $years = floor($days / 365);

    if ($seconds < 60) {
        return "just now";
    } else if ($minutes < 60) {
        return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours < 24) {
        return $hours == 1 ? "1 hour ago" : "$hours hours ago";
    } else if ($days < 7) {
        return $days == 1 ? "1 day ago" : "$days days ago";
    } else if ($weeks < 4) {
        return $weeks == 1 ? "1 week ago" : "$weeks weeks ago";
    } else if ($months < 12) {
        return $months == 1 ? "1 month ago" : "$months months ago";
    } else {
        return $years == 1 ? "1 year ago" : "$years years ago";
    }
}
?>