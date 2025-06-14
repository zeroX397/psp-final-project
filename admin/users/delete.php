<?php
session_start();
require_once("../../connection.php");

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Validate ID param
$user_id = $_GET['id'] ?? '';
if (!$user_id) {
    die("User ID is required.");
}

// Prevent deleting yourself (safety!)
if ($_SESSION['user_id'] == $user_id) {
    die("You cannot delete your own account.");
}

// Soft delete the user
$stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: /admin/users/index.php?success=user_deleted");
} else {
    echo "Error deleting user: " . $stmt->error;
}

$stmt->close();
$conn->close();
