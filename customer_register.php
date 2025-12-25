<?php
session_start();
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['customer_email'])) {
    header("Location: order_form.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validate input
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('A valid email address is required.');
        }
        if (empty($name)) {
            throw new Exception('Full name is required.');
        }
        if (empty($password) || strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('An account with this email already exists.');
        }

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert customer into database (only email, name, and password)
        $stmt = $conn->prepare("INSERT INTO customers (email, name, password) VALUES (?, ?, ?)");
        $stmt->execute([$email, $name, $hashed_password]);

        // Log the customer in
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_name'] = $name; // Store name for use in other pages
        header("Location: order_form.php");
        exit();
    } catch (PDOException $e) {
        // Check for duplicate entry error (MySQL error code 1062)
        if ($e->getCode() == 1062) {
            $message = '<div class="alert alert-danger">An account with this email already exists.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
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
    <title>Customer Registration - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
    $active_page = 'customer_register';
    include 'header.php';
    ?>

    <section class="container my-5">
        <h1>Customer Registration</h1>
        <p>Create an account to start ordering custom artwork.</p>
        <?php echo $message; ?>
        <form method="POST" action="" class="row g-3" id="registerForm">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
        <p class="mt-3">Already have an account? <a href="customer_login.php">Login here</a>.</p>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
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