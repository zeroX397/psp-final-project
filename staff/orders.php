<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: /login.php");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = $_POST['status'];
    $approvedBy = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE orders SET status = ?, approved_by = ? WHERE order_id = ?");
    $stmt->bind_param("sii", $newStatus, $approvedBy, $orderId);
    $stmt->execute();
    $stmt->close();

    header("Location: orders.php");
    exit();
}

// Fetch all orders
$result = $conn->query("
    SELECT o.*, u.username AS customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");

function getEnumValues($conn, $table, $column)
{
    $sql = "SHOW COLUMNS FROM `$table` WHERE Field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $column);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $type = $result['Type'] ?? '';
    preg_match("/^enum\((.*)\)$/", $type, $matches);
    $vals = [];

    if (!empty($matches[1])) {
        $vals = array_map(function ($v) {
            return trim($v, "'");
        }, explode(",", $matches[1]));
    }

    return $vals;
}

$statusOptions = getEnumValues($conn, 'orders', 'status');


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
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

    <div class="container-fluid mt-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>User</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= $row['order_date'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>$<?= number_format($row['total_amount'], 2) ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <select name="status" class="form-select me-2">
                                    <?php foreach ($statusOptions as $status): ?>
                                        <option value="<?= $status ?>" <?= $row['status'] === $status ? 'selected' : '' ?>>
                                            <?= ucfirst($status) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td><a href="/order-pdf.php?id=<?= $row['order_id'] ?>" class="btn btn-sm btn-outline-danger"><i
                                    class="bi bi-file-earmark-pdf"></i>PDF</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
        </script>
</body>

</html>