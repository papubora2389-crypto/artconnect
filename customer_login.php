<?php
session_start();
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['customer_email'])) {
    header("Location: order_form.php");
    exit();
}

$message = '';

// Check for message in URL (e.g., from redirect)
if (isset($_GET['message'])) {
    $message = '<div class="alert alert-info">' . htmlspecialchars(urldecode($_GET['message'])) . '</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validate input
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('A valid email address is required.');
        }
        if (empty($password)) {
            throw new Exception('Password is required.');
        }
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }

        // Check customer credentials
        $stmt = $conn->prepare("SELECT password FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            throw new Exception('No account found with that email.');
        }

        // Verify hashed password
        if (!password_verify($password, $customer['password'])) {
            throw new Exception('Incorrect password.');
        }

        // Set session and redirect
        $_SESSION['customer_email'] = $email;
        header("Location: order_form.php");
        exit();
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
    $active_page = 'customer_login';
    include 'header.php';
    ?>

    <section class="container my-5">
        <h1>Customer Login</h1>
        <p>Please login to access your customer panel.</p>
        <?php echo $message; ?>
        <form method="POST" action="" class="row g-3" id="loginForm">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        <p class="mt-3">Don't have an account? <a href="customer_register.php">Register here</a>.</p>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const password = document.getElementById('password');
            
            form.addEventListener('submit', function(event) {
                if (password.value.length < 6) {
                    event.preventDefault();
                    alert('Password must be at least 6 characters long.');
                }
            });
        });
    </script>
</body>
</html>