<?php
session_start();

if (!isset($_POST['product_id'])) {
    header("Location: /shop.php");
    exit();
}

$productId = (int) $_POST['product_id'];
$qty = max(1, (int) ($_POST['qty'] ?? 1));

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;

header("Location: /user/cart.php");
exit();
?>