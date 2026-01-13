<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'customer_nav.php';

$room_id = $_GET['room_id'] ?? null;
if (!$room_id) {
    header("Location: view_rooms.php");
    exit();
}

// Get room information
$room_query = "SELECT * FROM rooms WHERE id = $room_id AND status = 'Available'";
$room_result = mysqli_query($conn, $room_query);
$room = mysqli_fetch_assoc($room_result);

if (!$room) {
    header("Location: view_rooms.php");
    exit();
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    // Date validation
    if ($check_in >= $check_out) {
        $error = "Check-in date must be before check-out date.";
    } else {
        // Check room availability for the given date range
        $conflict = mysqli_query($conn, "
            SELECT * FROM reservations
            WHERE room_id = '$room_id'
            AND (
                ('$check_in' BETWEEN check_in_date AND check_out_date)
                OR
                ('$check_out' BETWEEN check_in_date AND check_out_date)
                OR
                (check_in_date BETWEEN '$check_in' AND '$check_out')
            )
        ");

        if (mysqli_num_rows($conflict) > 0) {
            $error = "The room is already booked for these dates. Please choose different dates.";
        } else {
            // Calculate total price
            $days = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
            $total_price = $days * $room['price_per_night'];

            // Save to reservations table
            $query = "INSERT INTO reservations (user_id, room_id, check_in_date, check_out_date, total_price) 
                    VALUES ('$customer_id', '$room_id', '$check_in', '$check_out', '$total_price')";
            
            if (mysqli_query($conn, $query)) {
                $reservation_id = mysqli_insert_id($conn);
                
                // Create pending payment
                $payment_query = "INSERT INTO payments (reservation_id, amount_paid, status) 
                                VALUES ('$reservation_id', '$total_price', 'Pending')";
                mysqli_query($conn, $payment_query);
                
                header("Location: my_reservations.php");
                exit();
            } else {
                $error = "Failed to save reservation: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Book Room</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <?php if (!empty($room['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($room['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($room['type']) ?>">
                            <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-house-door text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5><?= htmlspecialchars($room['type']) ?></h5>
                            <p class="text-primary fw-bold">Rp <?= number_format($room['price_per_night'], 0, ',', '.') ?> / night</p>
                            <?php if (!empty($room['description'])): ?>
                                <p><?= htmlspecialchars($room['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($room['facilities'])): ?>
                                <div class="mt-3">
                                    <small class="text-muted">Facilities:</small>
                                    <p class="small mb-0"><?= htmlspecialchars($room['facilities']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in" class="form-label">Check-in Date</label>
                                    <input type="date" class="form-control" id="check_in" name="check_in" required 
                                           min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out" class="form-label">Check-out Date</label>
                                    <input type="date" class="form-control" id="check_out" name="check_out" required 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                           value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                            <a href="view_rooms.php" class="btn btn-outline-secondary">Back to Rooms</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
