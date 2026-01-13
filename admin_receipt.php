<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

$reservation_id = $_GET['id'] ?? null;

if (!$reservation_id) {
    header("Location: reservations.php");
    exit();
}

// Get reservation and payment details
$query = "SELECT r.*, p.*, rm.type, rm.price_per_night, c.name as customer_name, c.email as customer_email, c.phone as customer_phone
          FROM reservations r 
          JOIN payments p ON r.id = p.reservation_id 
          JOIN rooms rm ON r.room_id = rm.id 
          JOIN customers c ON r.user_id = c.id
          WHERE r.id = $reservation_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: reservations.php");
    exit();
}

// Calculate duration
$check_in = new DateTime($data['check_in_date']);
$check_out = new DateTime($data['check_out_date']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <!-- Hotel Header -->
                    <div class="text-center mb-4">
                        <h2 class="mb-0">MyHotel</h2>
                        <p class="text-muted mb-0">123 Hotel Street, City, Country</p>
                        <p class="text-muted mb-0">Phone: +628714026672 | Email: info@MyHotel.com</p>
                    </div>

                    <!-- Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Invoice Details</h5>
                            <p class="mb-1"><strong>Invoice Number:</strong> INV-<?= str_pad($data['id'], 6, '0', STR_PAD_LEFT) ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime($data['payment_date'])) ?></p>
                            <p class="mb-1"><strong>Payment Method:</strong> <?= htmlspecialchars($data['payment_method']) ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5>Customer Details</h5>
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
                        <i class="bi bi-check-circle-fill"></i> Payment Status: <?= strtoupper($data['status']) ?>
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
                        <a href="reservations.php" class="btn btn-secondary">Back to Reservations</a>
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
    .container-fluid {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
    }
}
</style>

<?php include 'footer.php'; ?> 