<?php
session_start();
require_once 'connection.php';
require_once 'libs/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order || ($order['user_id'] != $userId && !in_array($role, ['staff', 'admin']))) {
    die("Access denied.");
}

$stmt = $conn->prepare("
    SELECT od.*, p.name
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$pdf = new TCPDF();
$pdf->AddPage();

$html = '
<h1>Order #' . $order['order_id'] . '</h1>
<p><strong>Name:</strong> ' . htmlspecialchars($order['username']) . '</p>
<p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
<p><strong>Address:</strong> ' . htmlspecialchars($order['address']) . '</p>
<p><strong>Status:</strong> ' . htmlspecialchars($order['status']) . '</p>
<p><strong>Order Date:</strong> ' . $order['order_date'] . '</p>
<br>
<h3>Items</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
    </tr>';

foreach ($items as $item) {
    $sub = $item['quantity'] * $item['price_at_purchase'];
    $html .= '
    <tr>
        <td>' . htmlspecialchars($item['name']) . '</td>
        <td>' . $item['quantity'] . '</td>
        <td>$' . number_format($item['price_at_purchase'], 2) . '</td>
        <td>$' . number_format($sub, 2) . '</td>
    </tr>';
}

$html .= '</table><br><h4>Total: $' . number_format($order['total_amount'], 2) . '</h4>';

$pdf->writeHTML($html);
$pdf->Output('order_' . $orderId . '.pdf', 'I');
