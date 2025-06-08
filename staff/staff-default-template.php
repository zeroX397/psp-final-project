<!-- This is a template file for, well, template of course. Top navbar, db connection, layout, etc. 
 Please copy this file and remove this comment if you want to create a new page. -->
<?php 
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /login.php");
    exit();
}

?>