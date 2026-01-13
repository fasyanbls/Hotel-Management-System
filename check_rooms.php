<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'head.php';
include 'config/database.php';
include 'nav.php';

$available_rooms = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    $sql = "SELECT * FROM rooms 
            WHERE id NOT IN (
                SELECT room_id FROM reservations 
                WHERE NOT (
                    check_out <= '$check_in' OR check_in >= '$check_out'
                )
            )";
    $result = mysqli_query($conn, $sql);
    while ($r = mysqli_fetch_assoc($result)) {
        $available_rooms[] = $r;
    }
}
?>

<h2>Check Room Availability</h2>

<form method="POST" action="">
    <div class="row">
        <div class="col-md-5">
            <label for="check_in" class="form-label">Check-in Date</label>
            <input type="date" name="check_in" id="check_in" class="form-control" required>
        </div>
        <div class="col-md-5">
            <label for="check_out" class="form-label"> Check-out Date</label>
            <input type="date" name="check_out" id="check_out" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Check</button>
        </div>
    </div>
</form>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
    <hr>
    <h4>Result: Room Available</h4>
    <?php if (count($available_rooms) > 0): ?>
        <ul class="list-group">
            <?php foreach ($available_rooms as $room): ?>
                <li class="list-group-item">
                    Room <?= $room['room_number'] ?> - <?= $room['type'] ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-warning mt-3">There are no rooms available for that date.</div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
