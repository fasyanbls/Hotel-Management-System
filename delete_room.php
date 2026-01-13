<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM rooms WHERE id = $id");
header('Location: rooms.php');
exit;
?>
