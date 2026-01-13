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
$result = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $id");
$room = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $price = $_POST['price_per_night'];
    $status = $_POST['status'];
    $facilities = $_POST['facilities'];

    // Check if the user uploaded a new image
    if (!empty($_FILES['image']['name'])) {
        $image_name = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name);
        $query = "UPDATE rooms SET 
                    type='$type', 
                    price_per_night='$price', 
                    status='$status', 
                    facilities='$facilities',
                    image='$image_name' 
                  WHERE id = $id";
    } else {
        $query = "UPDATE rooms SET 
                    type='$type', 
                    price_per_night='$price', 
                    status='$status', 
                    facilities='$facilities'
                  WHERE id = $id";
    }

    mysqli_query($conn, $query);
    header('Location: rooms.php');
    exit;
}
?>

<h2>Edit Room</h2>
<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Room Type</label>
        <input type="text" name="type" class="form-control" value="<?= $room['type'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Price per Night</label>
        <input type="number" name="price_per_night" class="form-control" value="<?= $room['price_per_night'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="Available" <?= $room['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="Booked" <?= $room['status'] == 'Booked' ? 'selected' : '' ?>>Booked</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Facilities</label>
        <textarea name="facilities" class="form-control" rows="2"><?= $room['facilities'] ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Room Image</label><br>
        <?php if ($room['image']): ?>
            <img src="uploads/<?= $room['image'] ?>" alt="Room Image" style="width: 200px; border-radius: 10px;" class="mb-2"><br>
        <?php endif; ?>
        <input type="file" name="image" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

<?php include 'footer.php'; ?>
