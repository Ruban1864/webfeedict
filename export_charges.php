<?php
session_start(); // Start the session

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
$host = 'localhost'; 
$dbname = 'feedict'; 
$username = 'root';  
$password = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch user details
$user_id = $_SESSION['user_id'];

// Initialize filter variables
$date_filter = '';
$filter_description = '';

// Handle filtering
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['filter'])) {
        $filter = $_POST['filter'];
        switch ($filter) {
            case 'last7days':
                $date_filter = "AND charge_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $filter_description = "Last 7 Days";
                break;
            case 'last30days':
                $date_filter = "AND charge_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $filter_description = "Last 30 Days";
                break;
            case 'month':
                $month = $_POST['month'] ?? '';
                if ($month) {
                    $date_filter = "AND MONTH(charge_date) = '$month'";
                    $filter_description = "Month: " . date('F', mktime(0, 0, 0, $month, 1));
                }
                break;
            case 'custom':
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                if ($start_date && $end_date) {
                    $date_filter = "AND charge_date BETWEEN '$start_date' AND '$end_date'";
                    $filter_description = "Custom Range: $start_date to $end_date";
                }
                break;
        }
    }

    // Fetch charges for the logged-in user
    $query = "SELECT * FROM charges WHERE user_id = :user_id $date_filter ORDER BY charge_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create the PDF
    require('fpdf/fpdf.php');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Add Title
    $pdf->Cell(200, 10, 'Charge Report', 0, 1, 'C');

    // Add Filter Description
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(200, 10, $filter_description, 0, 1, 'C');

    // Add a line break
    $pdf->Ln(10);

    // Add table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Charge Date', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Charge Amount', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Day', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Review Text', 1, 1, 'C');

    // Add charges data to the table
    $pdf->SetFont('Arial', '', 12);
    foreach ($charges as $charge) {
        $pdf->Cell(40, 10, $charge['charge_date'], 1, 0, 'C');
        $pdf->Cell(40, 10, $charge['charge_amount'], 1, 0, 'C');
        $pdf->Cell(40, 10, $charge['day'], 1, 0, 'C');
        $pdf->MultiCell(60, 10, $charge['review_text'], 1, 'L');
    }

    // Output the PDF
    $pdf->Output('D', 'charge_report.pdf');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Charges - Feedict</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        h1 {
            text-align: center;
            color: #ff8c00;
        }
        form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button {
            background-color: #ff8c00;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
        }
        .button:hover {
            background-color: #ff5f00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Export Charges</h1>
        <form method="POST">
            <div class="form-group">
                <label for="filter">Filter Options:</label>
                <select name="filter" id="filter" required>
                    <option value="">Select an option</option>
                    <option value="last7days">Last 7 Days</option>
                    <option value="last30days">Last 30 Days</option>
                    <option value="month">Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div class="form-group" id="month-group" style="display: none;">
                <label for="month">Select Month:</label>
                <select name="month" id="month">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
            <div class="form-group" id="custom-dates" style="display: none;">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date">
            </div>
            <button type="submit" class="button">Export PDF</button>
        </form>
    </div>

    <script>
        const filterSelect = document.getElementById('filter');
        const monthGroup = document.getElementById('month-group');
        const customDates = document.getElementById('custom-dates');

        filterSelect.addEventListener('change', () => {
            const filterValue = filterSelect.value;
            monthGroup.style.display = filterValue === 'month' ? 'block' : 'none';
            customDates.style.display = filterValue === 'custom' ? 'block' : 'none';
        });
    </script>
    <div style="margin-top: 20px; text-align: center;">
            <a href="home.php" class="button">Back to Home</a>
        </div>
    </div>
</body>
</html>
