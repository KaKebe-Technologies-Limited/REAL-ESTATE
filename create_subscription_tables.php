<?php
require_once 'config.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if subscription fields exist in property_owner table
$check_fields_query = "SHOW COLUMNS FROM property_owner LIKE 'subscription_status'";
$result = $conn->query($check_fields_query);

if ($result->num_rows == 0) {
    // Add subscription fields to property_owner table
    $alter_table_query = "ALTER TABLE property_owner
        ADD COLUMN subscription_start_date TIMESTAMP NULL,
        ADD COLUMN subscription_end_date TIMESTAMP NULL,
        ADD COLUMN subscription_status ENUM('active', 'expired', 'pending') DEFAULT 'pending',
        ADD COLUMN last_renewal_date TIMESTAMP NULL";
    
    if ($conn->query($alter_table_query) === TRUE) {
        echo "Subscription fields added to property_owner table successfully<br>";
    } else {
        echo "Error adding subscription fields: " . $conn->error . "<br>";
    }
    
    // Update existing owners to have active subscriptions for 4 months
    $update_owners_query = "UPDATE property_owner 
        SET 
            subscription_start_date = NOW(),
            subscription_end_date = DATE_ADD(NOW(), INTERVAL 4 MONTH),
            subscription_status = 'active',
            last_renewal_date = NOW()
        WHERE payment_status = 'paid'";
    
    if ($conn->query($update_owners_query) === TRUE) {
        echo "Existing owners updated with active subscriptions<br>";
    } else {
        echo "Error updating existing owners: " . $conn->error . "<br>";
    }
} else {
    echo "Subscription fields already exist in property_owner table<br>";
}

// Check if owner_subscriptions table exists
$check_table_query = "SHOW TABLES LIKE 'owner_subscriptions'";
$result = $conn->query($check_table_query);

if ($result->num_rows == 0) {
    // Create owner_subscriptions table
    $create_table_query = "CREATE TABLE owner_subscriptions (
        subscription_id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'UGX',
        transaction_id VARCHAR(100),
        order_tracking_id VARCHAR(100),
        merchant_reference VARCHAR(100),
        payment_method VARCHAR(50),
        payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        subscription_months INT DEFAULT 4,
        subscription_start_date TIMESTAMP NULL,
        subscription_end_date TIMESTAMP NULL,
        FOREIGN KEY (owner_id) REFERENCES property_owner(owner_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table_query) === TRUE) {
        echo "owner_subscriptions table created successfully<br>";
    } else {
        echo "Error creating owner_subscriptions table: " . $conn->error . "<br>";
    }
} else {
    echo "owner_subscriptions table already exists<br>";
}

// Check if owner_subscription_notes table exists
$check_notes_table_query = "SHOW TABLES LIKE 'owner_subscription_notes'";
$result = $conn->query($check_notes_table_query);

if ($result->num_rows == 0) {
    // Create owner_subscription_notes table
    $create_notes_table_query = "CREATE TABLE owner_subscription_notes (
        note_id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        admin_id INT NOT NULL,
        note TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES property_owner(owner_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_notes_table_query) === TRUE) {
        echo "owner_subscription_notes table created successfully<br>";
    } else {
        echo "Error creating owner_subscription_notes table: " . $conn->error . "<br>";
    }
} else {
    echo "owner_subscription_notes table already exists<br>";
}

// Close connection
$conn->close();

echo "<br>All subscription tables and fields have been checked and created if needed.<br>";
echo "<a href='renew_subscription.php'>Go to Subscription Renewal Page</a>";
?>
