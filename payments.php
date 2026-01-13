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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payment Records</h2>
    </div>

    <!-- Search Form -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by customer / room / status" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total Price</th>
                    <th>Payment Status</th>
                    <th>Payment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT r.*, u.name AS customer_name, rm.type as room_type, 
                                 p.id as payment_id, p.status as payment_status, 
                                 p.payment_date, p.amount_paid
                          FROM reservations r
                          JOIN users u ON r.user_id = u.id
                          JOIN rooms rm ON r.room_id = rm.id
                          LEFT JOIN payments p ON r.id = p.reservation_id";

                if (isset($_GET['search'])) {
                    $search = mysqli_real_escape_string($conn, $_GET['search']);
                    $query .= " WHERE u.name LIKE '%$search%' 
                               OR rm.type LIKE '%$search%' 
                               OR p.status LIKE '%$search%'";
                }

                $query .= " ORDER BY r.check_in_date DESC";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room_type']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['check_in_date'])) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($row['check_out_date'])) . "</td>";
                        echo "<td>Rp " . number_format($row['total_price'], 0, ',', '.') . "</td>";
                        echo "<td>";
                        if ($row['payment_status']) {
                            echo "<span class='badge " . 
                                ($row['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning text-dark') . "'>" .
                                htmlspecialchars($row['payment_status']) . "</span>";
                        } else {
                            echo "<span class='badge bg-danger'>Unpaid</span>";
                        }
                        echo "</td>";
                        echo "<td>" . ($row['payment_date'] ? date('d M Y', strtotime($row['payment_date'])) : '-') . "</td>";
                        echo "<td>";
                        if ($row['payment_id']) {
                            echo "<a href='edit_payment.php?id={$row['payment_id']}' class='btn btn-sm btn-outline-warning'>Edit</a> ";
                            echo "<a href='delete_payment.php?id={$row['payment_id']}' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Delete this payment?')\">Delete</a>";
                        } else {
                            echo "<a href='edit_payment.php?reservation_id={$row['id']}' class='btn btn-sm btn-outline-primary'>Add Payment</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No reservation records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
