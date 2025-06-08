<?php
session_start();
require_once("../../connection.php");

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Validate ID param
$product_id = $_GET['id'] ?? '';
if (!$product_id) {
    die("Product ID is required.");
}

// Fetch current image (optional: delete image file too if you want)
$stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
$current_image = $product['image'];

$stmt->close();

// Optionally delete the image file
$image_path = "../../assets/img/products/" . $current_image;
if (file_exists($image_path)) {
    unlink($image_path);
}

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    header("Location: /admin/products/index.php?success=product_deleted");
} else {
    echo "Error deleting product: " . $stmt->error;
}

$stmt->close();
$conn->close();
