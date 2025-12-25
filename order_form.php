<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['customer_email'])) {
    header("Location: customer_login.php?message=" . urlencode("Please login to place an order."));
    exit();
}

// Check database connection
if (!$conn) {
    die('<div class="alert alert-danger">Database connection failed. Please try again later.</div>');
}

// Fetch customer details
$customer_email = $_SESSION['customer_email'];
$customer_name = '';
try {
    $stmt = $conn->prepare("SELECT name, email FROM customers WHERE email = ?");
    $stmt->execute([$customer_email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($customer) {
        $customer_name = $customer['name'];
        $customer_email = $customer['email']; // Ensure email matches session
        // Store in session for final_form.php
        $_SESSION['customer_name'] = $customer_name;
    } else {
        // If customer not found (unlikely since login succeeded), redirect to login
        session_destroy();
        header("Location: customer_login.php?message=" . urlencode("Customer not found. Please login again."));
        exit();
    }
} catch (Exception $e) {
    die('<div class="alert alert-danger">Error fetching customer details: ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Initialize message variable
$message = '';

// Handle order form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['remove_wishlist_id'])) {
    try {
        // Get form data (artwork details only)
        $num_faces = isset($_POST['num_faces']) ? (int)$_POST['num_faces'] : 0;
        $art_type = isset($_POST['art_type']) ? $_POST['art_type'] : '';
        $art_size = isset($_POST['art_size']) ? $_POST['art_size'] : '';
        $orientation = isset($_POST['orientation']) ? $_POST['orientation'] : '';
        $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0.00;
        $special_instructions = isset($_POST['special_instructions']) ? trim($_POST['special_instructions']) : '';

        // Validate order details
        if ($num_faces < 1 || $num_faces > 5) {
            throw new Exception('Number of faces must be between 1 and 5.');
        }
        if (!in_array($art_type, ['normal_sketch', 'watercolor', 'realistic'])) {
            throw new Exception('Invalid art type.');
        }
        if (!in_array($art_size, ['A5', 'A4', 'A3', 'A2'])) {
            throw new Exception('Invalid art size.');
        }
        if (!in_array($orientation, ['portrait', 'landscape', 'artist_choice'])) {
            throw new Exception('Invalid orientation.');
        }

        // Handle portrait photo upload
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $portrait_photo = $_FILES['portrait_photo'];

        if (!isset($portrait_photo) || $portrait_photo['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Please upload a portrait photo.");
        }
        if (!in_array($portrait_photo['type'], $allowed_types)) {
            throw new Exception('Portrait photo must be a JPEG, PNG, or GIF file.');
        }
        if ($portrait_photo['size'] > $max_size) {
            throw new Exception('Portrait photo size must not exceed 5MB.');
        }

        // Ensure uploads directory exists and is writable
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        if (!is_writable($upload_dir)) {
            throw new Exception('Upload directory is not writable. Please contact support.');
        }

        // Generate a unique photo filename using timestamp and random string
        $random_string = bin2hex(random_bytes(8)); // 16-character random string
        $photo_filename = 'portrait_' . time() . '_' . $random_string . '_' . basename($portrait_photo['name']);
        $upload_path = $upload_dir . $photo_filename;
        if (!move_uploaded_file($portrait_photo['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload your photo. Please try again.');
        }

        // Insert order into orders table (without customer details)
        $stmt = $conn->prepare("INSERT INTO orders (num_faces, art_type, art_size, orientation, total_price, customer_name, email, phone_number, address, delivery_pincode, special_instructions, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$num_faces, $art_type, $art_size, $orientation, $total_price, $customer_name, $customer_email, '', '', '', $special_instructions, 'Pending']);
        $order_id = $conn->lastInsertId();

        // Insert portrait photo into order_photos table
        $stmt = $conn->prepare("INSERT INTO order_photos (order_id, photo_type, photo_filename) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, 'portrait', $photo_filename]);

        // Redirect to final_form.php with order_id
        header("Location: final_form.php?order_id=" . urlencode($order_id));
        exit();
    } catch (PDOException $e) {
        // Check for duplicate entry error (MySQL error code 1062)
        if ($e->getCode() == 1062) {
            $message = '<div class="alert alert-danger">Duplicate photo filename detected. Please try uploading again.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
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
    <title>Order Form - ArtConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
    $active_page = 'order_form';
    include 'header.php';
    ?>

    <section class="container my-5">
        <h1>Order Your Custom Artwork</h1>
        <p>Customize your artwork and provide your details below.</p>
        <?php echo $message; ?>
        <form id="orderForm" method="POST" action="" class="row g-3" enctype="multipart/form-data">
            <h3 class="mt-3">Artwork Details</h3>
            <div class="col-md-6 mb-3">
                <label for="numFaces" class="form-label">Number of Faces</label>
                <select id="numFaces" name="num_faces" class="form-select" required>
                    <option value="1" selected>1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="artType" class="form-label">Art Type</label>
                <select id="artType" name="art_type" class="form-select" required>
                    <option value="normal_sketch" selected>Normal Sketch</option>
                    <option value="watercolor">Watercolor</option>
                    <option value="realistic">Realistic</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="artSize" class="form-label">Art Size</label>
                <select id="artSize" name="art_size" class="form-select" required>
                    <option value="A5" selected>A5 (5.8 x 8.3 in)</option>
                    <option value="A4">A4 (8.3 x 11.7 in)</option>
                    <option value="A3">A3 (11.7 x 16.5 in)</option>
                    <option value="A2">A2 (16.5 x 23.4 in)</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="orientation" class="form-label">Orientation</label>
                <select id="orientation" name="orientation" class="form-select" required>
                    <option value="portrait" selected>Portrait</option>
                    <option value="landscape">Landscape</option>
                    <option value="artist_choice">Artist Choice</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="totalPrice" class="form-label">Total Price (₹)</label>
                <input type="text" id="totalPrice" name="total_price" class="form-control" value="0.00" readonly>
            </div>
            <div class="col-md-12 mb-3">
                <label for="portraitPhoto" class="form-label">Upload Your Photo (Photo which you want to turn into portrait)</label>
                <input type="file" id="portraitPhoto" name="portrait_photo" class="form-control" accept="image/jpeg,image/png,image/gif" required>
            </div>
            <div class="col-12 mb-3">
                <label for="specialInstructions" class="form-label">Special Instructions (Optional)</label>
                <textarea id="specialInstructions" name="special_instructions" class="form-control" rows="3" placeholder="Any special instructions for the artist?"></textarea>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">Next</button>
            </div>
        </form>
    </section>

    <footer class="bg-dark text-white text-center py-3">
        <p>© 2025 ArtConnect. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing order form script');
            const form = document.getElementById('orderForm');
            const numFaces = document.getElementById('numFaces');
            const artType = document.getElementById('artType');
            const artSize = document.getElementById('artSize');
            const totalPrice = document.getElementById('totalPrice');

            // Check if elements are found
            if (!form || !numFaces || !artType || !artSize || !totalPrice) {
                console.error('One or more form elements not found:', {
                    form: !!form,
                    numFaces: !!numFaces,
                    artType: !!artType,
                    artSize: !!artSize,
                    totalPrice: !!totalPrice
                });
                return;
            }

            function calculatePrice() {
                const faces = parseInt(numFaces.value) || 0;
                const type = artType.value;
                const size = artSize.value;

                console.log('Calculating price with:', { faces, type, size });

                // Pricing logic in Rupees
                const basePrices = {
                    normal_sketch: 3000,
                    watercolor: 6000,
                    realistic: 9000
                };
                const facePrice = 2000; // ₹2000 per additional face
                const sizeMultipliers = {
                    A5: 0.7,
                    A4: 1,
                    A3: 1.4,
                    A2: 1.8
                };

                let price = basePrices[type] || 0;
                console.log('Base price:', price);

                if (faces > 1) {
                    const additionalFacesCost = (faces - 1) * facePrice;
                    price += additionalFacesCost;
                    console.log('Additional faces cost:', additionalFacesCost, 'New price:', price);
                }

                if (size) {
                    const multiplier = sizeMultipliers[size] || 1.0;
                    price *= multiplier;
                    console.log('Size multiplier:', multiplier, 'New price:', price);
                }

                totalPrice.value = price.toFixed(2);
                console.log('Total price set to:', totalPrice.value);
            }

            // Set up event listeners
            numFaces.addEventListener('change', () => {
                console.log('numFaces changed:', numFaces.value);
                calculatePrice();
            });
            artType.addEventListener('change', () => {
                console.log('artType changed:', artType.value);
                calculatePrice();
            });
            artSize.addEventListener('change', () => {
                console.log('artSize changed:', artSize.value);
                calculatePrice();
            });

            // Calculate initial price on load
            console.log('Calculating initial price on load');
            calculatePrice();

            // Client-side validation for total price
            form.addEventListener('submit', function(event) {
                const price = parseFloat(totalPrice.value);
                if (price <= 0) {
                    event.preventDefault();
                    alert('Total price must be greater than 0. Please check your selections.');
                }
            });
        });
    </script>
</body>
</html>