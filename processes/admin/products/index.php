<?php
include("../../connection.php");

// Fetch all products
$sql = "SELECT * FROM products WHERE deleted_at IS NULL";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

?>
