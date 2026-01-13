<!-- Bootstrap Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🏨 MyHotel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="rooms.php">Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="customers.php">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="reservations.php">Reservations</a></li>
                <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">Reports</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a class="text-white fw-bold text-decoration-none" href="add_reservation.php">+ New Reservation</a>
                <a class="btn btn-outline-light btn-sm" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
            </div>
        </div>
    </div>
</nav>
