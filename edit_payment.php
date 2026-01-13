<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

$error = '';
$success = '';

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : null;

if ($payment_id) {
    // Edit existing payment
    $query = "SELECT p.*, r.check_in_date, r.check_out_date, r.total_price,
                     u.name as customer_name, rm.type as room_type
              FROM payments p
              JOIN reservations r ON p.reservation_id = r.id
              JOIN users u ON r.user_id = u.id
              JOIN rooms rm ON r.room_id = rm.id
              WHERE p.id = $payment_id";
    $result = mysqli_query($conn, $query);
    $payment = mysqli_fetch_assoc($result);

    if (!$payment) {
        header("Location: payments.php");
        exit();
    }
    
    $reservation_id = $payment['reservation_id'];
} else if ($reservation_id) {
    // New payment for existing reservation
    $query = "SELECT r.*, u.name as customer_name, rm.type as room_type
              FROM reservations r
              JOIN users u ON r.user_id = u.id
              JOIN rooms rm ON r.room_id = rm.id
              WHERE r.id = $reservation_id";
    $result = mysqli_query($conn, $query);
    $reservation = mysqli_fetch_assoc($result);

    if (!$reservation) {
        header("Location: payments.php");
        exit();
    }
} else {
    header("Location: payments.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    
    if (empty($amount) || empty($payment_date) || empty($payment_method) || empty($status)) {
        $error = "Please fill in all fields.";
    } else {
        if ($payment_id) {
            // Update existing payment
            $query = "UPDATE payments SET 
                        amount_paid = '$amount',
                        payment_date = '$payment_date',
                        payment_method = '$payment_method',
                        status = '$status'
                     WHERE id = $payment_id";
        } else {
            // Insert new payment
            $query = "INSERT INTO payments (reservation_id, amount_paid, payment_date, status, payment_method) 
                     VALUES ('$reservation_id', '$amount', '$payment_date', '$status', '$payment_method')";
        }
        
        if (mysqli_query($conn, $query)) {
            header("Location: payments.php");
            exit();
        } else {
            $error = "Failed to " . ($payment_id ? "update" : "add") . " payment: " . mysqli_error($conn);
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $payment_id ? 'Edit' : 'Add' ?> Payment</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h5 class="alert-heading">Reservation Details</h5>
                        <p class="mb-1">
                            <strong>Customer:</strong> 
                            <?= htmlspecialchars($payment['customer_name'] ?? $reservation['customer_name']) ?>
                        </p>
                        <p class="mb-1">
                            <strong>Room:</strong> 
                            <?= htmlspecialchars($payment['room_type'] ?? $reservation['room_type']) ?>
                        </p>
                        <p class="mb-1">
                            <strong>Check In:</strong> 
                            <?= date('d M Y', strtotime($payment['check_in_date'] ?? $reservation['check_in_date'])) ?>
                        </p>
                        <p class="mb-1">
                            <strong>Check Out:</strong> 
                            <?= date('d M Y', strtotime($payment['check_out_date'] ?? $reservation['check_out_date'])) ?>
                        </p>
                        <p class="mb-0">
                            <strong>Total Price:</strong> 
                            Rp <?= number_format($payment['total_price'] ?? $reservation['total_price'], 0, ',', '.') ?>
                        </p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   value="<?= $payment['amount_paid'] ?? $reservation['total_price'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                   value="<?= $payment['payment_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">-- Select Payment Method --</option>
                                <?php
                                $methods = ['Cash', 'Bank Transfer', 'Credit Card', 'E-Wallet'];
                                foreach ($methods as $method) {
                                    $selected = ($payment['payment_method'] ?? '') === $method ? 'selected' : '';
                                    echo "<option value='$method' $selected>$method</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Payment Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php
                                $statuses = ['Paid', 'Pending'];
                                foreach ($statuses as $status) {
                                    $selected = ($payment['status'] ?? '') === $status ? 'selected' : '';
                                    echo "<option value='$status' $selected>$status</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?= $payment_id ? 'Update' : 'Add' ?> Payment
                            </button>
                            <a href="payments.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>