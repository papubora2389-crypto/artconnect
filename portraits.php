<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the active page for navigation highlighting
$active_page = 'portraits';
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Portraits - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <section class="container my-5">
        <h1>Order Your Custom Portrait</h1>
        <p>Create a personalized portrait with your preferred style and specifications.</p>

        <div class="text-center mb-4">
            <?php if (isset($_SESSION['customer_email'])): ?>
                <a href="order_form.php" class="btn btn-primary btn-lg">Place Order</a>
            <?php else: ?>
                <a href="customer_login.php" class="btn btn-primary btn-lg">Login to Order</a>
            <?php endif; ?>
        </div>

        <div id="portraitCarousel" class="carousel slide carousel-square mx-auto" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="uploads/portrait1.webp" class="d-block" alt="Portrait 1">
                </div>
                <div class="carousel-item">
                    <img src="uploads/portrait2.webp" class="d-block" alt="Portrait 2">
                </div>
                <div class="carousel-item">
                    <img src="uploads/portrait3.webp" class="d-block" alt="Portrait 3">
                </div>
                <div class="carousel-item">
                    <img src="uploads/portrait4.webp" class="d-block" alt="Portrait 4">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#portraitCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#portraitCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.querySelector('#portraitCarousel');
            new bootstrap.Carousel(carousel, {
                interval: 2000,
                wrap: true
            });
        });
    </script>
</body>
</html>