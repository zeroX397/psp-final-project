<?php
session_start();
require_once("../connection.php");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        ini_set('session.gc_maxlifetime', 1800); // Timeout 30 minutes inactivity
        session_regenerate_id(true);
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['LAST_ACTIVITY'] = time();

        // Load cart from database if user is logged in
        loadCartFromDB($conn, $user['user_id']);

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin");
        } elseif ($user['role'] === 'staff') {
            header("Location: /staff");
        } else {
            header("Location: ../user/");
        }
        exit();
    } else {
        // Wrong password
        header("Location: /login.php?error=wrong_password");
        exit();
    }
} else {
    // User not found
    header("Location: /login.php?error=user_not_found");
}

$stmt->close();

function loadCartFromDB($conn, $userId) {
    $_SESSION['cart'] = [];
    $stmt = $conn->prepare("SELECT product_id, quantity FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
    $stmt->close();
}

$conn->close();
exit();