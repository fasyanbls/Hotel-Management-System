<?php
require('fpdf/fpdf.php');
include 'config/database.php';

class PDF extends FPDF {
    // Page header
    function Header() {
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title with blue color
        $this->SetTextColor(51, 122, 183);
        $this->Cell(120,10,'Hotel Reservation Report',0,0,'C');
        $this->SetTextColor(0);
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Report generated on: ' . date('d-m-Y H:i'),0,0,'L');
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
    }

    // Wrapped cell
    function WrapCell($w, $h, $txt, $border=0, $align='L', $fill=false) {
        // Store current position
        $x = $this->GetX();
        $y = $this->GetY();

        // Calculate text height
        $this->MultiCell($w, 5, $txt, 0, $align);
        $height = $this->GetY() - $y;

        // Go back to stored position
        $this->SetXY($x + $w, $y);

        return $height;
    }

    // Better table
    function ImprovedTable($header, $data) {
        // Column widths (adjusted for room type)
        $w = array(10, 35, 45, 45, 25, 25, 25, 35);
        
        // Colors for header
        $this->SetFillColor(51, 122, 183);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 10);
        
        // Header
        for($i=0; $i<count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 9);
        
        // Data
        $fill = false;
        foreach($data as $row) {
            $maxHeight = 6; // Minimum row height
            
            // Calculate required height for room type
            $this->SetXY($this->GetX(), $this->GetY());
            $roomTypeHeight = $this->WrapCell($w[3], 5, $row[3]);
            $maxHeight = max($maxHeight, $roomTypeHeight);
            
            // Reset position and draw the complete row
            $this->SetXY($this->GetX(), $this->GetY() - $roomTypeHeight);
            
            $this->Cell($w[0], $maxHeight, $row[0], 'LR', 0, 'C', $fill);
            $this->Cell($w[1], $maxHeight, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], $maxHeight, $row[2], 'LR', 0, 'L', $fill);
            
            // Room type with wrapping
            $x = $this->GetX();
            $y = $this->GetY();
            $this->MultiCell($w[3], 5, $row[3], 'LR', 'L', $fill);
            $this->SetXY($x + $w[3], $y);
            
            $this->Cell($w[4], $maxHeight, $row[4], 'LR', 0, 'C', $fill);
            $this->Cell($w[5], $maxHeight, $row[5], 'LR', 0, 'C', $fill);
            $this->Cell($w[6], $maxHeight, $row[6], 'LR', 0, 'C', $fill);
            $this->Cell($w[7], $maxHeight, $row[7], 'LR', 0, 'R', $fill);
            $this->Ln($maxHeight);
            
            $fill = !$fill;
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// Create new PDF document
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Set colors for header
$pdf->SetFillColor(51, 122, 183); // Blue background
$pdf->SetTextColor(255); // White text
$pdf->SetFont('Arial', 'B', 10);

// Table Header
$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Customer', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Room Type', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Check-in', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Check-out', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Price', 1, 1, 'C', true);

// Reset text color and set font for data
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 9);

// Fetch data
$query = "
    SELECT 
        r.id,
        c.name AS customer_name,
        c.email,
        rm.type AS room_type,
        r.check_in_date,
        r.check_out_date,
        r.total_price,
        COALESCE(p.status, 'Unpaid') as payment_status
    FROM reservations r
    JOIN customers c ON r.user_id = c.id
    JOIN rooms rm ON r.room_id = rm.id
    LEFT JOIN payments p ON r.id = p.reservation_id
    ORDER BY r.id ASC
";

$result = mysqli_query($conn, $query);
$no = 1;

// Set colors for alternating rows
$lightBlue = array(235, 245, 255);
$white = array(255, 255, 255);

// Table data
while($row = mysqli_fetch_assoc($result)) {
    // Alternate row colors
    if($no % 2 == 0) {
        $pdf->SetFillColor($lightBlue[0], $lightBlue[1], $lightBlue[2]);
    } else {
        $pdf->SetFillColor($white[0], $white[1], $white[2]);
    }

    // Format dates
    $check_in = date('d/m/Y', strtotime($row['check_in_date']));
    $check_out = date('d/m/Y', strtotime($row['check_out_date']));
    
    // Format price
    $price = 'Rp ' . number_format($row['total_price'], 0, ',', '.');

    // Set text color for status
    $status = $row['payment_status'];
    
    $pdf->Cell(10, 8, $no++, 1, 0, 'C', true);
    $pdf->Cell(40, 8, substr($row['customer_name'], 0, 23), 1, 0, 'L', true);
    $pdf->Cell(50, 8, substr($row['email'], 0, 28), 1, 0, 'L', true);
    $pdf->Cell(45, 8, substr($row['room_type'], 0, 25), 1, 0, 'L', true);
    $pdf->Cell(30, 8, $check_in, 1, 0, 'C', true);
    $pdf->Cell(30, 8, $check_out, 1, 0, 'C', true);
    
    // Set color for status
    if($status == 'Paid') {
        $pdf->SetTextColor(40, 167, 69); // Green
    } else {
        $pdf->SetTextColor(220, 53, 69); // Red
    }
    $pdf->Cell(25, 8, $status, 1, 0, 'C', true);
    
    // Reset text color for price
    $pdf->SetTextColor(0);
    $pdf->Cell(35, 8, $price, 1, 1, 'L', true);
}

// Output PDF
$pdf->Output('I', 'Hotel_Reservation_Report.pdf');
