<?php
session_start();
require_once 'config/db.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php");
    exit();
}

// Retrieve customer email from session
$customer_email = $_SESSION['customer_email'];

// Retrieve or fetch customer name
$customer_name = $_SESSION['customer_name'] ?? null;
if (!$customer_name) {
    // Fetch name from database if not in session
    try {
        $stmt = $conn->prepare("SELECT name FROM customers WHERE email = ?");
        $stmt->execute([$customer_email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        $customer_name = $customer['name'] ?? 'Unknown Customer';
        $_SESSION['customer_name'] = $customer_name; // Store in session for future use
    } catch (Exception $e) {
        $customer_name = 'Unknown Customer';
    }
}

// Initialize variables
$submission_message = '';
$submission_success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_form'])) {
    try {
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
        $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';

        // Validate inputs
        if (empty($address)) {
            throw new Exception('Address is required.');
        }
        if (empty($phone_number) || !preg_match('/^[0-9]{10}$/', $phone_number)) {
            throw new Exception('Phone number must be a valid 10-digit number.');
        }
        if (empty($pincode) || !preg_match('/^[0-9]{6}$/', $pincode)) {
            throw new Exception('Pincode must be a valid 6-digit number.');
        }

        // Insert into final_form_submissions table using session name and email
        $stmt = $conn->prepare("
            INSERT INTO final_form_submissions (name, email, address, phone_number, pincode)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_name, $customer_email, $address, $phone_number, $pincode]);

        // Set success message and flag
        $submission_message = '<div class="alert alert-success">Order Placed successfully!</div>';
        $submission_success = true;
    } catch (Exception $e) {
        $submission_message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Form - ArtConnect</title>
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
                    <li class="nav-item"><a class="nav-link" href="customer_panel.php">Customer Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer_logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="container my-5">
        <h1>Final Form</h1>
        <p>Please fill out the form below with your details.</p>

        <?php echo $submission_message; ?>

        <?php if ($submission_success): ?>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
            </div>
        <?php else: ?>
            <form method="POST" action="" class="row g-3">
                <input type="hidden" name="submit_form" value="1">

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($customer_name); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer_email); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Enter your full address"></textarea>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required placeholder="Enter your 10-digit phone number (e.g., 9876543210)">
                </div>
                <div class="mb-3">
                    <label for="pincode" class="form-label">Pincode</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" required placeholder="Enter your 6-digit pincode (e.g., 123456)">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Place Order</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
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