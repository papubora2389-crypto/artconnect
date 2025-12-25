<?php
session_start();
require_once 'config/db.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_email'])) {
    header("Location: login.php"); // Redirect to login page
    exit();
}

$customer_email = $_SESSION['customer_email'];
$message = '';

// Fetch ordered paintings for the customer
$ordered_paintings = [];
try {
    $stmt = $conn->prepare("
        SELECT po.id, po.total_price, po.status, 
               g.type, g.size
        FROM painting_order po
        JOIN gallery g ON po.painting_id = g.id
        WHERE po.email = ? AND g.type = 'painting'
        ORDER BY po.created_at DESC
    ");
    $stmt->execute([$customer_email]);
    $ordered_paintings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message .= '<div class="alert alert-danger">Error fetching ordered paintings: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $ordered_paintings = [];
}

// Fetch ordered portraits for the customer
$ordered_portraits = [];
try {
    $stmt = $conn->prepare("SELECT id, art_type, art_size, total_price, status FROM orders WHERE email = ? ORDER BY id DESC");
    $stmt->execute([$customer_email]);
    $ordered_portraits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message .= '<div class="alert alert-danger">Error fetching ordered portraits: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $ordered_portraits = [];
}

// Fetch wishlist items for the customer
$wishlist_items = [];
try {
    $stmt = $conn->prepare("SELECT id, art_type, art_size, num_faces, added_date FROM wishlist WHERE customer_email = ? ORDER BY added_date DESC");
    $stmt->execute([$customer_email]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message .= '<div class="alert alert-danger">Error fetching wishlist: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $wishlist_items = [];
}

// Handle wishlist item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_wishlist_id'])) {
    try {
        $wishlist_id = (int)$_POST['remove_wishlist_id'];
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND customer_email = ?");
        $stmt->execute([$wishlist_id, $customer_email]);
        $message = '<div class="alert alert-success">Wishlist item removed successfully!</div>';
        header("Location: customer_panel.php"); // Refresh the page
        exit();
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error removing wishlist item: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Panel - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .extra-table {
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fa;
            margin-top: 30px;
        }
        .extra-table h3 {
            color: #007bff;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="review.php">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="customer_panel.php">Customer Panel</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="container my-5">
        <h1>Welcome to Your Customer Panel</h1>
        <p>View your orders and wishlist below.</p>
        <?php echo $message; ?>

        <!-- Ordered Portraits Section -->
        <h3 class="mt-4">Ordered Portraits</h3>
        <?php if (empty($ordered_portraits)): ?>
            <p>You have no portrait orders yet. <a href="order_form.php">Place a portrait order now!</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Price (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordered_portraits as $portrait): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($portrait['id']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $portrait['art_type']))); ?></td>
                                <td><?php echo htmlspecialchars($portrait['art_size']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($portrait['total_price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($portrait['status'] ?? 'Pending'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Wishlist Section -->
        <h3 class="mt-5">Your Wishlist</h3>
        <?php if (empty($wishlist_items)): ?>
            <p>Your wishlist is empty. Start adding items to your wishlist!</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Art Type</th>
                            <th>Art Size</th>
                            <th>Number of Faces</th>
                            <th>Added On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wishlist_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $item['art_type']))); ?></td>
                                <td><?php echo htmlspecialchars($item['art_size']); ?></td>
                                <td><?php echo htmlspecialchars($item['num_faces']); ?></td>
                                <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($item['added_date']))); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="remove_wishlist_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Extra Table: Ordered Paintings -->
        <div class="extra-table">
            <h3>Ordered Paintings (Extra View)</h3>
            <p>This table shows your painting orders with details sourced from the painting_order table (ID, Price, Status) and gallery table (Type, Size).</p>
            <?php if (empty($ordered_paintings)): ?>
                <p>You have no painting orders yet. <a href="painting.php">Order a painting now!</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Price (₹)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordered_paintings as $painting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($painting['id']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($painting['type'])); ?></td>
                                    <td><?php echo htmlspecialchars($painting['size']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($painting['total_price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($painting['status'] ?? 'Pending'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>