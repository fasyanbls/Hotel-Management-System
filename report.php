<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'nav.php';

// Fetch data
$reservations = mysqli_query($conn, "SELECT COUNT(*) as total FROM reservations");
$reservation_count = mysqli_fetch_assoc($reservations)['total'];

$paid_payments = mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'Paid'");
$paid_count = mysqli_fetch_assoc($paid_payments)['total'];

$total_income = mysqli_query($conn, "SELECT SUM(amount_paid) as total FROM payments WHERE status = 'Paid'");
$income = mysqli_fetch_assoc($total_income)['total'];

$total_rooms = mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms");
$booked_rooms_query = mysqli_query($conn, "SELECT COUNT(DISTINCT room_id) as total FROM reservations");
$booked_rooms = mysqli_fetch_assoc($booked_rooms_query)['total'];

$total_rooms_count = mysqli_fetch_assoc($total_rooms)['total'];
$available_rooms = $total_rooms_count - $booked_rooms;

// Monthly income data for Chart.js
$chart_query = mysqli_query($conn, "
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total 
    FROM payments 
    WHERE status = 'Paid' 
    GROUP BY month 
    ORDER BY month ASC
");

$months = [];
$incomes = [];

while ($row = mysqli_fetch_assoc($chart_query)) {
    $months[] = $row['month'];
    $incomes[] = $row['total'];
}
?>

<div class="container py-5">
    <h2 class="text-center fw-bold mb-5">📊 Hotel Report Overview</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body text-center">
                    <i data-feather="calendar" class="mb-2 text-primary" style="height: 32px;"></i>
                    <h6 class="fw-semibold">Total Reservations</h6>
                    <h3 class="text-primary"><?= $reservation_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body text-center">
                    <i data-feather="check-circle" class="mb-2 text-success" style="height: 32px;"></i>
                    <h6 class="fw-semibold">Successful Payments</h6>
                    <h3 class="text-success"><?= $paid_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body text-center">
                    <i data-feather="credit-card" class="mb-2 text-warning" style="height: 32px;"></i>
                    <h6 class="fw-semibold">Total Income</h6>
                    <h3 class="text-warning">Rp <?= number_format($income, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body text-center">
                    <i data-feather="home" class="mb-2 text-info" style="height: 32px;"></i>
                    <h6 class="fw-semibold">Available Rooms</h6>
                    <h3 class="text-info"><?= $available_rooms ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body text-center">
                    <i data-feather="users" class="mb-2 text-danger" style="height: 32px;"></i>
                    <h6 class="fw-semibold">Occupied Rooms</h6>
                    <h3 class="text-danger"><?= $booked_rooms ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center my-4">
        <a href="export_excel.php" class="btn btn-outline-success me-2"><i data-feather="file-text"></i> Export Excel</a>
        <a href="export_pdf.php" class="btn btn-outline-danger"><i data-feather="printer"></i> Export PDF</a>
        
   
</a>
    
    </div>

    <div class="card shadow-sm border-0 mt-5">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">📈 Monthly Income Overview</h5>
            <canvas id="incomeChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js & Feather Icons -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
    feather.replace();

    const ctx = document.getElementById('incomeChart').getContext('2d');
    const incomeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Monthly Income (Rp)',
                data: <?= json_encode($incomes) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.raw.toLocaleString();
                        }
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>

