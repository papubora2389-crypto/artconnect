<?php
session_start();
require_once 'config/db.php';

// Check if the customer is logged in
if (!isset($_SESSION['customer_email'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your wishlist.']);
    exit;
}

$customer_email = $_SESSION['customer_email'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$painting_id = isset($_POST['painting_id']) ? (int)$_POST['painting_id'] : 0;

if ($painting_id <= 0 || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    if ($action === 'add') {
        // Check if the painting exists
        $stmt = $conn->prepare("SELECT id FROM gallery WHERE id = ? AND type = 'painting'");
        $stmt->execute([$painting_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Painting not found.']);
            exit;
        }

        // Add to wishlist
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (customer_email, painting_id) VALUES (?, ?)");
        $stmt->execute([$customer_email, $painting_id]);
        echo json_encode(['success' => true, 'message' => 'Added to wishlist.']);
    } elseif ($action === 'remove') {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE customer_email = ? AND painting_id = ?");
        $stmt->execute([$customer_email, $painting_id]);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
    }
} catch (PDOException $e) {
    // Log the error for debugging (you can replace this with a proper logging mechanism)
    error_log("Wishlist action error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>