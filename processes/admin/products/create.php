<?php
session_start();
require_once("../../../connection.php");

// Security check: only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Get POST data
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;

// Handle file upload
$upload_dir = "../../../assets/img/products/";
$image_name = null;

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
} else {
    die("Image upload failed. Image is required.");
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdss", $name, $description, $price, $stock, $image_name);

if ($stmt->execute()) {
    header("Location: /admin/products/index.php?success=product_added");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
