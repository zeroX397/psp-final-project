<?php
include "../../connection.php";

// Fetch all users
$sql = "SELECT * FROM users WHERE deleted_at IS NULL";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "No users found.";
}

$conn->close();
?>