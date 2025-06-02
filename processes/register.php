<?php
session_start();
require_once("../connection.php");

// Validate input
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$repeat_password = $_POST['repeat_password'] ?? '';

if ($password !== $repeat_password) {
    die("Passwords do not match.");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Upload profile picture
$upload_dir = "../assets/img/profilepic/";
$profile_picture_name = null;

if (!empty($_FILES['profile_picture']['name'])) {
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_name = basename($_FILES['profile_picture']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_ext, $allowed_exts)) {
        die("Invalid file type. Only JPG, PNG, and GIF allowed.");
    }

    // Give the file a unique name
    $profile_picture_name = uniqid("pp_") . '.' . $file_ext;
    move_uploaded_file($file_tmp, $upload_dir . $profile_picture_name);
}

// Insert user (default role is 'user')
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, profile_picture) VALUES (?, ?, ?, 'user', ?)");
$stmt->bind_param("ssss", $username, $email, $hashed_password, $profile_picture_name);

if ($stmt->execute()) {
    header("Location: /login.php?success=registered");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
