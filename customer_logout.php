<?php
session_start();

// Unset all customer session variables
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

// Redirect to home page
header("Location: index.php");
exit();
?> 