<?php
session_start();
require_once("../connection.php");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT user_id, username, password, role, profile_picture FROM users WHERE username = ?");
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
        $_SESSION['profile_picture'] = $user['profile_picture'] ?? 'default.png';
        $_SESSION['LAST_ACTIVITY'] = time();

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin");
        } elseif ($user['role'] === 'staff') {
            header("Location: ../staff");
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
$conn->close();
exit();