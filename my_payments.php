<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'customer_nav.php';

// Get customer ID from customers table
$customer_email = $_SESSION['customer_email'];
$customer_query = "SELECT id FROM customers WHERE email = '$customer_email'";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

if (!$customer) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $customer['id'];

// Get customer's payments
$payments_query = "SELECT p.*, r.check_in_date, r.check_out_date, rm.type 
                  FROM payments p 
                  JOIN reservations r ON p.reservation_id = r.id 
                  JOIN rooms rm ON r.room_id = rm.id 
                  WHERE r.user_id = $customer_id 
                  ORDER BY p.payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);
$payments_count = mysqli_num_rows($payments_result);

// Get total amount paid
$total_paid_query = "SELECT COALESCE(SUM(p.amount_paid), 0) as total 
                    FROM payments p 
                    JOIN reservations r ON p.reservation_id = r.id 
                    WHERE r.user_id = $customer_id AND p.status = 'Paid'";
$total_paid_result = mysqli_query($conn, $total_paid_query);
$total_paid = mysqli_fetch_assoc($total_paid_result)['total'];

// Get pending payments
$pending_payments_query = "SELECT COUNT(*) as count FROM payments p 
                         JOIN reservations r ON p.reservation_id = r.id 
                         WHERE r.user_id = $customer_id AND p.status = 'Pending'";
$pending_payments_result = mysqli_query($conn, $pending_payments_query);
$pending_payments_count = mysqli_fetch_assoc($pending_payments_result)['count'] ?? 0;
?>

<div class="container mt-4">
    <h2 class="mb-4">My Payments</h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Paid</h5>
                    <p class="card-text fs-4 fw-bold">Rp <?= number_format($total_paid, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <p class="card-text fs-4 fw-bold"><?= $pending_payments_count ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($payments_count > 0): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Room</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><?= htmlspecialchars($payment['type']) ?></td>
                                    <td><?= date('d M Y', strtotime($payment['check_in_date'])) ?></td>
                                    <td><?= date('d M Y', strtotime($payment['check_out_date'])) ?></td>
                                    <td>Rp <?= number_format($payment['amount_paid'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                    <td>
                                        <?php if ($payment['status'] == 'Paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['status'] == 'Paid'): ?>
                                            <a href="receipt.php?id=<?= $payment['reservation_id'] ?>" class="btn btn-sm btn-outline-success">Receipt</a>
                                        <?php else: ?>
                                            <a href="update_payment.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-primary">Update</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You don't have any payments yet.
        </div>
        <div class="text-center">
            <a href="my_reservations.php" class="btn btn-primary">View My Reservations</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> 