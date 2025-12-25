<?php
// Initialize message variable
$active_page = 'contact';
include 'header.php';
$message = '';

// Fetch logged-in customer's details from session
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : '';
$customer_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

        // Basic validation
        if (empty($name)) {
            throw new Exception('Name is required.');
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('A valid email address is required.');
        }
        if (empty($message_text)) {
            throw new Exception('Message is required.');
        }

        // Insert into contacts table
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message_text]);

        $message = '<div class="alert alert-success">Thank you for your message, ' . htmlspecialchars($name) . '! We will get back to you soon.</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <section class="container my-5">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you! Please fill out the form below to get in touch.</p>
        <?php echo $message; ?>
        <form method="POST" action="" class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" value="<?php echo htmlspecialchars($customer_name); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" value="<?php echo htmlspecialchars($customer_email); ?>" required>
            </div>
            <div class="col-12 mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" class="form-control" rows="5" placeholder="Enter your message" required></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Send Message</button>
            </div>
        </form>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>