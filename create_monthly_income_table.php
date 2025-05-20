<?php
// Include database configuration
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create monthly_income table
$sql = "CREATE TABLE IF NOT EXISTS monthly_income (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    month VARCHAR(10) NOT NULL,
    year INT(4) NOT NULL,
    rental_income DECIMAL(15,2) NOT NULL DEFAULT 0,
    sales_income DECIMAL(15,2) NOT NULL DEFAULT 0,
    subscription_income DECIMAL(15,2) NOT NULL DEFAULT 0,
    owner_id INT(11) NULL,
    manager_id INT(11) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY month_year_owner_manager (month, year, owner_id, manager_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table monthly_income created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert some sample data for the current month and previous months
$currentMonth = date('M');
$currentYear = date('Y');

// Function to get previous month and year
function getPreviousMonthYear($month, $year) {
    $date = DateTime::createFromFormat('M Y', "$month $year");
    $date->modify('-1 month');
    return [
        'month' => $date->format('M'),
        'year' => $date->format('Y')
    ];
}

// Sample data for admin (no owner_id or manager_id)
$months = [];
$currentMonthYear = ['month' => $currentMonth, 'year' => $currentYear];

// Generate data for the last 6 months
for ($i = 0; $i < 6; $i++) {
    $months[] = $currentMonthYear;
    $currentMonthYear = getPreviousMonthYear($currentMonthYear['month'], $currentMonthYear['year']);
}

// Insert sample data for admin view (overall income)
foreach ($months as $monthData) {
    $month = $monthData['month'];
    $year = $monthData['year'];
    
    // Random income values
    $rentalIncome = rand(10000, 50000);
    $salesIncome = rand(50000, 200000);
    $subscriptionIncome = rand(5000, 20000);
    
    // Check if entry already exists
    $checkSql = "SELECT id FROM monthly_income WHERE month = ? AND year = ? AND owner_id IS NULL AND manager_id IS NULL";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $month, $year);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert new record
        $insertSql = "INSERT INTO monthly_income (month, year, rental_income, sales_income, subscription_income) 
                      VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("siddd", $month, $year, $rentalIncome, $salesIncome, $subscriptionIncome);
        
        if ($insertStmt->execute()) {
            echo "Sample data inserted for $month $year<br>";
        } else {
            echo "Error inserting data: " . $insertStmt->error . "<br>";
        }
        $insertStmt->close();
    } else {
        echo "Data already exists for $month $year<br>";
    }
    $checkStmt->close();
}

// Get all owners
$ownersSql = "SELECT owner_id FROM property_owner LIMIT 5";
$ownersResult = $conn->query($ownersSql);

if ($ownersResult->num_rows > 0) {
    while($owner = $ownersResult->fetch_assoc()) {
        $ownerId = $owner['owner_id'];
        
        // Insert sample data for each owner for the current month
        $month = date('M');
        $year = date('Y');
        
        // Random income values (smaller than admin totals)
        $rentalIncome = rand(1000, 10000);
        $salesIncome = rand(5000, 20000);
        
        // Check if entry already exists
        $checkSql = "SELECT id FROM monthly_income WHERE month = ? AND year = ? AND owner_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("sii", $month, $year, $ownerId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            // Insert new record
            $insertSql = "INSERT INTO monthly_income (month, year, rental_income, sales_income, owner_id) 
                          VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("siddi", $month, $year, $rentalIncome, $salesIncome, $ownerId);
            
            if ($insertStmt->execute()) {
                echo "Sample data inserted for owner ID $ownerId for $month $year<br>";
            } else {
                echo "Error inserting data: " . $insertStmt->error . "<br>";
            }
            $insertStmt->close();
        } else {
            echo "Data already exists for owner ID $ownerId for $month $year<br>";
        }
        $checkStmt->close();
    }
}

// Get all managers
$managersSql = "SELECT manager_id FROM property_manager LIMIT 5";
$managersResult = $conn->query($managersSql);

if ($managersResult->num_rows > 0) {
    while($manager = $managersResult->fetch_assoc()) {
        $managerId = $manager['manager_id'];
        
        // Insert sample data for each manager for the current month
        $month = date('M');
        $year = date('Y');
        
        // Random income values (smaller than admin totals)
        $rentalIncome = rand(1000, 10000);
        $salesIncome = rand(5000, 20000);
        
        // Check if entry already exists
        $checkSql = "SELECT id FROM monthly_income WHERE month = ? AND year = ? AND manager_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("sii", $month, $year, $managerId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            // Insert new record
            $insertSql = "INSERT INTO monthly_income (month, year, rental_income, sales_income, manager_id) 
                          VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("siddi", $month, $year, $rentalIncome, $salesIncome, $managerId);
            
            if ($insertStmt->execute()) {
                echo "Sample data inserted for manager ID $managerId for $month $year<br>";
            } else {
                echo "Error inserting data: " . $insertStmt->error . "<br>";
            }
            $insertStmt->close();
        } else {
            echo "Data already exists for manager ID $managerId for $month $year<br>";
        }
        $checkStmt->close();
    }
}

$conn->close();
echo "Done!";
?>
