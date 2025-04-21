<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Get some rental properties
$rental_sql = "SELECT property_id FROM rental_property LIMIT 5";
$rental_result = $conn->query($rental_sql);

$rental_ids = [];
if ($rental_result->num_rows > 0) {
    while ($row = $rental_result->fetch_assoc()) {
        $rental_ids[] = $row['property_id'];
    }
}

// Get some sales properties
$sale_sql = "SELECT property_id FROM sales_property LIMIT 5";
$sale_result = $conn->query($sale_sql);

$sale_ids = [];
if ($sale_result->num_rows > 0) {
    while ($row = $sale_result->fetch_assoc()) {
        $sale_ids[] = $row['property_id'];
    }
}

// Sample user data
$users = [
    ['name' => 'John Doe', 'email' => 'john@example.com'],
    ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
    ['name' => 'Robert Johnson', 'email' => 'robert@example.com'],
    ['name' => 'Sarah Williams', 'email' => 'sarah@example.com'],
    ['name' => 'Michael Brown', 'email' => 'michael@example.com']
];

// Sample review texts
$reviews = [
    "Great property! Very spacious and well-maintained.",
    "Excellent location and amenities. Highly recommended!",
    "Good value for money. The property is as described.",
    "Nice property but a bit overpriced for what it offers.",
    "Average property. Nothing special but does the job.",
    "Beautiful property with great views. Would definitely recommend!",
    "The property is in a convenient location but needs some maintenance.",
    "Fantastic property! Everything was perfect.",
    "Decent property but the neighborhood is a bit noisy.",
    "The property exceeded my expectations. Very satisfied!"
];

// Add sample ratings for rental properties
$count = 0;
foreach ($rental_ids as $property_id) {
    // Add 3-5 ratings per property
    $num_ratings = rand(3, 5);
    
    for ($i = 0; $i < $num_ratings; $i++) {
        $user = $users[array_rand($users)];
        $rating = rand(3, 5); // Mostly positive ratings
        $review_text = $reviews[array_rand($reviews)];
        
        $sql = "INSERT INTO property_ratings (property_id, property_type, user_name, user_email, rating, review_text) 
                VALUES (?, 'rental', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issis", $property_id, $user['name'], $user['email'], $rating, $review_text);
        
        if ($stmt->execute()) {
            $count++;
        } else {
            echo "Error adding rental rating: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    }
}

// Add sample ratings for sale properties
foreach ($sale_ids as $property_id) {
    // Add 2-4 ratings per property
    $num_ratings = rand(2, 4);
    
    for ($i = 0; $i < $num_ratings; $i++) {
        $user = $users[array_rand($users)];
        $rating = rand(3, 5); // Mostly positive ratings
        $review_text = $reviews[array_rand($reviews)];
        
        $sql = "INSERT INTO property_ratings (property_id, property_type, user_name, user_email, rating, review_text) 
                VALUES (?, 'sale', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issis", $property_id, $user['name'], $user['email'], $rating, $review_text);
        
        if ($stmt->execute()) {
            $count++;
        } else {
            echo "Error adding sale rating: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    }
}

echo "Added $count sample ratings successfully!";

$conn->close();
?>
