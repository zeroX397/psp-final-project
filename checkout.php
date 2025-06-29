<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: /user/cart.php');
    exit();
}

$cart = $_SESSION['cart'];

function getCartItemsFromDB($conn, $cart)
{
    $productData = [];
    $ids = array_keys($cart);
    if (count($ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT product_id, name, price, stock FROM products WHERE product_id IN ($placeholders) AND deleted_at IS NULL");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productData[$row['product_id']] = $row;
        }
        $stmt->close();
    }
    return $productData;
}

$cartProducts = getCartItemsFromDB($conn, $cart);
$totalAmount = 0;
foreach ($cart as $id => $qty) {
    if (isset($cartProducts[$id])) {
        $totalAmount += $qty * $cartProducts[$id]['price'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Checkout | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-5">
    <h1>Checkout</h1>
    <form method="POST" action="/processes/checkout-process.php">
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <textarea name="address" id="address" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Place Order</button>
    </form>
</body>

</html>