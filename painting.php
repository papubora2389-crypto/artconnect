<?php
session_start();
$active_page = 'painting';
include 'header.php';

// Fetch paintings
$paintings = [];
try {
    $stmt = $conn->query("SELECT id, image_path, price, size FROM gallery WHERE type = 'painting'");
    $paintings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $paintings = [];
    error_log("Error fetching paintings: " . $e->getMessage());
}

// Fetch wishlist status for the logged-in customer
$customer_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : null;
$wishlist_items = [];
if ($customer_email) {
    try {
        $stmt = $conn->prepare("SELECT painting_id FROM wishlist WHERE customer_email = ?");
        $stmt->execute([$customer_email]);
        $wishlist_items = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Array of painting_ids in the wishlist
    } catch (Exception $e) {
        error_log("Error fetching wishlist: " . $e->getMessage());
        $wishlist_items = [];
    }
}

// Check for order success message
$order_message = '';
if (isset($_SESSION['order_message'])) {
    $order_message = $_SESSION['order_message'];
    unset($_SESSION['order_message']); // Clear the message after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Painting - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .painting-card {
            transition: transform 0.2s;
        }
        .painting-card:hover {
            transform: scale(1.05);
        }
        .wishlist-btn {
            cursor: pointer;
            color: #ccc;
        }
        .wishlist-btn.active {
            color: red;
        }
        .modal-image {
            max-height: 400px;
            object-fit: contain;
            width: 100%;
        }
    </style>
</head>
<body>
    <section class="container my-5">
        <h1>Order a Painting</h1>
        <p>Browse our collection of paintings and order your favorite piece today!</p>

        <?php if (!empty($order_message)) echo $order_message; ?>

        <?php if (empty($paintings)): ?>
            <p>No paintings available at the moment.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($paintings as $painting): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card painting-card">
                            <img src="uploads/<?php echo htmlspecialchars($painting['image_path']); ?>" class="card-img-top" alt="Painting" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">Painting #<?php echo htmlspecialchars($painting['id']); ?></h5>
                                <p class="card-text">Price: ₹<?php echo htmlspecialchars(number_format($painting['price'], 2)); ?></p>
                                <p class="card-text">Size: <?php echo htmlspecialchars($painting['size']); ?></p>
                                <button type="button" class="btn btn-secondary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#paintingModal<?php echo htmlspecialchars($painting['id']); ?>">View</button>
                                <i class="fas fa-heart wishlist-btn float-end <?php echo in_array($painting['id'], $wishlist_items) ? 'active' : ''; ?>" data-painting-id="<?php echo htmlspecialchars($painting['id']); ?>" onclick="toggleWishlist(this, <?php echo htmlspecialchars($painting['id']); ?>)"></i>
                            </div>
                        </div>

                        <!-- Modal for each painting -->
                        <div class="modal fade" id="paintingModal<?php echo htmlspecialchars($painting['id']); ?>" tabindex="-1" aria-labelledby="paintingModalLabel<?php echo htmlspecialchars($painting['id']); ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paintingModalLabel<?php echo htmlspecialchars($painting['id']); ?>">Painting #<?php echo htmlspecialchars($painting['id']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="uploads/<?php echo htmlspecialchars($painting['image_path']); ?>" class="modal-image mb-3" alt="Painting">
                                        <p>Price: ₹<?php echo htmlspecialchars(number_format($painting['price'], 2)); ?></p>
                                        <p>Size: <?php echo htmlspecialchars($painting['size']); ?></p>
                                        <i class="fas fa-heart wishlist-btn mb-3 <?php echo in_array($painting['id'], $wishlist_items) ? 'active' : ''; ?>" id="modalWishlistBtn<?php echo htmlspecialchars($painting['id']); ?>" data-painting-id="<?php echo htmlspecialchars($painting['id']); ?>" onclick="toggleWishlist(this, <?php echo htmlspecialchars($painting['id']); ?>)"></i>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="final_form.php?painting_id=<?php echo htmlspecialchars($painting['id']); ?>&price=<?php echo htmlspecialchars($painting['price']); ?>" class="btn btn-primary">Buy Now</a>
                                    </div>
                                </div>
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
    <script>
        function toggleWishlist(element, paintingId) {
            // Update both the card and modal wishlist buttons
            const cardBtn = document.querySelector(`.wishlist-btn[data-painting-id="${paintingId}"]:not(#modalWishlistBtn${paintingId})`);
            const modalBtn = document.getElementById(`modalWishlistBtn${paintingId}`);
            const isActive = element.classList.contains('active');

            // Toggle the visual state for both buttons
            element.classList.toggle('active');
            if (cardBtn && cardBtn !== element) cardBtn.classList.toggle('active');
            if (modalBtn && modalBtn !== element) modalBtn.classList.toggle('active');

            // Make an AJAX request to add/remove from wishlist
            fetch('wishlist_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${isActive ? 'remove' : 'add'}&painting_id=${paintingId}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert the toggle if the server request fails
                    element.classList.toggle('active');
                    if (cardBtn && cardBtn !== element) cardBtn.classList.toggle('active');
                    if (modalBtn && modalBtn !== element) modalBtn.classList.toggle('active');
                    alert(data.message || 'An error occurred while updating the wishlist.');
                } else {
                    console.log(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                element.classList.toggle('active');
                if (cardBtn && cardBtn !== element) cardBtn.classList.toggle('active');
                if (modalBtn && modalBtn !== element) modalBtn.classList.toggle('active');
                alert('An error occurred while updating the wishlist: ' + error.message);
            });
        }
    </script>
</body>
</html>