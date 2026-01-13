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
$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    header("Location: my_payments.php");
    exit();
}

// Get payment details
$payment_query = "SELECT p.*, r.check_in_date, r.check_out_date, rm.type, rm.price_per_night 
                 FROM payments p 
                 JOIN reservations r ON p.reservation_id = r.id 
                 JOIN rooms rm ON r.room_id = rm.id 
                 WHERE p.id = $payment_id AND r.user_id = $customer_id";
$payment_result = mysqli_query($conn, $payment_query);
$payment = mysqli_fetch_assoc($payment_result);

if (!$payment) {
    header("Location: my_payments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    
    // Update payment record
    $query = "UPDATE payments SET 
              payment_method = '$payment_method',
              status = 'Paid',
              payment_date = NOW()
              WHERE id = $payment_id";

    if (mysqli_query($conn, $query)) {
        header("Location: my_payments.php");
        exit();
    } else {
        $error = "Failed to update payment: " . mysqli_error($conn);
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Update Payment</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Reservation Details</h5>
                            <p class="mb-1"><strong>Room Type:</strong> <?= htmlspecialchars($payment['type']) ?></p>
                            <p class="mb-1"><strong>Check In:</strong> <?= date('d M Y', strtotime($payment['check_in_date'])) ?></p>
                            <p class="mb-1"><strong>Check Out:</strong> <?= date('d M Y', strtotime($payment['check_out_date'])) ?></p>
                            <p class="mb-1"><strong>Amount:</strong> Rp <?= number_format($payment['amount_paid'], 0, ',', '.') ?></p>
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
                            <i class="bi bi-info-circle"></i> Please select your payment method to confirm the payment.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Confirm Payment</button>
                            <a href="my_payments.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 