<!-- This is a template file for, well, template of course. Top navbar, db connection, layout, etc. 
 Please copy this file and remove this comment if you want to create a new page. -->
<?php
include 'connection.php';
session_start();
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
                        <a class="nav-link" href="cart.php">My Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <!-- Check whether user is admin or not to show admin dropdown -->
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



</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

</html>