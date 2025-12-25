<?php
require_once 'config/db.php';
$active_page = 'gallery'; include 'header.php';

// Fetch admin-uploaded gallery photos from the gallery table
$photos = [];
try {
    $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE type = 'gallery_photo' AND image_path LIKE 'gallery_%'");
    $stmt->execute();
    $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $photos = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

    <section class="container my-5">
        <h1>Our Gallery</h1>
        <p>Discover a collection of beautiful artworks created by our talented artists.</p>

        <h3>Gallery Photos</h3>
        <?php if (empty($photos)): ?>
            <p>No gallery photos available at the moment. Please check back later.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($photos as $photo): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="uploads/<?php echo htmlspecialchars($photo); ?>" class="card-img-top" alt="Gallery Photo">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars(pathinfo($photo, PATHINFO_FILENAME)); ?></h5>
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
</body>
</html>