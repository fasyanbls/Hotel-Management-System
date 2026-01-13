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

$query = "DELETE FROM payments WHERE id = $id";

if (mysqli_query($conn, $query)) {
    header("Location: payments.php");
} else {
    echo "Failed to delete the payment: " . mysqli_error($conn);
}
?>
