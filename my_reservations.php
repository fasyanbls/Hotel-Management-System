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
    // If customer doesn't exist in customers table, create them
    $customer_name = $_SESSION['customer_name'];
    $insert_query = "INSERT INTO customers (name, email) VALUES ('$customer_name', '$customer_email')";
    mysqli_query($conn, $insert_query);
    $customer_id = mysqli_insert_id($conn);
} else {
    $customer_id = $customer['id'];
}

// Get customer's reservations
$reservations_query = "SELECT r.*, rm.type, rm.price_per_night, rm.image 
                      FROM reservations r 
                      JOIN rooms rm ON r.room_id = rm.id 
                      WHERE r.user_id = $customer_id 
                      ORDER BY r.check_in_date DESC";
$reservations_result = mysqli_query($conn, $reservations_query);
$reservations_count = mysqli_num_rows($reservations_result);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Reservations</h2>
        <a href="view_rooms.php" class="btn btn-primary">Book New Room</a>
    </div>
    
    <?php if ($reservations_count > 0): ?>
        <div class="row">
            <?php while ($reservation = mysqli_fetch_assoc($reservations_result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <?php if (!empty($reservation['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($reservation['image']) ?>" class="img-fluid rounded-start h-100" alt="<?= htmlspecialchars($reservation['type']) ?>" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                        <i class="bi bi-house-door text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($reservation['type']) ?></h5>
                                    <p class="card-text text-primary fw-bold">Rp <?= number_format($reservation['price_per_night'], 0, ',', '.') ?> / night</p>
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Check In:</small>
                                            <p class="mb-0"><?= date('d M Y', strtotime($reservation['check_in_date'])) ?></p>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Check Out:</small>
                                            <p class="mb-0"><?= date('d M Y', strtotime($reservation['check_out_date'])) ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php 
                                    // Calculate total price
                                    $check_in = new DateTime($reservation['check_in_date']);
                                    $check_out = new DateTime($reservation['check_out_date']);
                                    $interval = $check_in->diff($check_out);
                                    $nights = $interval->days;
                                    $total_price = $nights * $reservation['price_per_night'];
                                    ?>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Duration:</small>
                                        <p class="mb-0"><?= $nights ?> nights</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Total Price:</small>
                                        <p class="mb-0 fw-bold">Rp <?= number_format($total_price, 0, ',', '.') ?></p>
                                    </div>
                                    
                                    <?php 
                                    // Check payment status
                                    $payment_query = "SELECT * FROM payments WHERE reservation_id = {$reservation['id']}";
                                    $payment_result = mysqli_query($conn, $payment_query);
                                    
                                    if (mysqli_num_rows($payment_result) > 0) {
                                        $payment = mysqli_fetch_assoc($payment_result);
                                        $status = $payment['status'];
                                        $status_class = $status == 'Paid' ? 'success' : 'warning';
                                    } else {
                                        $status = 'Unpaid';
                                        $status_class = 'danger';
                                    }
                                    ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $status_class ?>"><?= $status ?></span>
                                        
                                        <?php if ($status == 'Unpaid'): ?>
                                            <a href="add_payment.php?reservation_id=<?= $reservation['id'] ?>" class="btn btn-sm btn-primary">Pay Now</a>
                                        <?php elseif ($status == 'Pending'): ?>
                                            <a href="my_payments.php" class="btn btn-sm btn-outline-primary">View Payment</a>
                                        <?php else: ?>
                                            <a href="receipt.php?id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-success">View Receipt</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You don't have any reservations yet.
        </div>
        <div class="text-center">
            <a href="view_rooms.php" class="btn btn-primary">Book a Room</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> 