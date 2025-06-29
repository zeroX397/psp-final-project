<?php
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getCartItemCount()
{
    return array_sum($_SESSION['cart'] ?? []);
}

function getCartItemsFromDB($conn, $cart)
{
    $productData = [];
    $ids = array_keys($cart);
    if (count($ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $conn->prepare("SELECT product_id, name, price, stock, image FROM products WHERE product_id IN ($placeholders) AND deleted_at IS NULL");
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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if ($action === 'add_to_cart' && $productId > 0) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + max(1, $quantity);
    } elseif ($action === 'update_quantity' && $productId > 0) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    } elseif ($action === 'remove_item' && $productId > 0) {
        unset($_SESSION['cart'][$productId]);
    } elseif ($action === 'clear_cart') {
        $_SESSION['cart'] = [];
    }
    header("Location: cart.php");
    exit();
}

$cart = $_SESSION['cart'];
$cartProducts = getCartItemsFromDB($conn, $cart);
$conn->close();

$totalPrice = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Peaceful World</a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/user/cart.php">My Cart</a>
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
                    <a href="/logout.php">
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

    <div class="container py-5">
        <h1 class="mb-4">My Cart</h1>

        <?php if (empty($cartProducts)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="shop.php">Shop now</a>.</div>
        <?php else: ?>
            <table class="table align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartProducts as $id => $p):
                        $qty = $cart[$id];
                        $sub = $qty * $p['price'];
                        $totalPrice += $sub;
                        ?>
                        <tr>
                            <td><img src="/assets/img/products/<?= htmlspecialchars($p['image']) ?>" width="60" class="me-2">
                                <?= htmlspecialchars($p['name']) ?></td>
                            <td>US$ <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td>
                                <form method="POST" action="cart.php" class="d-flex">
                                    <input type="number" name="quantity" value="<?= $qty ?>" min="1" max="<?= $p['stock'] ?>"
                                        class="form-control me-2" style="width: 70px;">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>

                            <td>US$ <?= number_format($sub, 0, ',', '.') ?></td>
                            <td>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <input type="hidden" name="action" value="remove_item">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th colspan="2">US$ <?= number_format($totalPrice, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="d-flex justify-content-between mt-3">
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="btn btn-warning">Clear Cart</button>
                </form>
                <a href="/checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>