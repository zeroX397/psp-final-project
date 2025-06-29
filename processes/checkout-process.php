<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart']) || !isset($_POST['address'])) {
    header("Location: /user/cart.php");
    exit();
}

$userId = $_SESSION['user_id'];
$address = trim($_POST['address']);
$cart = $_SESSION['cart'];

function getProducts($conn, $cart)
{
    $data = [];
    $ids = array_keys($cart);
    if ($ids) {
        $qMarks = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT product_id, price, stock FROM products WHERE product_id IN ($qMarks)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[$row['product_id']] = $row;
        }
        $stmt->close();
    }
    return $data;
}

$conn->begin_transaction();
try {
    $products = getProducts($conn, $cart);
    $totalAmount = 0;

    foreach ($cart as $pid => $qty) {
        if (!isset($products[$pid])) {
            throw new Exception("Invalid product ID: $pid");
        }
        if ($qty > $products[$pid]['stock']) {
            throw new Exception("Insufficient stock for product ID: $pid");
        }
        $totalAmount += $qty * $products[$pid]['price'];
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, address, total_amount) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $userId, $address, $totalAmount);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    $insertTransaction = $conn->prepare("INSERT INTO transactions (order_id, payment_date, amount_paid) VALUES (?, NOW(), ?)");
    $insertTransaction->bind_param("id", $orderId, $totalAmount);
    $insertTransaction->execute();
    $insertTransaction->close();

    $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
    $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");

    foreach ($cart as $pid => $qty) {
        $price = $products[$pid]['price'];
        $stmt->bind_param("iiid", $orderId, $pid, $qty, $price);
        $stmt->execute();

        $updateStock->bind_param("ii", $qty, $pid);
        $updateStock->execute();
    }

    $stmt->close();
    $updateStock->close();
    $conn->commit();

    $_SESSION['cart'] = [];
    header("Location: order-success.php?order_id=$orderId");
} catch (Exception $e) {
    $conn->rollback();
    echo "Checkout failed: " . htmlspecialchars($e->getMessage());
}
?>