<?php
session_start();
require_once("../../connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

$user_id = $_GET['id'] ?? '';
if (!$user_id) {
    die("User ID is required.");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Admin | Edit User | Peaceful World</title>
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
                    <a href="/logout.php">
                        <button class="btn btn-danger">Logout</button>
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <!-- Body edit-user form -->
    <div class="container mt-5">
        <h1>Edit User</h1>
        <a href="/admin/users/index.php" class="btn btn-secondary mb-3">← Back to Users List</a>

        <form action="/processes/admin/users/edit.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

            <div class="form-group mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control"
                    value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                    required>
            </div>
            <div class="form-group mb-3">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label>Current Profile Picture:</label><br>
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="../../assets/img/products/<?= htmlspecialchars($user['profile_picture']) ?>" width="100"
                        alt="No Profile Picture"><br><br>
                <?php else: ?>
                    No image<br><br>
                <?php endif; ?>
                <label>Change Profile Picture (optional)</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>

</body>

</html>


</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

</html>