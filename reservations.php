<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

$search = $_GET['search'] ?? '';
$filter_query = "";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $filter_query = "WHERE c.name LIKE '%$search_safe%' 
                    OR r.check_in_date LIKE '%$search_safe%' 
                    OR r.check_out_date LIKE '%$search_safe%' 
                    OR rm.type LIKE '%$search_safe%'";
}

$sql = "SELECT r.id, c.name AS customer_name, r.room_id, rm.type, r.check_in_date, r.check_out_date, p.status as payment_status
        FROM reservations r
        JOIN customers c ON r.user_id = c.id
        JOIN rooms rm ON r.room_id = rm.id
        LEFT JOIN payments p ON r.id = p.reservation_id
        $filter_query
        ORDER BY r.check_in_date DESC";

$reservations = mysqli_query($conn, $sql);
$today = date('Y-m-d');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Reservation Records</h2>
        <a href="admin_add_reservation.php" class="btn btn-sm btn-primary">+ Add Reservation</a>
    </div>

    <form class="mb-4" method="GET" action="">
        <div class="input-group shadow-sm">
            <input type="text" name="search" class="form-control" placeholder="Search by customer / date / room type" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered align-middle text-center">
            <thead class="table-primary">
                <tr>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Payment Status</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($reservations) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($reservations)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['room_id']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= date('d M Y', strtotime($row['check_in_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($row['check_out_date'])) ?></td>
                            <td>
                                <?php
                                if ($row['payment_status'] == 'Paid') {
                                    echo "<span class='badge bg-success'>Paid</span>";
                                } else {
                                    echo "<span class='badge bg-danger'>Unpaid</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="admin_receipt.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" target="_blank">Receipt</a>
                            </td>
                            <td>
                                <a href="delete_reservation.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this reservation?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-muted">No reservation data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
