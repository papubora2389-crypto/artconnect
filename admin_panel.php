<?php
session_start();
require_once 'config/db.php';

// Redirect to login if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Initialize messages
$painting_message = '';
$gallery_message = '';
$delete_message = '';

// Handle painting upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_painting'])) {
    try {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $painting_file = $_FILES['painting_file'];
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $size = isset($_POST['size']) ? trim($_POST['size']) : '';

        // Validate painting file
        if (!isset($painting_file) || $painting_file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Please upload a painting file.");
        }
        if (!in_array($painting_file['type'], $allowed_types)) {
            throw new Exception('Painting file must be a JPEG, PNG, GIF, or WEBP file.');
        }
        if ($painting_file['size'] > $max_size) {
            throw new Exception('Painting file size must not exceed 5MB.');
        }

        // Validate price and size
        if ($price <= 0) {
            throw new Exception('Price must be a positive number.');
        }
        if (empty($size)) {
            throw new Exception('Size is required.');
        }

        $filename = 'painting_' . time() . '_' . basename($painting_file['name']);
        $upload_path = 'uploads/' . $filename;
        if (!move_uploaded_file($painting_file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload painting file.');
        }

        // Insert into gallery table with price and size
        $stmt = $conn->prepare("INSERT INTO gallery (image_path, type, price, size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$filename, 'painting', $price, $size]);

        $painting_message = '<div class="alert alert-success">Painting uploaded successfully!</div>';
    } catch (Exception $e) {
        $painting_message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle gallery photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_gallery'])) {
    try {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $gallery_file = $_FILES['gallery_file'];

        if (!isset($gallery_file) || $gallery_file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Please upload a gallery photo.");
        }
        if (!in_array($gallery_file['type'], $allowed_types)) {
            throw new Exception('Gallery photo must be a JPEG, PNG, GIF, or WEBP file.');
        }
        if ($gallery_file['size'] > $max_size) {
            throw new Exception('Gallery photo size must not exceed 5MB.');
        }

        $filename = 'gallery_' . time() . '_' . basename($gallery_file['name']);
        $upload_path = 'uploads/' . $filename;
        if (!move_uploaded_file($gallery_file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload gallery photo.');
        }

        // Insert into gallery table
        $stmt = $conn->prepare("INSERT INTO gallery (image_path, type) VALUES (?, ?)");
        $stmt->execute([$filename, 'gallery_photo']);

        $gallery_message = '<div class="alert alert-success">Gallery photo uploaded successfully!</div>';
    } catch (Exception $e) {
        $gallery_message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle deletion of paintings and gallery photos
if (isset($_GET['action']) && ($_GET['action'] === 'delete_painting' || $_GET['action'] === 'delete_gallery')) {
    try {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $type = $_GET['action'] === 'delete_painting' ? 'painting' : 'gallery_photo';

        if ($id <= 0) {
            throw new Exception('Invalid ID.');
        }

        // Fetch the image path to delete the file
        $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ? AND type = ?");
        $stmt->execute([$id, $type]);
        $image_path = $stmt->fetchColumn();

        if (!$image_path) {
            throw new Exception('Image not found.');
        }

        // Delete the file from uploads/
        $file_path = 'uploads/' . $image_path;
        if (file_exists($file_path) && !unlink($file_path)) {
            throw new Exception('Failed to delete the image file.');
        }

        // Delete the record from the gallery table
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ? AND type = ?");
        $stmt->execute([$id, $type]);

        $delete_message = '<div class="alert alert-success">' . ucfirst($type) . ' deleted successfully!</div>';
    } catch (Exception $e) {
        $delete_message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle status update for orders (both painting and portrait)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    try {
        $order_id = (int)$_POST['order_id'];
        $status = in_array($_POST['status'], ['pending', 'shipped', 'delivered', 'cancelled']) ? $_POST['status'] : 'pending';
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        header("Location: admin_panel.php");
        exit;
    } catch (Exception $e) {
        $delete_message = '<div class="alert alert-danger">Error updating order status: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle deletion of orders (both painting and portrait)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    try {
        $order_id = (int)$_POST['delete_order_id'];
        // Delete associated photos from order_photos (for portrait orders)
        $stmt = $conn->prepare("DELETE FROM order_photos WHERE order_id = ?");
        $stmt->execute([$order_id]);
        // Delete the order from orders table
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $delete_message = '<div class="alert alert-success">Order deleted successfully!</div>';
        header("Location: admin_panel.php");
        exit;
    } catch (Exception $e) {
        $delete_message = '<div class="alert alert-danger">Error deleting order: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Fetch ordered portraits from orders and order_photos tables
$ordered_portraits = [];
try {
    $stmt = $conn->query("
        SELECT op.order_id, op.photo_filename, 
               o.customer_name, o.email, o.phone_number, o.address, o.delivery_pincode, 
               o.art_type, o.art_size, o.total_price, o.status, o.created_at, 
               o.orientation, o.special_instructions
        FROM order_photos op
        JOIN orders o ON op.order_id = o.id
        WHERE op.photo_type = 'portrait'
        ORDER BY op.order_id DESC
    ");
    $ordered_portraits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ordered_portraits = [];
}

// Fetch ordered paintings from orders and gallery tables
$ordered_paintings = [];
try {
    $stmt = $conn->query("
        SELECT o.id, o.customer_name, o.email, o.phone_number, o.address, o.delivery_pincode, 
               o.art_type AS type, o.art_size AS size, o.total_price AS price, o.status, o.created_at, 
               g.image_path
        FROM orders o
        LEFT JOIN gallery g ON o.painting_id = g.id
        WHERE o.art_type = 'painting'
        ORDER BY o.created_at DESC
    ");
    $ordered_paintings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $ordered_paintings = [];
    $delete_message = '<div class="alert alert-danger">Error fetching ordered paintings: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Fetch reviews
$reviews = [];
try {
    $stmt = $conn->query("SELECT * FROM reviews");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reviews = [];
}

// Fetch paintings
$paintings = [];
try {
    $stmt = $conn->query("SELECT id, image_path, price, size FROM gallery WHERE type = 'painting'");
    $paintings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $paintings = [];
}

// Fetch gallery photos
$gallery_photos = [];
try {
    $stmt = $conn->query("SELECT id, image_path FROM gallery WHERE type = 'gallery_photo'");
    $gallery_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $gallery_photos = [];
}

// Fetch contact submissions
$contacts = [];
try {
    $stmt = $conn->query("SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $contacts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ArtConnect</title>
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
                        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="container my-5">
        <h1>Admin Panel</h1>
        <p>Welcome to the admin panel. Manage paintings, reviews, customer details, ordered portraits, and contact submissions below.</p>
        <?php echo $delete_message; ?>

        <!-- Upload Painting -->
        <h3>Upload Painting</h3>
        <?php echo $painting_message; ?>
        <form method="POST" action="" enctype="multipart/form-data" class="row g-3 mb-5">
            <input type="hidden" name="upload_painting" value="1">
            <div class="col-md-6 mb-3">
                <label for="paintingFile" class="form-label">Upload Painting</label>
                <input type="file" id="paintingFile" name="painting_file" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="price" class="form-label">Price (₹)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" placeholder="Enter price in rupees (e.g., 999.99)" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="size" class="form-label">Size</label>
                <input type="text" id="size" name="size" class="form-control" placeholder="Enter size (e.g., 24x36 inches)" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Upload Painting</button>
            </div>
        </form>

        <!-- Manage Paintings -->
        <h3>Manage Paintings</h3>
        <?php if (empty($paintings)): ?>
            <p>No paintings available to manage.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Filename</th>
                        <th>Price (₹)</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paintings as $painting): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($painting['id']); ?></td>
                            <td>
                                <img src="uploads/<?php echo htmlspecialchars($painting['image_path']); ?>" alt="Painting" style="max-width: 100px; height: auto;">
                            </td>
                            <td><?php echo htmlspecialchars($painting['image_path']); ?></td>
                            <td>₹<?php echo htmlspecialchars(number_format($painting['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($painting['size']); ?></td>
                            <td>
                                <a href="?action=delete_painting&id=<?php echo htmlspecialchars($painting['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this painting?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- View Reviews -->
        <h3>Customer Reviews</h3>
        <?php if (empty($reviews)): ?>
            <p>No reviews available. (Note: Reviews table may not exist yet.)</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Email</th>
                        <th>Review</th>
                        <th>Rating</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($review['id']); ?></td>
                            <td><?php echo htmlspecialchars($review['customer_email']); ?></td>
                            <td><?php echo htmlspecialchars($review['comment']); ?></td>
                            <td><?php echo htmlspecialchars($review['rating']); ?></td>
                            <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- View Customer Orders and Portraits -->
        <h3>Customer Orders and Portraits</h3>
        <?php if (empty($ordered_portraits)): ?>
            <p>No portraits have been ordered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Customer Email</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Pincode</th>
                            <th>Portrait Image</th>
                            <th>Art Type</th>
                            <th>Art Size</th>
                            <th>Total Price (₹)</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Orientation</th>
                            <th>Special Instructions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordered_portraits as $portrait): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($portrait['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['email']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['phone_number']) ?: 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($portrait['address']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['delivery_pincode']); ?></td>
                                <td>
                                    <a href="uploads/<?php echo htmlspecialchars($portrait['photo_filename']); ?>" target="_blank">
                                        <img src="uploads/<?php echo htmlspecialchars($portrait['photo_filename']); ?>" alt="Ordered Portrait" style="max-width: 100px; height: auto;">
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($portrait['art_type']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['art_size']); ?></td>
                                <td>₹<?php echo htmlspecialchars(number_format($portrait['total_price'], 2)); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($portrait['order_id']); ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $portrait['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="shipped" <?php echo $portrait['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $portrait['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $portrait['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($portrait['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($portrait['orientation']) ?: 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($portrait['special_instructions']) ?: 'None'; ?></td>
                                <td>
                                    <a href="download.php?file=<?php echo urlencode($portrait['photo_filename']); ?>" class="btn btn-primary btn-sm">Download</a>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($portrait['order_id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- View Ordered Paintings -->
        <h3>Ordered Paintings</h3>
        <?php if (empty($ordered_paintings)): ?>
            <p>No paintings have been ordered yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Pincode</th>
                            <th>Painting Image</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Price (₹)</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordered_paintings as $painting): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($painting['id']); ?></td>
                                <td><?php echo htmlspecialchars($painting['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($painting['email']); ?></td>
                                <td><?php echo htmlspecialchars($painting['phone_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($painting['address']); ?></td>
                                <td><?php echo htmlspecialchars($painting['delivery_pincode']); ?></td>
                                <td>
                                    <?php if ($painting['image_path']): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($painting['image_path']); ?>" target="_blank">
                                            <img src="uploads/<?php echo htmlspecialchars($painting['image_path']); ?>" alt="Ordered Painting" style="max-width: 100px; height: auto;">
                                        </a>
                                    <?php else: ?>
                                        <span>N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(ucfirst($painting['type'])); ?></td>
                                <td><?php echo htmlspecialchars($painting['size']); ?></td>
                                <td>₹<?php echo htmlspecialchars(number_format($painting['price'], 2)); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($painting['id']); ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $painting['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="shipped" <?php echo $painting['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $painting['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $painting['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($painting['created_at']))); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($painting['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- View Contact Us Submissions -->
        <h3>Contact Us Submissions</h3>
        <?php if (empty($contacts)): ?>
            <p>No contact submissions found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Submission Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contact['id']); ?></td>
                                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                <td><?php echo htmlspecialchars($contact['message']); ?></td>
                                <td><?php echo htmlspecialchars($contact['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>