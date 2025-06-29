<?php
session_start();
require_once 'connection.php';   // adjust path if needed

// 1. Validate & sanitise the id parameter
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('Invalid product ID.');
}
$product_id = (int) $_GET['id'];

// 2. Fetch the product (skip soft-deleted ones)
$stmt = $conn->prepare(
    "SELECT product_id, name, description, price, stock, image
     FROM products
     WHERE product_id = ? AND deleted_at IS NULL
     LIMIT 1"
);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die('Product not found.');
}
$product = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> ... | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
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
                        <a class="nav-link" href="/about.php">About Us</a>
                    </li>
                    <!-- Check whether user is admin or not to show admin dropdown -->
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'])): ?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="/user/cart.php">My Cart</a>
                    </li>
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

    <!-- Product Detail Content -->
    <div class="container py-5">
        <div class="row">
            <!-- Product image -->
            <div class="col-md-6 mb-4">
                <img src="/assets/img/products/<?= htmlspecialchars($product['image']) ?>"
                    class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <!-- Product details -->
            <div class="col-md-6">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <p class="lead fw-bold">$<?= number_format($product['price'], 2) ?></p>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <p class="text-muted">Stock: <?= (int) $product['stock'] ?></p>

                <!-- Add-to-Cart form -->
                <form action="/processes/cart-add.php" method="POST" class="d-inline">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <!-- Quantity selector -->
                    <div class="input-group mb-3" style="max-width: 180px;">
                        <button class="btn btn-outline-secondary" type="button" id="qty-minus">−</button>
                        <input type="number" name="qty" id="qty-input" class="form-control text-center" value="1"
                            min="1" max="<?= (int) $product['stock'] ?>">
                        <button class="btn btn-outline-secondary" type="button" id="qty-plus">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary" <?= $product['stock'] < 1 ? 'disabled' : '' ?>>
                        Add to Cart
                    </button>
                </form>
                <a href="shop.php" class="btn btn-outline-secondary ms-2">← Back to Shop</a>
            </div>
        </div>
    </div>


</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
<script>
    (function () {
        const max = <?= (int) $product['stock'] ?>;
        const input = document.getElementById('qty-input');
        document.getElementById('qty-minus').onclick = () => {
            input.value = Math.max(1, parseInt(input.value) - 1);
        };
        document.getElementById('qty-plus').onclick = () => {
            input.value = Math.min(max, parseInt(input.value) + 1);
        };
        input.oninput = () => {
            // keep value within bounds even on manual typing
            let v = parseInt(input.value) || 1;
            input.value = Math.max(1, Math.min(max, v));
        };
    })();
</script>

</html>