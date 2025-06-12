<?php
include '../../../connection.php';


$query = "SELECT user_id, username, email, role, profile_picture, created_at FROM users";
$result = mysqli_query($conn, $query);

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>
