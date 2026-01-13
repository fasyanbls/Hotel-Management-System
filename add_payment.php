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
$reservation_id = $_GET['reservation_id'] ?? null;

if (!$reservation_id) {
    header("Location: my_reservations.php");
    exit();
}

// Get reservation details
$reservation_query = "SELECT r.*, rm.type, rm.price_per_night 
                     FROM reservations r 
                     JOIN rooms rm ON r.room_id = rm.id 
                     WHERE r.id = $reservation_id AND r.user_id = $customer_id";
$reservation_result = mysqli_query($conn, $reservation_query);
$reservation = mysqli_fetch_assoc($reservation_result);

if (!$reservation) {
    header("Location: my_reservations.php");
    exit();
}

// Calculate total price
$check_in = new DateTime($reservation['check_in_date']);
$check_out = new DateTime($reservation['check_out_date']);
$interval = $check_in->diff($check_out);
$nights = $interval->days;
$total_price = $nights * $reservation['price_per_night'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    
    // Insert payment record
    $query = "INSERT INTO payments (reservation_id, amount_paid, payment_date, status, payment_method) 
              VALUES ('$reservation_id', '$total_price', NOW(), 'Paid', '$payment_method')";

    if (mysqli_query($conn, $query)) {
        // Update the session with the correct customer ID
        $_SESSION['customer_id'] = $customer_id;
        header("Location: my_payments.php");
        exit();
    } else {
        $error = "Failed to process payment: " . mysqli_error($conn);
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Payment Details</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Reservation Details</h5>
                            <p class="mb-1"><strong>Room Type:</strong> <?= htmlspecialchars($reservation['type']) ?></p>
                            <p class="mb-1"><strong>Check In:</strong> <?= date('d M Y', strtotime($reservation['check_in_date'])) ?></p>
                            <p class="mb-1"><strong>Check Out:</strong> <?= date('d M Y', strtotime($reservation['check_out_date'])) ?></p>
                            <p class="mb-1"><strong>Duration:</strong> <?= $nights ?> nights</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Summary</h5>
                            <p class="mb-1"><strong>Price per Night:</strong> Rp <?= number_format($reservation['price_per_night'], 0, ',', '.') ?></p>
                            <p class="mb-1"><strong>Total Amount:</strong> Rp <?= number_format($total_price, 0, ',', '.') ?></p>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> After selecting your payment method, you will be redirected to the payment gateway.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                            <a href="my_reservations.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>