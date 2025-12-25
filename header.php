<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/db.php';

// Check database connection
if (!$conn) {
    die('<div class="alert alert-danger">Database connection failed. Please try again later.</div>');
}

// Determine the active page (default to empty if not set)
$active_page = isset($active_page) ? $active_page : '';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="admin_login.php">
            <img src="assets/images/logo.png" alt="ArtConnect Logo" class="navbar-logo me-2">
            ArtConnect
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'index' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'painting' ? 'active' : ''; ?>" href="painting.php">Order Painting</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'portraits' ? 'active' : ''; ?>" href="portraits.php">Order Portraits</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'order_form' ? 'active' : ''; ?>" href="order_form.php">Order Form</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'about' ? 'active' : ''; ?>" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'review' ? 'active' : ''; ?>" href="review.php">Reviews</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active_page === 'contact' ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['customer_email'])): ?>
                    <li class="nav-item"><a class="nav-link <?php echo $active_page === 'customer_panel' ? 'active' : ''; ?>" href="customer_panel.php">Customer Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer_logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?php echo $active_page === 'customer_login' ? 'active' : ''; ?>" href="customer_login.php">Customer Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>