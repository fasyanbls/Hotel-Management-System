<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'customer_nav.php';

$customer_id = $_SESSION['customer_id'];

// Get customer information
$customer_query = "SELECT * FROM customers WHERE id = $customer_id";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

// Get customer's reservations
$reservations_query = "SELECT r.*, rm.type, rm.price_per_night 
                      FROM reservations r 
                      JOIN rooms rm ON r.room_id = rm.id 
                      WHERE r.user_id = $customer_id 
                      ORDER BY r.check_in_date DESC";
$reservations_result = mysqli_query($conn, $reservations_query);
$reservations_count = mysqli_num_rows($reservations_result);

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
$total_paid_query = "SELECT SUM(p.amount_paid) as total FROM payments p 
                    JOIN reservations r ON p.reservation_id = r.id 
                    WHERE r.user_id = $customer_id AND p.status = 'Paid'";
$total_paid_result = mysqli_query($conn, $total_paid_query);
$total_paid = mysqli_fetch_assoc($total_paid_result)['total'] ?? 0;

// Get pending payments
$pending_payments_query = "SELECT COUNT(*) as count FROM payments p 
                         JOIN reservations r ON p.reservation_id = r.id 
                         WHERE r.user_id = $customer_id AND p.status = 'Pending'";
$pending_payments_result = mysqli_query($conn, $pending_payments_query);
$pending_payments_count = mysqli_fetch_assoc($pending_payments_result)['count'] ?? 0;
?>

<div class="container mt-4">
    <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['customer_name']) ?>!</h2>
    
    <?php if ($pending_payments_count > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Attention!</strong> You have <?= $pending_payments_count ?> pending payment(s).
            <a href="my_payments.php" class="btn btn-sm btn-outline-dark ms-2">View Payments</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">My Reservations</h5>
                    <p class="card-text fs-4 fw-bold"><?= $reservations_count ?></p>
                    <a href="my_reservations.php" class="text-white text-decoration-none">View Details →</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Paid</h5>
                    <p class="card-text fs-4 fw-bold">Rp <?= number_format($total_paid, 0, ',', '.') ?></p>
                    <a href="my_payments.php" class="text-white text-decoration-none">View Details →</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <p class="card-text fs-4 fw-bold"><?= $pending_payments_count ?></p>
                    <a href="my_payments.php" class="text-white text-decoration-none">View Details →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Reservations</h5>
                </div>
                <div class="card-body">
                    <?php if ($reservations_count > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Room</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    while ($reservation = mysqli_fetch_assoc($reservations_result) and $count < 5): 
                                        $count++;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['type']) ?></td>
                                            <td><?= date('d M Y', strtotime($reservation['check_in_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($reservation['check_out_date'])) ?></td>
                                            <td>
                                                <?php 
                                                // Check if payment exists and is paid
                                                $payment_check = mysqli_query($conn, "SELECT status FROM payments WHERE reservation_id = {$reservation['id']}");
                                                if (mysqli_num_rows($payment_check) > 0) {
                                                    $payment = mysqli_fetch_assoc($payment_check);
                                                    echo $payment['status'] == 'Paid' ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning">Pending</span>';
                                                } else {
                                                    echo '<span class="badge bg-danger">Unpaid</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($reservations_count > 5): ?>
                            <div class="text-center mt-3">
                                <a href="my_reservations.php" class="btn btn-sm btn-outline-primary">View All Reservations</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">You don't have any reservations yet.</div>
                        <div class="text-center">
                            <a href="view_rooms.php" class="btn btn-primary">Book a Room</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <?php if ($payments_count > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Room</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = 0;
                                    while ($payment = mysqli_fetch_assoc($payments_result) and $count < 5): 
                                        $count++;
                                    ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                                            <td><?= htmlspecialchars($payment['type']) ?></td>
                                            <td>Rp <?= number_format($payment['amount_paid'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($payment['status'] == 'Paid'): ?>
                                                    <span class="badge bg-success">Paid</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($payments_count > 5): ?>
                            <div class="text-center mt-3">
                                <a href="my_payments.php" class="btn btn-sm btn-outline-primary">View All Payments</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">You don't have any payments yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="view_rooms.php" class="btn btn-outline-primary w-100">🛏 Book a Room</a>
        </div>
        <div class="col-md-4">
            <a href="my_reservations.php" class="btn btn-outline-success w-100">📆 My Reservations</a>
        </div>
        <div class="col-md-4">
            <a href="my_profile.php" class="btn btn-outline-secondary w-100">👤 My Profile</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 