<?php
session_start();
include '../../connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | Peaceful World Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Peaceful World</a>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/cart.php">My Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about-us.php">About Us</a></li>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/profile.php"><button class="btn btn-primary">Profile</button></a>
                    <a href="/logout.php"><button class="btn btn-danger">Logout</button></a>
                <?php else: ?>
                    <a href="/register.php"><button class="btn btn-primary">Register</button></a>
                    <a href="/login.php"><button class="btn btn-default">Login</button></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- User Table -->
    <div class="container mt-5">
        <h2>List of Users</h2>
        <table class="table table-bordered" id="userTable">
            <thead class="table-light">
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Profile Picture</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Bootstrap & Script -->
    <script>
        fetch('../../processes/admin/db/get_user.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector("#userTable tbody");
                data.forEach(user => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td>${user.username}</td>
                        <td>${user.email}</td>
                        <td>${user.role}</td>
                        <td><img src="/assets/profile_pictures/${user.profile_picture}" width="50" height="50" class="rounded-circle" alt="profile picture"></td>
                        <td>${user.created_at}</td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error("Failed to fetch user data:", error);
            });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
</body>

</html>
