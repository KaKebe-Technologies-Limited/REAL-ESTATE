<?php
session_start();
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'ownerDashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Renewed - ALLEA</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0; 
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .success-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        .success-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .success-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .success-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }
        .redirect-message {
            font-size: 14px;
            color: #888;
            margin-top: 20px;
        }
        .btn-dashboard {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-dashboard:hover {
            background-color: #0069d9;
            color: #fff;
            text-decoration: none;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <img src="assets/images/logo1.png" alt="ALLEA Logo" class="logo">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="success-title">Subscription Renewed Successfully!</h1>
            <p class="success-message">Your account has been successfully renewed. You can now continue using all owner features.</p>
            <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn-dashboard">Go to Dashboard</a>
            <p class="redirect-message">You will be redirected automatically in <span id="countdown">5</span> seconds...</p>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Redirect to dashboard after 5 seconds
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const redirectUrl = "<?php echo htmlspecialchars($redirect_url); ?>";
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = redirectUrl;
            }
        }, 1000);
    </script>
</body>
</html>
