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

    // For past dates
    // Calculate time units
    $seconds = $time_difference;
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $minutes %= 60; // Get remaining minutes after calculating hours
    $days = floor($hours / 24);
    $hours %= 24; // Get remaining hours after calculating days
    $weeks = floor($days / 7);
    $days %= 7; // Get remaining days after calculating weeks
    $months = floor($days / 30.44);
    $years = floor($days / 365.25);

    // Return the most appropriate time format
    if ($seconds < 60) {
        return "just now";
    } else if ($minutes > 0 && $hours == 0) {
        return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours > 0 && $days == 0) {
        return $hours == 1 ? "1 hour ago" : "$hours hours ago";
    } else if ($days > 0 && $weeks == 0) {
        return $days == 1 ? "1 day ago" : "$days days ago";
    } else if ($weeks > 0 && $months < 1) {
        return $weeks == 1 ? "1 week ago" : "$weeks weeks ago";
    } else if ($months > 0 && $years < 1) {
        return $months == 1 ? "1 month ago" : "$months months ago";
    } else {
        return $years == 1 ? "1 year ago" : "$years years ago";
    }
}