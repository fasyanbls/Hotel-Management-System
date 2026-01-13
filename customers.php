<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Customer Data</h2>
    <a href="add_customer.php" class="btn btn-primary">+ Add Customer</a>
</div>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM customers");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>
                        <a href='edit_customer.php?id={$row['id']}' class='btn btn-sm btn-warning me-1'>Edit</a>
                        <a href='delete_customer.php?id={$row['id']}' 
                        class='btn btn-sm btn-danger' 
                        onclick=\"return confirm('Are you sure want to delete this customer?')\">
                        Delete
                        </a>
                    </td>
                  </tr>";
                  
        }
        ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>

