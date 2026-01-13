<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';
$id = $_GET['id'];

// Ambil data reservasi + users + rooms
$reservation = mysqli_query($conn, "SELECT * FROM reservations WHERE id = $id");
$data = mysqli_fetch_assoc($reservation);

$users = mysqli_query($conn, "SELECT * FROM users WHERE role = 'customer'");
$rooms = mysqli_query($conn, "SELECT * FROM rooms");

// Fungsi hitung hari
function calculateDays($check_in, $check_out) {
    return (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    $room_result = mysqli_query($conn, "SELECT price_per_night FROM rooms WHERE id = $room_id");
    $room = mysqli_fetch_assoc($room_result);
    $price = $room['price_per_night'];

    $days = calculateDays($check_in, $check_out);
    $total = $days * $price;

    $query = "UPDATE reservations SET 
                user_id = '$user_id', 
                room_id = '$room_id', 
                check_in_date = '$check_in', 
                check_out_date = '$check_out', 
                total_price = '$total' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: reservations.php");
    } else {
        echo "Gagal update reservasi: " . mysqli_error($conn);
    }
}
?>

<h2>Edit Reservasi</h2>
<form method="POST">
    Nama Customer:
    <select name="user_id" required>
        <?php while ($u = mysqli_fetch_assoc($users)) {
            $selected = $u['id'] == $data['user_id'] ? 'selected' : '';
            echo "<option value='{$u['id']}' $selected>{$u['name']}</option>";
        } ?>
    </select><br><br>

    Pilih Kamar:
    <select name="room_id" required>
        <?php mysqli_data_seek($rooms, 0); while ($r = mysqli_fetch_assoc($rooms)) {
            $selected = $r['id'] == $data['room_id'] ? 'selected' : '';
            echo "<option value='{$r['id']}' $selected>No {$r['room_number']} ({$r['type']})</option>";
        } ?>
    </select><br><br>

    Check-in: <input type="date" name="check_in" value="<?= $data['check_in_date'] ?>" required><br><br>
    Check-out: <input type="date" name="check_out" value="<?= $data['check_out_date'] ?>" required><br><br>

    <input type="submit" value="Update">
</form>
