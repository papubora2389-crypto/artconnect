<?php
session_start();

// Initialize message variable
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Basic validation
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required.');
        }

        // Placeholder authentication (replace with database check in production)
        $admin_username = 'admin';
        $admin_password = 'password123'; // In production, use password hashing

        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            $message = '<div class="alert alert-success">Login successful! Welcome, Admin.</div>';
            // Optionally redirect to admin panel
            // header('Location: admin_panel.php');
            // exit;
        } else {
            throw new Exception('Invalid username or password.');
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $message = '<div class="alert alert-success">You are already logged in as Admin.</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="ArtConnect Logo" class="navbar-logo me-2">
                ArtConnect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="painting.php">Order Painting</a></li>
                    <li class="nav-item"><a class="nav-link" href="portraits.php">Order Portraits</a></li>
                    <li class="nav-item"><a class="nav-link" href="order_form.php">Order Form</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="review.php">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link active" href="admin_login.php">Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="container my-5">
        <h1>Admin Login</h1>
        <p>Please enter your credentials to access the admin panel.</p>
        <?php echo $message; ?>
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <div class="mb-4">
                <a href="logout.php" class="btn btn-danger">Logout</a>
                <a href="admin_panel.php" class="btn btn-primary ms-2">Go to Admin Panel</a>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>