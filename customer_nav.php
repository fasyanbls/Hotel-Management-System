<!-- Bootstrap Navigation Bar for Customers -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🏨 MyHotel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="customer_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="view_rooms.php">View Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="my_reservations.php">My Reservations</a></li>
                <li class="nav-item"><a class="nav-link" href="my_payments.php">My Payments</a></li>
                <li class="nav-item"><a class="nav-link" href="my_profile.php">My Profile</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white">Welcome, <?= $_SESSION['customer_name'] ?></span>
                <a class="btn btn-outline-light btn-sm" href="customer_logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </div>
</nav> 