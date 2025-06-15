<?php

include 'connection.php'; 


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function addToCart($productId, $productName, $productPrice, $quantity = 1) {
    if (!is_numeric($productId) || !is_numeric($productPrice) || !is_numeric($quantity) || $quantity <= 0) {
        return false;
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $productName,
            'price' => (float)$productPrice,
            'quantity' => (int)$quantity
        ];
    }
    return true;
}

function updateCartItem($productId, $newQuantity) {
    if (!isset($_SESSION['cart'][$productId])) {
        return false;
    }
    if (!is_numeric($newQuantity) || $newQuantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId]['quantity'] = (int)$newQuantity;
    }
    return true;
}

function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }
    return false;
}

function clearCart() {
    $_SESSION['cart'] = [];
}

function getCartItems() {
    return $_SESSION['cart'];
}

function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function getCartTotalPrice() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

if (isset($_POST['action'])) {
    $productId = $_POST['product_id'] ?? null;

    if ($_POST['action'] === 'add_to_cart') {
        $productName = $_POST['product_name'] ?? null;
        $productPrice = $_POST['product_price'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        if ($productId && $productName && $productPrice) {
            if (addToCart($productId, $productName, $productPrice, $quantity)) {
                $_SESSION['message'] = "Produk berhasil ditambahkan ke keranjang!";
            } else {
                $_SESSION['error'] = "Gagal menambahkan produk ke keranjang. Input tidak valid.";
            }
        } else {
            $_SESSION['error'] = "Gagal menambahkan produk. Data tidak lengkap.";
        }
    } elseif ($productId) {
        if ($_POST['action'] === 'update_quantity') {
            $newQuantity = $_POST['quantity'] ?? 1;
            if (updateCartItem($productId, $newQuantity)) {
                $_SESSION['message'] = "Kuantitas produk berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Gagal memperbarui kuantitas produk.";
            }
        } elseif ($_POST['action'] === 'remove_item') {
            if (removeFromCart($productId)) {
                $_SESSION['message'] = "Produk berhasil dihapus dari keranjang.";
            } else {
                $_SESSION['error'] = "Gagal menghapus produk dari keranjang.";
            }
        }
    } elseif ($_POST['action'] === 'clear_cart') { // Aksi clear cart
        clearCart();
        $_SESSION['message'] = "Keranjang belanja berhasil dikosongkan.";
    } else {
         $_SESSION['error'] = "Aksi gagal. ID produk tidak ditemukan atau aksi tidak valid.";
    }

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'cart.php'));
    exit();
}

$message = $_SESSION['message'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['message']);
unset($_SESSION['error']);

$cartItems = getCartItems();
$totalPrice = getCartTotalPrice();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="./">Peaceful World</a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="./">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">My Cart (<?php echo getCartItemCount(); ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Admin Panel
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin/">Admin Panel</a></li>
                                <li><a class="dropdown-item" href="products/">Products</a></li>
                                <li><a class="dropdown-item" href="users">Users</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">
                        <button class="btn btn-primary">Profile</button>
                    </a>
                    <a href="logout.php">
                        <button class="btn btn-danger">Logout</button>
                    </a>
                <?php else: ?>
                    <a href="register.php">
                        <button class="btn btn-primary">Register</button>
                    </a>
                    <a href="login.php">
                        <button class="btn btn-default">Login</button>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <main class="container my-4">
        <?php if ($message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <h2>My Cart</h2>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" role="alert">
                Your cart is empty. <a href="shop.php">Start shopping now!</a>
            </div>
        <?php else: ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $productId => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="cart.php" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
                                    <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="0" class="form-control me-2" style="width: 70px;">
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            <td>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total</strong></td>
                        <td colspan="2"><strong>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-end mt-3">
                <form action="cart.php" method="POST" class="me-2">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to clear your cart?');">Clear Cart</button>
                </form>
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>