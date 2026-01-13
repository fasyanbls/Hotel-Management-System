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

// Get all reservations that don't have payments
$reservations_query = "SELECT r.*, u.name as customer_name, rm.type as room_type, rm.price_per_night 
                      FROM reservations r
                      JOIN users u ON r.user_id = u.id
                      JOIN rooms rm ON r.room_id = rm.id
                      WHERE NOT EXISTS (
                          SELECT 1 FROM payments p 
                          WHERE p.reservation_id = r.id
                      )";
$reservations_result = mysqli_query($conn, $reservations_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_id = $_POST['reservation_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_method = $_POST['payment_method'];
    $status = $_POST['status'];
    
    // Basic validation
    if (empty($reservation_id) || empty($amount) || empty($payment_date) || empty($payment_method) || empty($status)) {
        $error = "Please fill in all fields.";
    } else {
        // Insert payment record
        $query = "INSERT INTO payments (reservation_id, amount_paid, payment_date, status, payment_method) 
                 VALUES ('$reservation_id', '$amount', '$payment_date', '$status', '$payment_method')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: payments.php");
            exit();
        } else {
            $error = "Failed to add payment: " . mysqli_error($conn);
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Payment</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="reservation_id" class="form-label">Select Reservation</label>
                            <select class="form-select" id="reservation_id" name="reservation_id" required>
                                <option value="">-- Select Reservation --</option>
                                <?php while ($row = mysqli_fetch_assoc($reservations_result)): ?>
                                    <option value="<?= $row['id'] ?>">
                                        Customer: <?= htmlspecialchars($row['customer_name']) ?> - 
                                        Room: <?= htmlspecialchars($row['room_type']) ?> - 
                                        Amount: Rp <?= number_format($row['total_price'], 0, ',', '.') ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">-- Select Payment Method --</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Payment Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Payment</button>
                            <a href="payments.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 