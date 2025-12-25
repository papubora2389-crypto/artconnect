<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';

// Check database connection
if (!$conn) {
    die('<div class="alert alert-danger">Database connection failed. Please try again later.</div>');
}

// Initialize message variable
$message = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['customer_email'])) {
    try {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $customer_email = $_SESSION['customer_email'];

        // Validate input
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Rating must be between 1 and 5.');
        }
        if (strlen($comment) > 500) {
            throw new Exception('Comment must not exceed 500 characters.');
        }

        // Insert review into database
        $stmt = $conn->prepare("INSERT INTO reviews (customer_email, rating, comment) VALUES (?, ?, ?)");
        $stmt->execute([$customer_email, $rating, $comment]);

        $message = '<div class="alert alert-success">Review submitted successfully!</div>';
    } catch (PDOException $e) {
        // Check for duplicate entry error (MySQL error code 1062)
        if ($e->getCode() == 1062) {
            $message = '<div class="alert alert-danger">You have already submitted a review. Multiple reviews are not allowed.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Fetch all reviews to display
try {
    $stmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, c.name 
                           FROM reviews r 
                           JOIN customers c ON r.customer_email = c.email 
                           ORDER BY r.created_at DESC");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reviews = [];
    $message .= '<div class="alert alert-danger">Error fetching reviews: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
    $active_page = 'review';
    include 'header.php';
    ?>

    <section class="container my-5">
        <h1>Customer Reviews</h1>
        <p>Read what our customers have to say about their ArtConnect experience.</p>

        <?php echo $message; ?>

        <!-- Review Submission Form (Only for Logged-In Users) -->
        <?php if (isset($_SESSION['customer_email'])): ?>
            <h3>Write a Review</h3>
            <form method="POST" action="" class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="rating" class="form-label">Rating (1-5)</label>
                    <select id="rating" name="rating" class="form-select" required>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5" selected>5 - Excellent</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label for="comment" class="form-label">Your Review</label>
                    <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Share your experience..." maxlength="500"></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                Please <a href="customer_login.php?message=<?= urlencode('Please login to write a review.') ?>">login</a> to write a review.
            </div>
        <?php endif; ?>

        <!-- Display Reviews -->
        <h3 class="mt-5">All Reviews</h3>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to share your experience!</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($review['name']) ?></h5>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star"><?= $i <= $review['rating'] ? '★' : '☆' ?></span>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text"><?= htmlspecialchars($review['comment']) ?: '<em>No comment provided.</em>' ?></p>
                                <p class="card-text"><small class="text-muted">Posted on <?= date('F j, Y, g:i a', strtotime($review['created_at'])) ?></small></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .star-rating .star {
            color: #f5c518;
            font-size: 1.2rem;
        }
    </style>
</body>
</html>