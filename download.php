<?php
session_start();

// Redirect to login if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Validate filename parameter
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('No file specified.');
}

$filename = basename($_GET['file']); // Sanitize to prevent directory traversal
$file_path = 'uploads/' . $filename;

// Check if file exists
if (!file_exists($file_path) || !is_readable($file_path)) {
    die('File not found or not accessible.');
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Output the file
readfile($file_path);
exit;