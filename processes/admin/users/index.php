<?php
include "../../connection.php";

// Fetch all users
$sql = "SELECT * FROM users";
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