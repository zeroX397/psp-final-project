<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = $_SESSION['user_id'];

// Ambil data order + validasi apakah milik user
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.address, o.status, o.total_amount, t.payment_date
    FROM orders o
    LEFT JOIN transactions t ON o.order_id = t.order_id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$orderResult = $stmt->get_result();
$order = $orderResult->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "Order not found or access denied.";
    exit();
}

// Ambil detail produk dalam order ini
$stmt = $conn->prepare("
    SELECT od.product_id, od.quantity, od.price_at_purchase, p.name
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$detailsResult = $stmt->get_result();
$details = $detailsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Order #<?= $order['order_id'] ?> Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Peaceful World</a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/cart.php">My Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about.php">About Us</a>
                    </li>
                    <!-- Check whether user is admin or not to show admin dropdown -->
                    <?php if (isset($_SESSION['user_id']) || in_array($_SESSION['role'], ['staff', 'admin'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Admin Panel
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin">Admin Panel</a></li>
                                <li><a class="dropdown-item" href="/admin/products">Products</a></li>
                                <li><a class="dropdown-item" href="/admin/users">Users</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/staff">Staff Area</a></li>
                                <li><a class="dropdown-item" href="/staff/orders.php">All Orders</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/user">
                        <button class="btn btn-primary">Profile</button>
                    </a>
                    <a href="../logout.php">
                        <button class="btn btn-danger">Logout</button>
                    </a>
                <?php else: ?>
                    <a href="/register.php">
                        <button class="btn btn-primary">Register</button>
                    </a>
                    <a href="/login.php">
                        <button class="btn btn-default">Login</button>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container py-4">

        <h2>Order #<?= $order['order_id'] ?></h2>
        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
        <p><strong>Payment Method:</strong> <?= $order['status'] ?? 'Pending' ?></p>
        <p><strong>Paid At:</strong> <?= $order['payment_date'] ?? '-' ?></p>
        <p><strong>Total:</strong> US$ <?= number_format($order['total_amount'], 2) ?></p>

        <h4 class="mt-4">Items</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price per item</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>US$ <?= number_format($item['price_at_purchase'], 2) ?></td>
                        <td>US$ <?= number_format($item['quantity'] * $item['price_at_purchase'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="/user/orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
</body>

</html>