<?php
require_once 'get_recent_activities.php';

// Test function to verify getTimeAgo
function testGetTimeAgo() {
    // Current time for reference
    $now = time();
    
    // Test cases for past times
    $test_cases = [
        // Seconds
        date('Y-m-d H:i:s', $now - 10) => 'just now',
        date('Y-m-d H:i:s', $now - 59) => 'just now',
        
        // Minutes
        date('Y-m-d H:i:s', $now - 60) => '1 minute ago',
        date('Y-m-d H:i:s', $now - 120) => '2 minutes ago',
        date('Y-m-d H:i:s', $now - 3540) => '59 minutes ago',
        
        // Hours
        date('Y-m-d H:i:s', $now - 3600) => '1 hour ago',
        date('Y-m-d H:i:s', $now - 7200) => '2 hours ago',
        date('Y-m-d H:i:s', $now - 86340) => '23 hours ago',
        
        // Days
        date('Y-m-d H:i:s', $now - 86400) => '1 day ago',
        date('Y-m-d H:i:s', $now - 172800) => '2 days ago',
        date('Y-m-d H:i:s', $now - 518400) => '6 days ago',
        
        // Weeks
        date('Y-m-d H:i:s', $now - 604800) => '1 week ago',
        date('Y-m-d H:i:s', $now - 1209600) => '2 weeks ago',
        date('Y-m-d H:i:s', $now - 2419200) => '4 weeks ago',
        
        // Months
        date('Y-m-d H:i:s', $now - 2592000) => '1 month ago',
        date('Y-m-d H:i:s', $now - 5184000) => '2 months ago',
        date('Y-m-d H:i:s', $now - 31104000) => '11 months ago',
        
        // Years
        date('Y-m-d H:i:s', $now - 31536000) => '1 year ago',
        date('Y-m-d H:i:s', $now - 63072000) => '2 years ago',
    ];
    
    // Test cases for future times
    $future_test_cases = [
        // Seconds
        date('Y-m-d H:i:s', $now + 10) => 'in a few seconds',
        date('Y-m-d H:i:s', $now + 59) => 'in a few seconds',
        
        // Minutes
        date('Y-m-d H:i:s', $now + 60) => 'in 1 minute',
        date('Y-m-d H:i:s', $now + 120) => 'in 2 minutes',
        date('Y-m-d H:i:s', $now + 3540) => 'in 59 minutes',
        
        // Hours
        date('Y-m-d H:i:s', $now + 3600) => 'in 1 hour',
        date('Y-m-d H:i:s', $now + 7200) => 'in 2 hours',
        date('Y-m-d H:i:s', $now + 86340) => 'in 23 hours',
        
        // Days
        date('Y-m-d H:i:s', $now + 86400) => 'in 1 day',
        date('Y-m-d H:i:s', $now + 172800) => 'in 2 days',
        date('Y-m-d H:i:s', $now + 518400) => 'in 6 days',
        
        // Weeks
        date('Y-m-d H:i:s', $now + 604800) => 'in 1 week',
        date('Y-m-d H:i:s', $now + 1209600) => 'in 2 weeks',
        date('Y-m-d H:i:s', $now + 2419200) => 'in 4 weeks',
        
        // Months
        date('Y-m-d H:i:s', $now + 2592000) => 'in 1 month',
        date('Y-m-d H:i:s', $now + 5184000) => 'in 2 months',
        date('Y-m-d H:i:s', $now + 31104000) => 'in 11 months',
        
        // Years
        date('Y-m-d H:i:s', $now + 31536000) => 'in 1 year',
        date('Y-m-d H:i:s', $now + 63072000) => 'in 2 years',
    ];
    
    // Merge all test cases
    $all_test_cases = array_merge($test_cases, $future_test_cases);
    
    // Run tests
    echo "<h1>Testing getTimeAgo Function</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Timestamp</th><th>Expected Result</th><th>Actual Result</th><th>Status</th></tr>";
    
    $passed = 0;
    $failed = 0;
    
    foreach ($all_test_cases as $timestamp => $expected) {
        $result = getTimeAgo($timestamp);
        $status = ($result === $expected) ? 'PASS' : 'FAIL';
        
        if ($status === 'PASS') {
            $passed++;
            $row_color = "#d4edda"; // Light green
        } else {
            $failed++;
            $row_color = "#f8d7da"; // Light red
        }
        
        echo "<tr style='background-color: $row_color;'>";
        echo "<td>$timestamp</td>";
        echo "<td>$expected</td>";
        echo "<td>$result</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Summary
    echo "<h2>Test Summary</h2>";
    echo "<p>Total tests: " . count($all_test_cases) . "</p>";
    echo "<p>Passed: $passed</p>";
    echo "<p>Failed: $failed</p>";
    
    if ($failed === 0) {
        echo "<p style='color: green; font-weight: bold;'>All tests passed! The getTimeAgo function is working correctly.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Some tests failed. The getTimeAgo function needs adjustment.</p>";
    }
}

// Run the test
testGetTimeAgo();
