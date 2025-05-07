<?php
// Check if we need to redirect back to the callback
$redirect_back = isset($_GET['redirect_back']) && $_GET['redirect_back'] == 1;
$callback_url = '';

// If we need to redirect back, construct the callback URL
if ($redirect_back) {
    // Get the original query parameters if any
    $query = $_SERVER['QUERY_STRING'];
    $query = str_replace('redirect_back=1', '', $query);
    $query = trim($query, '&');
    
    // Construct the callback URL with processing parameter
    $callback_url = 'owner_payment_callback.php?processing=1';
    if (!empty($query)) {
        $callback_url .= '&' . $query;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - ALLEA</title>
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
        .processing-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        .processing-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
            height: auto;
        }
        .spinner-container {
            text-align: center;
            padding: 30px 20px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            margin-bottom: 20px;
            color: #007bff;
        }
        h3 {
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="processing-container">
        <div class="processing-box">
            <div class="logo">
                <img src="assets/images/logo1.png" alt="Company Logo">
            </div>
            <div class="spinner-container">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h3>Processing Your Payment</h3>
                <p>Please wait while we verify your payment...</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <?php if ($redirect_back && !empty($callback_url)): ?>
    <script>
        // Redirect back to the callback URL after a short delay
        setTimeout(function() {
            window.location.href = "<?php echo $callback_url; ?>";
        }, 2000);
    </script>
    <?php endif; ?>
</body>
</html>
