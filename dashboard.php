<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

// Check unpaid payments
$unpaid = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'Pending'");
$unpaid_count = mysqli_fetch_assoc($unpaid)['total'];

// Count data
$reservations = mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations");
$reservation_count = mysqli_fetch_assoc($reservations)['total'];

$customers = mysqli_query($conn, "SELECT COUNT(*) as total FROM customers");
$customer_count = mysqli_fetch_assoc($customers)['total'];

$paid = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'Paid'");
$paid_count = mysqli_fetch_assoc($paid)['total'];

$pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'Pending'");
$pending_count = mysqli_fetch_assoc($pending)['total'];

$total_income = mysqli_query($conn, "SELECT SUM(amount_paid) as total FROM payments WHERE status = 'Paid'");
$income = mysqli_fetch_assoc($total_income)['total'];

$rooms = mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms");
$total_rooms = mysqli_fetch_assoc($rooms)['total'];

$booked_rooms = mysqli_query($conn, "SELECT COUNT(DISTINCT room_id) as total FROM reservations");
$booked = mysqli_fetch_assoc($booked_rooms)['total'];
$available = $total_rooms - $booked;

// Get recent reservations
$recent_reservations = mysqli_query($conn, "SELECT r.*, c.name as customer_name, rm.type as room_type 
                                          FROM reservations r 
                                          JOIN customers c ON r.user_id = c.id 
                                          JOIN rooms rm ON r.room_id = rm.id 
                                          ORDER BY r.check_in_date DESC LIMIT 5");
//This query retrieves the 5 most recent reservation data from the reservations table. 
//The results will be displayed based on the most recent check-in date.

// Get recent payments
$recent_payments = mysqli_query($conn, "SELECT p.*, r.check_in_date, r.check_out_date, c.name as customer_name, rm.type as room_type 
                                      FROM payments p 
                                      JOIN reservations r ON p.reservation_id = r.id 
                                      JOIN customers c ON r.user_id = c.id 
                                      JOIN rooms rm ON r.room_id = rm.id 
                                      ORDER BY p.payment_date DESC LIMIT 5");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Admin Dashboard</h2>
        <div>
            <span class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
        </div>
    </div>
    
    <?php if ($unpaid_count > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Attention!</strong> There is <?= $unpaid_count ?> payment of outstanding reservations.
            <a href="payments.php" class="btn btn-sm btn-outline-dark ms-2">See Details</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($pending_count > 0): ?>
        <div class="alert alert-warning shadow-sm">⚠️ There is <?= $pending_count ?> Outstanding Reservations.</div>
    <?php endif; ?>
    <?php if ($available <= 3): ?>
        <div class="alert alert-danger shadow-sm">🚨 The room is almost full! Remaining <?= $available ?> room.</div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Reservations</h5>
                    <p class="card-text fs-4 fw-bold"><?= $reservation_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Customers</h5>
                    <p class="card-text fs-4 fw-bold"><?= $customer_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <p class="card-text fs-4 fw-bold"><?= $pending_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text fs-4 fw-bold">Rp <?= number_format($income, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Reservations</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($recent_reservations) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($reservation = mysqli_fetch_assoc($recent_reservations)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($reservation['room_type']) ?></td>
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
                        <div class="text-center mt-3">
                            <a href="reservations.php" class="btn btn-sm btn-outline-primary">View All Reservations</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No reservations found.</div>
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
                    <?php if (mysqli_num_rows($recent_payments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = mysqli_fetch_assoc($recent_payments)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($payment['room_type']) ?></td>
                                            <td>Rp <?= number_format($payment['amount_paid'], 0, ',', '.') ?></td>
                                            <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
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
                        <div class="text-center mt-3">
                            <a href="payments.php" class="btn btn-sm btn-outline-primary">View All Payments</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No payments found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-6">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="roomChart"></canvas>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <a href="rooms.php" class="btn btn-outline-primary w-100">🛏 Manage Room</a>
        </div>
        <div class="col-md-3">
            <a href="customers.php" class="btn btn-outline-secondary w-100">👤 Manage Customers</a>
        </div>
        <div class="col-md-3">
            <a href="reservations.php" class="btn btn-outline-success w-100">📆 Reservation</a>
        </div>
        <div class="col-md-3">
            <a href="payments.php" class="btn btn-outline-dark w-100">💳 Payment</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- for the chart -->
<script>
const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Pending'],
        datasets: [{
            data: [<?= $paid_count ?>, <?= $pending_count ?>],
            backgroundColor: ['#198754', '#ffc107'],
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Payment Status'
            },
            legend: {
                position: 'bottom'
            }
        }
    }
});

const ctxRoom = document.getElementById('roomChart').getContext('2d');
new Chart(ctxRoom, {
    type: 'pie',
    data: {
        labels: ['Available', 'Occupied'],
        datasets: [{
            data: [<?= $available ?>, <?= $booked ?>],
            backgroundColor: ['#0d6efd', '#dc3545'],
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Room Status'
            },
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>
