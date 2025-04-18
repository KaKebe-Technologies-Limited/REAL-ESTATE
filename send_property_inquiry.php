<?php
// Initialize variables for error handling
$errors = [];
$success = false;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $property_name = isset($_POST['property_name']) ? htmlspecialchars(trim($_POST['property_name'])) : '';
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
    $property_type = isset($_POST['property_type']) ? htmlspecialchars(trim($_POST['property_type'])) : '';
    $agent_email = isset($_POST['agent_email']) ? filter_var(trim($_POST['agent_email']), FILTER_SANITIZE_EMAIL) : '';
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, proceed with sending email
    if (empty($errors)) {
        // Set email recipients
        $to = "info@eastafricanlandandrentals.net"; // Default recipient
        
        // If agent email is provided, add as CC
        $cc = '';
        if (!empty($agent_email)) {
            $cc = $agent_email;
        }
        
        // Set email subject
        $subject = "Property Inquiry: " . $property_name;
        
        // Build email headers
        $headers = "From: " . $email . "\r\n";
        if (!empty($cc)) {
            $headers .= "Cc: " . $cc . "\r\n";
        }
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Build email body
        $email_body = "
        <html>
        <head>
            <title>Property Inquiry</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 10px 20px; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { font-size: 12px; color: #777; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Property Inquiry</h2>
                </div>
                <div class='content'>
                    <p><strong>Property:</strong> " . $property_name . " (ID: " . $property_id . ", Type: " . $property_type . ")</p>
                    <p><strong>From:</strong> " . $name . "</p>
                    <p><strong>Email:</strong> " . $email . "</p>
                    <p><strong>Phone:</strong> " . $phone . "</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br($message) . "</p>
                </div>
                <div class='footer'>
                    <p>This email was sent from the East African Land and Rentals website contact form.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send email
        $mail_sent = mail($to, $subject, $email_body, $headers);
        
        if ($mail_sent) {
            $success = true;
        } else {
            $errors[] = "Failed to send email. Please try again later.";
        }
    }
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully!']);
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
        exit;
    }
    
    // For non-AJAX requests, redirect back to the property page
    if ($success) {
        // Redirect back to property page with success message
        header("Location: property-detail.php?id=" . $property_id . "&type=" . $property_type . "&status=success");
        exit;
    }
}

// If we get here with errors, redirect back with error information
if (!empty($errors)) {
    $error_string = implode(',', $errors);
    header("Location: property-detail.php?id=" . $property_id . "&type=" . $property_type . "&status=error&message=" . urlencode($error_string));
    exit;
}
?>
