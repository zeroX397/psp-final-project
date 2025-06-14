<?php
include 'connection.php';
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Start Shopping | Peaceful World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href=>Peaceful World</a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="./">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">My Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
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
    
<!-- Main Content Area with Dummy Data -->
    <div class="container mt-4 mb-5">
        <h1 class="text-center mb-4">Welcome to Our Dummy Shop!</h1>
        <p class="text-center lead">Explore a collection of placeholder products generated for demonstration purposes.</p>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            // Loop to generate 6 dummy product cards
            for ($i = 1; $i <= 6; $i++) {
                $productId = 100 + $i;
                $productTitle = "Product Name " . $i;
                $productPrice = "$" . rand(15, 150) . ".00";
                $productDescription = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ";
                if ($i % 2 == 0) {
                    $productDescription .= "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";
                }
            ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <img src="https://picsum.photos/id/<?php echo $productId; ?>/400/200" class="card-img-top" alt="Product Image <?php echo $i; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $productTitle; ?></h5>
                            <p class="card-text text-muted">Category <?php echo chr(64 + ($i % 3) + 1); // A, B, C etc. ?></p>
                            <p class="card-text flex-grow-1"><?php echo $productDescription; ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold fs-5"><?php echo $productPrice; ?></span>
                                <a href="#" class="btn btn-primary">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

    <!-- Body Shop -->

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

</html>
