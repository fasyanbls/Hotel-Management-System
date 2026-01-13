<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: customers.php");
    exit();
}

// Retrieve customer data
$result = mysqli_query($conn, "SELECT * FROM customers WHERE id = $id");
$customer = mysqli_fetch_assoc($result);

if (!$customer) {
    echo "<div class='alert alert-danger'>Customer not found.</div>";
    include 'footer.php';
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    if (!empty($name) && !empty($email)) {
        $update = mysqli_query($conn, "UPDATE customers SET 
            name = '$name',
            email = '$email',
            phone = '$phone'
            WHERE id = $id");
            
        if ($update) {
            header("Location: customers.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Failed to update customer: " . mysqli_error($conn) . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Name and email cannot be empty.</div>";
    }
}
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Edit Customer</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>">
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button> <br>
                    <a href="customers.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
