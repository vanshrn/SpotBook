<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Use the host to construct the base URL
$baseURL = "http://" . $_SERVER['HTTP_HOST'];

// Add any subdirectory if your site is in one
$subDir = dirname(dirname($_SERVER['PHP_SELF']));
if ($subDir != '/') {
    $baseURL .= $subDir;
}

// Redirect to the home page
header("Location: $baseURL/index.html");
exit;
?>