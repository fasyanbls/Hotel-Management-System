<?php
session_start();
include 'head.php';
include 'config/database.php';

// Get room information for display
$rooms_query = "SELECT * FROM rooms WHERE status = 'Available' ORDER BY price_per_night ASC LIMIT 6";
$rooms_result = mysqli_query($conn, $rooms_query);
$rooms_count = mysqli_num_rows($rooms_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHotel - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .room-card {
            transition: transform 0.3s;
        }
        .room-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🏨 MyHotel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rooms">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['admin'])): ?>
                        <a href="dashboard.php" class="btn btn-outline-light me-2">Admin Dashboard</a>
                        <a href="logout.php" class="btn btn-light">Logout</a>
                    <?php elseif (isset($_SESSION['customer_id'])): ?>
                        <a href="customer_dashboard.php" class="btn btn-outline-light me-2">My Dashboard</a>
                        <a href="customer_logout.php" class="btn btn-light">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">Admin Login</a>
                        <a href="customer_login.php" class="btn btn-light">Customer Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Welcome to MyHotel</h1>
            <p class="lead mb-4">Experience luxury and comfort in the heart of the city</p>
            <a href="#rooms" class="btn btn-primary btn-lg">View Our Rooms</a>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Our Rooms</h2>
            
            <?php if ($rooms_count > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php while ($room = mysqli_fetch_assoc($rooms_result)): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm room-card">
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
                <div class="text-center mt-4">
                    <a href="view_rooms.php" class="btn btn-outline-primary">View All Rooms</a>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No rooms are available at the moment. Please check back later.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Safe & Secure</h4>
                    <p>Your safety is our top priority with 24/7 security and secure payment processing.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <h4>Luxury Experience</h4>
                    <p>Enjoy premium amenities and services designed for your comfort and relaxation.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h4>Prime Location</h4>
                    <p>Conveniently located in the heart of the city with easy access to attractions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Contact Us</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-body">
                            <form>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" rows="4" required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>MyHotel</h5>
                    <p>Experience luxury and comfort in the heart of the city.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="#rooms" class="text-white">Rooms</a></li>
                        <li><a href="#features" class="text-white">Features</a></li>
                        <li><a href="#contact" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> 123 Hotel Street, City</li>
                        <li><i class="bi bi-telephone"></i> +123 456 7890</li>
                        <li><i class="bi bi-envelope"></i> info@myhotel.com</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> MyHotel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>