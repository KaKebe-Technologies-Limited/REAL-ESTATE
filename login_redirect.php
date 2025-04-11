<?php
// This file handles any direct GET requests to login.php
// and redirects them to the login.html page

// Log the redirect for debugging
file_put_contents('login_redirect.log', date('Y-m-d H:i:s') . " - Redirecting from " . $_SERVER['REQUEST_URI'] . " to login.html\n", FILE_APPEND);

// Redirect to login.html
header('Location: login.html');
exit;
