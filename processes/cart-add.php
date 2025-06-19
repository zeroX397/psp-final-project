<?php
// cart-add.php
session_start();
require_once '../connection.php';

if (!isset($_POST['product_id']) || !ctype_digit($_POST['product_id'])) {
    die('Bad request.');
}
$product_id = (int) $_POST['product_id'];
$qty = isset($_POST['qty']) && ctype_digit($_POST['qty'])
    ? max(1, (int) $_POST['qty'])   // enforce minimum 1
    : 1;                           // default for shop.php

// --- 1. Verify product & stock ---
$stmt = $conn->prepare(
    "SELECT name, price, stock FROM products
     WHERE product_id = ? AND deleted_at IS NULL LIMIT 1"
);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    die('Product not found.');
}
$product = $res->fetch_assoc();
if ($product['stock'] < $qty) {
    die('Not enough stock.');
}
$stmt->close();

// --- 2. Store in session + DB if logged in ---
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $qty;
} else {
    $_SESSION['cart'][$product_id] = $qty;
}

if (isset($_SESSION['user_id'])) {
    // Update database cart
    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
    $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $qty);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// --- 3. Redirect back nicely ---
$back = $_SERVER['HTTP_REFERER'] ?? 'shop.php';
header("Location: $back?added=1");
exit;
