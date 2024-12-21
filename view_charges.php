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

// Initialize variables
$charges = [];
$filter_description = "Please select a filter to view charges.";

// Handle filters
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = $_POST['filter'] ?? '';
    $query = "SELECT * FROM charges WHERE user_id = :user_id";
    $params = ['user_id' => $user_id];

    if ($filter === 'last_7_days') {
        $query .= " AND charge_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $filter_description = "Charges from the last 7 days.";
    } elseif ($filter === 'last_30_days') {
        $query .= " AND charge_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $filter_description = "Charges from the last 30 days.";
    } elseif ($filter === 'custom') {
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';

        if ($start_date && $end_date) {
            $query .= " AND charge_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;
            $filter_description = "Charges from $start_date to $end_date.";
        } else {
            $filter_description = "Invalid date range. Please try again.";
        }
    } else {
        $filter_description = "Invalid filter selection.";
    }

    $query .= " ORDER BY charge_date DESC";

    // Fetch charges for the logged-in user
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Charges - Feedict</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ff8c00;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
        .header h1 {
            margin: 0;
        }
        .filter-form {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .filter-form label {
            font-weight: bold;
        }
        .filter-form select, .filter-form input[type="date"], .filter-form button {
            padding: 8px;
            margin: 0 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .charges-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .charges-table th, .charges-table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ccc;
        }
        .charges-table th {
            background-color: #ff8c00;
            color: white;
        }
        .charges-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .button {
            padding: 10px 20px;
            background-color: #ff8c00;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #ff5f00;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>View Charges</h1>
        </div>

        <form method="POST" class="filter-form">
            <label for="filter">Filter by:</label>
            <select name="filter" id="filter">
                <option value="">-- Select Filter --</option>
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_30_days">Last 30 Days</option>
                <option value="custom">Custom Date Range</option>
            </select>
            <label for="start_date">From:</label>
            <input type="date" name="start_date" id="start_date">
            <label for="end_date">To:</label>
            <input type="date" name="end_date" id="end_date">
            <button type="submit" class="button">Apply</button>
        </form>

        <p style="margin-top: 20px; font-weight: bold; color: #333;">Filter: <?php echo htmlspecialchars($filter_description); ?></p>

        <table class="charges-table">
            <thead>
                <tr>
                    <th>Charge Date</th>
                    <th>Day</th>
                    <th>Charge Amount</th>
                    <th>Review Text</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($charges)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No charges found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($charges as $charge): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($charge['charge_date']); ?></td>
                            <td><?php echo htmlspecialchars($charge['day']); ?></td>
                            <td><?php echo htmlspecialchars($charge['charge_amount']); ?></td>
                            <td><?php echo htmlspecialchars($charge['review_text']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: center;">
            <a href="home.php" class="button">Back to Home</a>
        </div>
    </div>

</body>
</html>
