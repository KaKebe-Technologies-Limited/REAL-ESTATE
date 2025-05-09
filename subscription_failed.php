<?php
session_start();
$status = isset($_GET['status']) ? $_GET['status'] : 'FAILED';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Failed - ALLEA</title>
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
        .failed-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        .failed-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        .failed-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .failed-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .failed-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }
        .status-message {
            font-size: 14px;
            color: #888;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .btn-retry {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-right: 10px;
        }
        .btn-retry:hover {
            background-color: #0069d9;
            color: #fff;
            text-decoration: none;
        }
        .btn-dashboard {
            background-color: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-dashboard:hover {
            background-color: #5a6268;
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
    <div class="failed-container">
        <div class="failed-card">
            <img src="assets/images/logo1.png" alt="ALLEA Logo" class="logo">
            <div class="failed-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1 class="failed-title">Subscription Payment Failed</h1>
            <p class="failed-message">We couldn't process your subscription payment. Please try again or contact support if the problem persists.</p>
            
            <?php if ($status !== 'FAILED' || !empty($error)): ?>
            <div class="status-message">
                <strong>Status:</strong> <?php echo htmlspecialchars($status); ?>
                <?php if (!empty($error)): ?>
                <br>
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div>
                <a href="renew_subscription.php" class="btn-retry">Try Again</a>
                <a href="ownerDashboard.php" class="btn-dashboard">Go to Dashboard</a>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
