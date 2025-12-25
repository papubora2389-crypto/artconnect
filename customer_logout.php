<?php
session_start();
$active_page = 'customer_register';
include 'header.php';

// Destroy the session
session_unset();
session_destroy();

// Redirect to homepage
header("Location: customer_login.php");
exit();
?>