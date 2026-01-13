<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $price = $_POST['price_per_night'];
    $status = $_POST['status'];
    $facilities = $_POST['facilities'];

    // Upload photo
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    
    // Generate unique filename to prevent overwriting
    $image_name = uniqid() . '_' . $image;
    
    // Move the uploaded file
    move_uploaded_file($image_tmp, 'uploads/' . $image_name);

    $query = "INSERT INTO rooms (type, price_per_night, status, image, facilities) 
              VALUES ('$type', '$price', '$status', '$image_name', '$facilities')";
    mysqli_query($conn, $query);
    header('Location: rooms.php');
    exit;
}
?>

<h2>Add Room </h2>
<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Room Type</label>
        <input type="text" name="type" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Price per Night</label>
        <input type="number" name="price_per_night" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="Available">Available</option>
            <option value="Booked">Booked</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Facilities</label>
        <input type="text" name="facilities" class="form-control" placeholder="WiFi, AC, TV" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Room Photo</label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
        <!-- <img src="uploads/<?= $room['image'] ?>" alt="Room Image" style="width: 200px; border-radius: 10px;" class="mb-2"><br> -->
    </div>
    
    <button type="submit" class="btn btn-success">Save</button>
</form>

<?php include 'footer.php'; ?>
