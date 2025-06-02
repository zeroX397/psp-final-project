<!-- This is a template file for, well, template of course. Top navbar, db connection, layout, etc. 
 Please copy this file and remove this comment if you want to create a new page. -->
<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

?>