<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include 'config/database.php';

// Set headers to export as Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hotel_reservations.xls");

// Print headers
echo "Reservation ID\tCustomer Name\tEmail\tRoom Type\tCheck-in Date\tCheck-out Date\tTotal Price\tPayment Status\tPayment Date\tPayment Method\n";

// Fetch data with all necessary information
$query = "
    SELECT 
        r.id,
        c.name AS customer_name,
        c.email,
        rm.type AS room_type,
        r.check_in_date,
        r.check_out_date,
        r.total_price,
        p.status AS payment_status,
        p.payment_date,
        p.payment_method
    FROM reservations r 
    JOIN customers c ON r.user_id = c.id
    JOIN rooms rm ON r.room_id = rm.id
    LEFT JOIN payments p ON r.id = p.reservation_id
    ORDER BY r.check_in_date DESC
";

$result = mysqli_query($conn, $query);

// Display each row
while ($row = mysqli_fetch_assoc($result)) {
    // Format dates
    $check_in = date('d M Y', strtotime($row['check_in_date']));
    $check_out = date('d M Y', strtotime($row['check_out_date']));
    $payment_date = $row['payment_date'] ? date('d M Y', strtotime($row['payment_date'])) : 'Not Paid';
    
    // Format price
    $total_price = 'Rp ' . number_format($row['total_price'], 0, ',', '.');
    
    // Clean data for Excel
    $customer_name = str_replace(["\t", "\n", "\r"], ' ', $row['customer_name']);
    $email = str_replace(["\t", "\n", "\r"], ' ', $row['email']);
    $room_type = str_replace(["\t", "\n", "\r"], ' ', $row['room_type']);
    $payment_status = str_replace(["\t", "\n", "\r"], ' ', $row['payment_status']);
    $payment_method = str_replace(["\t", "\n", "\r"], ' ', $row['payment_method']);

    echo "{$row['id']}\t{$customer_name}\t{$email}\t{$room_type}\t{$check_in}\t{$check_out}\t{$total_price}\t{$payment_status}\t{$payment_date}\t{$payment_method}\n";
}
?>