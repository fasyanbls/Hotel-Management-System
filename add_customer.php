<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php'; //boostrap for styling
include 'config/database.php';
include 'nav.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    // Validasi sederhana
    if (!empty($name) && !empty($email) && !empty($phone)) {
        $query = "INSERT INTO customers (name, email, phone) VALUES ('$name', '$email', '$phone')";
        if (mysqli_query($conn, $query)) {
            header("Location: customers.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Failed to add customer: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>All field must be filled.</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Add Customer</h2>

    <form method="POST" action="" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" id="name" name="name">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" >
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phone" name="phone" >
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="customers.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'footer.php'; ?>