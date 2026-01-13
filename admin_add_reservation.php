<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $room_id = $_POST['room_id'];
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
            // Get room price
            $room_query = "SELECT price_per_night FROM rooms WHERE id = $room_id";
            $room_result = mysqli_query($conn, $room_query);
            $room = mysqli_fetch_assoc($room_result);
            
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
                
                header("Location: reservations.php");
                exit();
            } else {
                $error = "Failed to save reservation: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Add New Reservation</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">-- Select Customer --</option>
                                <?php
                                $customers = mysqli_query($conn, "SELECT * FROM customers");
                                while ($c = mysqli_fetch_assoc($customers)) {
                                    echo "<option value='{$c['id']}'>{$c['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="room_id" class="form-label">Room</label>
                            <select name="room_id" id="room_id" class="form-select" required>
                                <option value="">-- Choose Room --</option>
                                <?php
                                $rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE status = 'Available'");
                                while ($r = mysqli_fetch_assoc($rooms)) {
                                    echo "<option value='{$r['id']}'>Room {$r['room_number']} ({$r['type']})</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="check_in" class="form-label">Check-in</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" required 
                                   min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="check_out" class="form-label">Check-out</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" required 
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                   value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Reservation</button>
                            <a href="reservations.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 