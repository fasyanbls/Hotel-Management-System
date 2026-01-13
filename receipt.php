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
$customer_query = "SELECT * FROM customers WHERE email = '$customer_email'";
$customer_result = mysqli_query($conn, $customer_query);
$customer = mysqli_fetch_assoc($customer_result);

if (!$customer) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $customer['id'];
$reservation_id = $_GET['id'] ?? null;

if (!$reservation_id) {
    header("Location: my_payments.php");
    exit();
}

// Get reservation and payment details
$query = "SELECT r.*, p.*, rm.type, rm.price_per_night, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
          FROM reservations r 
          JOIN payments p ON r.id = p.reservation_id 
          JOIN rooms rm ON r.room_id = rm.id 
          JOIN customers c ON r.user_id = c.id
          WHERE r.id = $reservation_id AND r.user_id = $customer_id AND p.status = 'Paid'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: my_payments.php");
    exit();
}

// Calculate duration
$check_in = new DateTime($data['check_in_date']);
$check_out = new DateTime($data['check_out_date']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Hotel Header -->
                    <div class="text-center mb-4">
                        <h2 class="mb-1">MyHotel HOTEL</h2>
                        <p class="mb-0">President University, Cikarang</p>
                        <p class="mb-0">Phone: (021) 88888888 | Email: president@student.ac.id</p>
                    </div>

                    <!-- Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">INVOICE</h5>
                            <p class="mb-1"><strong>Invoice No:</strong> INV-<?= str_pad($data['id'], 6, '0', STR_PAD_LEFT) ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime($data['payment_date'])) ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars($data['payment_method']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">GUEST DETAILS</h5>
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($data['customer_name']) ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($data['customer_email']) ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($data['customer_phone']) ?></p>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Room Type:</strong> <?= htmlspecialchars($data['type']) ?><br>
                                        <small class="text-muted">
                                            Check-in: <?= date('d M Y', strtotime($data['check_in_date'])) ?><br>
                                            Check-out: <?= date('d M Y', strtotime($data['check_out_date'])) ?><br>
                                            Duration: <?= $nights ?> nights
                                        </small>
                                    </td>
                                    <td class="text-end">Rp <?= number_format($data['amount_paid'], 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total Amount</th>
                                    <th class="text-end">Rp <?= number_format($data['amount_paid'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Payment Status -->
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle-fill"></i> Payment Status: PAID
                    </div>

                    <!-- Footer -->
                    <div class="text-center text-muted">
                        <p class="mb-0">Thank you for choosing Luxury Hotel!</p>
                        <p class="mb-0">We hope you enjoyed your stay with us.</p>
                        <p class="mb-0">Please keep this invoice for your records.</p>
                    </div>

                    <!-- Print Button -->
                    <div class="text-center mt-4">
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Print Receipt
                        </button>
                        <a href="my_payments.php" class="btn btn-secondary">Back to Payments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .btn, footer {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .container {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
    }
}
</style>

<?php include 'footer.php'; ?>
