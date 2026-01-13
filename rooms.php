<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Room Data</h2>
    <a href="add_room.php" class="btn btn-primary">+ Add Room</a>
</div>

<div class="row">
    <?php 
    $result = mysqli_query($conn, "SELECT * FROM rooms");
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img src="uploads/<?= $row['image'] ?>" class="card-img-top" alt="Room Photo" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"> <?= $row['type'] ?></h5>
                    <p class="card-text mb-1"><strong>Price:</strong> Rp <?= number_format($row['price_per_night'], 0, ',', '.') ?> / night</p>
                    <p class="card-text mb-1"><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                    <?php if (!empty($row['facilities'])): ?>
                        <p class="card-text"><strong>Facilities:</strong> <?= $row['facilities'] ?></p>
                    <?php endif; ?>
                    <a href="edit_room.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_room.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php include 'footer.php'; ?>