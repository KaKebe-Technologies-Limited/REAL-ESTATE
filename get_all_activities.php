<?php
require_once 'get_recent_activities.php';

$activities = getRecentActivities(50); // Adjust limit as needed
$activities_with_time = array_map(function($activity) {
    $activity['time_ago'] = getTimeAgo($activity['created_at']);
    return $activity;
}, $activities);

echo json_encode([
    'success' => true,
    'activities' => $activities_with_time
]);
?>