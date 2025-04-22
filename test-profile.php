<?php
session_start();
header('Content-Type: text/html');

// Ensure we have a manager session for testing
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    echo "<p>No manager session found. This test requires you to be logged in as a manager.</p>";
    echo "<p><a href='login.html'>Go to login page</a></p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .test-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .result { margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; min-height: 100px; }
        pre { margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Manager Profile Test</h1>
        
        <div class="test-section">
            <h3>1. Test Get Profile API</h3>
            <button id="test-get-profile" class="btn btn-primary">Test Get Profile</button>
            <div id="get-profile-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>2. Test Update Profile API</h3>
            <form id="test-update-form">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
            <div id="update-profile-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>3. Session Data</h3>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
        
        <div class="mt-4">
            <a href="managerDashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test Get Profile
            document.getElementById('test-get-profile').addEventListener('click', function() {
                fetch('get_manager_profile.php')
                    .then(response => response.text())
                    .then(text => {
                        document.getElementById('get-profile-result').innerHTML = '<pre>' + text + '</pre>';
                        
                        try {
                            const data = JSON.parse(text);
                            if (data.success && data.user) {
                                // Populate the form with the user data
                                document.getElementById('first_name').value = data.user.first_name || '';
                                document.getElementById('last_name').value = data.user.last_name || '';
                                document.getElementById('email').value = data.user.email || '';
                                document.getElementById('phone').value = data.user.phone || '';
                                document.getElementById('username').value = data.user.username || '';
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                        }
                    })
                    .catch(error => {
                        document.getElementById('get-profile-result').innerHTML = 
                            '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    });
            });
            
            // Test Update Profile
            document.getElementById('test-update-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('update_manager_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    document.getElementById('update-profile-result').innerHTML = '<pre>' + text + '</pre>';
                })
                .catch(error => {
                    document.getElementById('update-profile-result').innerHTML = 
                        '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                });
            });
            
            // Automatically trigger the get profile test on page load
            document.getElementById('test-get-profile').click();
        });
    </script>
</body>
</html>
