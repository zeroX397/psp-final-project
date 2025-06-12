<?php
session_start();
include "../../connection.php";

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Get POST data
$user_id = $_POST['user_id'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$role = $_POST['role'] ?? 'user';

// Fetch current profile picture
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("User not found.");
}

$current_user = $result->fetch_assoc();
$current_picture = $current_user['profile_picture'];

$stmt->close();

// Handle new profile picture if provided
$upload_dir = "../../../uploads/";
$profile_picture_name = $current_picture;

if (!empty($_FILES['profile_picture']['name'])) {
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_name = basename($_FILES['profile_picture']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_ext, $allowed_exts)) {
        die("Invalid file type. Only JPG, PNG, and GIF allowed.");
    }

    $profile_picture_name = uniqid("pp_") . '.' . $file_ext;
    move_uploaded_file($file_tmp, $upload_dir . $profile_picture_name);

    // Optionally delete old picture
    if (!empty($current_picture) && file_exists($upload_dir . $current_picture)) {
        unlink($upload_dir . $current_picture);
    }
}

// Update user data
$stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, profile_picture = ? WHERE user_id = ?");
$stmt->bind_param("ssssi", $username, $email, $role, $profile_picture_name, $user_id);

if ($stmt->execute()) {
    header("Location: /admin/users/index.php?success=user_updated");
} else {
    echo "Error updating user: " . $stmt->error;
}

$stmt->close();
$conn->close();
