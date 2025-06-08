<?php
session_start();
require_once("../../../connection.php");

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Get POST data
$product_id = $_POST['product_id'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;

// Fetch current image (for keeping it if no new image is uploaded)
$stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Product not found.");
}

$current_product = $result->fetch_assoc();
$current_image = $current_product['image'];

$stmt->close();

// Handle new image upload if provided
$upload_dir = "../../../assets/img/products/";
$image_name = $current_image;

if (!empty($_FILES['image']['name'])) {
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_name = basename($_FILES['image']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_ext, $allowed_exts)) {
        die("Invalid file type. Only JPG, PNG, and GIF allowed.");
    }

    $image_name = uniqid("prod_") . '.' . $file_ext;
    move_uploaded_file($file_tmp, $upload_dir . $image_name);
}

// Update the product
$stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE product_id = ?");
$stmt->bind_param("ssdssi", $name, $description, $price, $stock, $image_name, $product_id);

if ($stmt->execute()) {
    header("Location: /admin/products/index.php?success=product_updated");
} else {
    echo "Error updating product: " . $stmt->error;
}

$stmt->close();
$conn->close();
