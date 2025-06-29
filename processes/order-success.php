<?php
/**
 * order-success.php
 *  Menampilkan ringkasan order setelah checkout.
 *  - Verifikasi user login & kepemilikan order
 *  - Tampilkan alamat, status, daftar item & total
 */

session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($orderId <= 0) {
    header('Location: /shop.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];

// --- Ambil order --------------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT order_id, address, order_date, status, total_amount
      FROM orders
     WHERE order_id = ? AND user_id = ? AND deleted_at IS NULL
     LIMIT 1
");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo '<h3 class="text-danger text-center mt-5">Order not found or access denied</h3>';
    exit();
}

// --- Ambil rincian item -------------------------------------------------------------
$stmt = $conn->prepare("
    SELECT od.quantity,
           od.price_at_purchase,
           p.name
      FROM order_details od
      JOIN products p ON p.product_id = od.product_id
     WHERE od.order_id = ?
     ORDER BY od.order_detail_id
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= htmlspecialchars($orderId) ?> | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <div class="text-center mb-4">
        <h1>Thank you for your purchase! 🎉</h1>
        <h4>Order ID: #<?= htmlspecialchars($orderId) ?></h4>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-bold">Shipping & Order Info</div>
        <div class="card-body">
            <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['address'])) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
        </div>
    </div>

    <h3 class="mb-3">Items</h3>
    <table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Product</th>
                <th class="text-end">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $row): 
                $subtotal = $row['price_at_purchase'] * $row['quantity'];
            ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td class="text-end">US$ <?= number_format((float)$row['price_at_purchase'], 2, '.', ',') ?></td>
                <td class="text-center"><?= $row['quantity'] ?></td>
                <td class="text-end">US$ <?= number_format((float)$subtotal, 2, '.', ',') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">US$ <?= number_format((float)$order['total_amount'], 2, '.', ',') ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-between mt-4">
        <a href="/shop.php" class="btn btn-primary">Continue Shopping</a>
        <a href="/user/orders.php" class="btn btn-secondary">View My Orders</a>
    </div>
</body>
</html>