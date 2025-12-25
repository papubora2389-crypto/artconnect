<?php
session_start();
require_once 'config/db.php';
$active_page = 'index';
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  

    <header class="hero text-center text-white">
        <div class="container">
            <h1>Welcome to ArtConnect</h1>
            <p>Discover unique paintings and portraits crafted with passion.</p>
            <a href="painting.php" class="btn btn-primary btn-lg">Order a Painting</a>
        </div>
    </header>

    <section class="container my-5">
        <h2 class="text-center mb-4">Featured Paintings</h2>
        <?php
        try {
            $stmt = $conn->query("SELECT image_path FROM gallery1 WHERE type = 'painting' LIMIT 4");
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($images) === 0) {
                echo '<p class="text-center">No paintings available in the gallery.</p>';
            } else {
        ?>
        <div id="artworkCarousel" class="carousel slide carousel-square mx-auto" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                <?php
                $first = true;
                foreach ($images as $row) {
                    $imagePath = 'uploads/' . htmlspecialchars($row['image_path']);
                    echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                    echo '<img src="' . $imagePath . '" class="d-block carousel-img" alt="Painting" loading="lazy">';
                    echo '</div>';
                    $first = false;
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#artworkCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#artworkCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php
            }
        } catch (PDOException $e) {
            echo '<p class="text-center text-danger">Error fetching paintings: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>

        <h2 class="text-center mt-5 mb-4">Featured Portraits</h2>
        <?php
        try {
            $stmt = $conn->query("SELECT image_path FROM gallery1 WHERE type = 'portrait' LIMIT 4");
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($images) === 0) {
                echo '<p class="text-center">No portraits available in the gallery.</p>';
            } else {
        ?>
        <div id="portraitCarousel" class="carousel slide carousel-square mx-auto" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                <?php
                $first = true;
                foreach ($images as $row) {
                    $imagePath = 'uploads/' . htmlspecialchars($row['image_path']);
                    echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                    echo '<img src="' . $imagePath . '" class="d-block carousel-img" alt="Portrait" loading="lazy">';
                    echo '</div>';
                    $first = false;
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#portraitCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#portraitCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <?php
            }
        } catch (PDOException $e) {
            echo '<p class="text-center text-danger">Error fetching portraits: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>