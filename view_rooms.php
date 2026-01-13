<?php
session_start();
include 'head.php';
include 'config/database.php';

// Include customer navigation if logged in as customer
if (isset($_SESSION['customer_id'])) {
    include 'customer_nav.php';
} else {
    // Simple navigation for non-logged in users
    echo '<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">🏨 MyHotel</a>
                <div class="d-flex">
                    <a href="customer_login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="customer_register.php" class="btn btn-light">Register</a>
                </div>
            </div>
          </nav>';
}

// Get all available rooms
$rooms_query = "SELECT * FROM rooms WHERE status = 'Available' ORDER BY price_per_night ASC";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms_count = mysqli_num_rows($rooms_result);
?>

<div class="container mt-4">
    <h2 class="mb-4">Available Rooms</h2>
    
    <?php if ($rooms_count > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php while ($room = mysqli_fetch_assoc($rooms_result)): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($room['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($room['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($room['type']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-house-door text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($room['type']) ?></h5>
                            <p class="card-text text-primary fw-bold">Rp <?= number_format($room['price_per_night'], 0, ',', '.') ?> / night</p>
                            
                            <?php if (!empty($room['description'])): ?>
                                <p class="card-text"><?= htmlspecialchars($room['description']) ?></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($room['facilities'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted">Facilities:</small>
                                    <p class="card-text small"><?= htmlspecialchars($room['facilities']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0">
                            <?php if (isset($_SESSION['customer_id'])): ?>
                                <a href="add_reservation.php?room_id=<?= $room['id'] ?>" class="btn btn-primary w-100">Book Now</a>
                            <?php else: ?>
                                <a href="customer_login.php" class="btn btn-outline-primary w-100">Login to Book</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No rooms are available at the moment. Please check back later.
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> 