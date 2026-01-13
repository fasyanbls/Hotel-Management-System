<?php
$host = "localhost:3307"; 
$username = "root";
$password = "";
$database = "final_database";    

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>
