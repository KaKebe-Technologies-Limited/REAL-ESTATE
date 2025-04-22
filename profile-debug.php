<?php
session_start();
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Profile Debug Information</h1>
    
    <div class="section">
        <h2>Session Data</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="section">
        <h2>Database Connection Test</h2>
        <?php
        require_once 'config.php';
        try {
            $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if ($conn->connect_error) {
                echo '<p class="error">Connection failed: ' . $conn->connect_error . '</p>';
            } else {
                echo '<p class="success">Database connection successful</p>';
                
                // Check if user is logged in
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $user_type = $_SESSION['user_type'] ?? 'unknown';
                    
                    echo "<p>User ID: $user_id, User Type: $user_type</p>";
                    
                    // Try to fetch manager data
                    if ($user_type === 'manager') {
                        $query = "SELECT * FROM property_manager WHERE manager_id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('i', $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $manager_data = $result->fetch_assoc();
                            echo '<p class="success">Manager data found:</p>';
                            echo '<pre>';
                            print_r($manager_data);
                            echo '</pre>';
                        } else {
                            echo '<p class="error">No manager data found for ID: ' . $user_id . '</p>';
                        }
                        
                        $stmt->close();
                    } else {
                        echo '<p class="error">User is not a manager</p>';
                    }
                } else {
                    echo '<p class="error">No user is logged in</p>';
                }
                
                $conn->close();
            }
        } catch (Exception $e) {
            echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>File Existence Check</h2>
        <?php
        $files_to_check = [
            'get_manager_profile.php',
            'update_manager_profile.php',
            'assets/js/profile.js',
            'assets/js/manager-dashboard-new.js',
            'assets/js/profile-common.js'
        ];
        
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                echo "<p class='success'>$file exists</p>";
            } else {
                echo "<p class='error'>$file does not exist</p>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Test Profile API Directly</h2>
        <div id="api-test-result"></div>
        <script>
            // Test the get_manager_profile.php endpoint directly
            fetch('get_manager_profile.php')
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        document.getElementById('api-test-result').innerHTML = 
                            '<p>API Response:</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                    } catch (e) {
                        document.getElementById('api-test-result').innerHTML = 
                            '<p class="error">Error parsing JSON:</p><pre>' + text + '</pre>';
                    }
                })
                .catch(error => {
                    document.getElementById('api-test-result').innerHTML = 
                        '<p class="error">Fetch error: ' + error.message + '</p>';
                });
        </script>
    </div>
    
    <div class="section">
        <h2>JavaScript Console Logs</h2>
        <p>Please open your browser's developer console (F12) and check for any errors.</p>
        <p>Common issues:</p>
        <ul>
            <li>JavaScript errors preventing functions from executing</li>
            <li>Network errors when fetching profile data</li>
            <li>Incorrect paths or endpoints</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>Manual Profile Display Test</h2>
        <button id="test-show-profile">Test Show Profile</button>
        <script>
            document.getElementById('test-show-profile').addEventListener('click', function() {
                if (typeof showProfile === 'function') {
                    showProfile();
                    this.textContent = 'showProfile() function called';
                } else {
                    this.textContent = 'ERROR: showProfile() function not found';
                    this.style.color = 'red';
                }
            });
        </script>
    </div>
</body>
</html>
