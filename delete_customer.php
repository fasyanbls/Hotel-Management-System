<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Hapus dari database
    $query = "DELETE FROM customers WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: customers.php?message=deleted");
        exit();
    } else {
        echo "Failed to delete the data.";
    }
} else {
    echo "ID not found.";
}
?>
