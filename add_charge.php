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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $charge_amount = $_POST['charge_amount'];
    $review_text = $_POST['review_text'];
    $charge_date = $_POST['charge_date'];
    $day = $_POST['day']; // Day selected by the user
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($charge_amount) || empty($charge_date) || empty($day)) {
        $error_message = "Please fill all required fields.";
    } else {
        // Insert the charge data into the database
        $stmt = $pdo->prepare("INSERT INTO charges (user_id, charge_amount, review_text, charge_date, day) VALUES (:user_id, :charge_amount, :review_text, :charge_date, :day)");
        $stmt->execute([
            'user_id' => $user_id,
            'charge_amount' => $charge_amount,
            'review_text' => $review_text,
            'charge_date' => $charge_date,
            'day' => $day
        ]);

        $success_message = "Charge added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Charge - Feedict</title>
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
        .form-container {
            margin-top: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-container input,
        .form-container textarea,
        .form-container select,
        .form-container button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-container button {
            background-color: #ff8c00;
            color: white;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #ff5f00;
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
        .success-message {
            color: green;
            font-weight: bold;
        }
        .navigation {
            text-align: center;
            margin-top: 20px;
        }
        .nav-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .nav-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Add Charge</h1>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form action="add_charge.php" method="POST">
                <label for="charge_amount">Charge Amount:</label>
                <input type="text" id="charge_amount" name="charge_amount" required>

                <label for="charge_date">Charge Date:</label>
                <input type="date" id="charge_date" name="charge_date" required>

                <label for="day">Day:</label>
                <select id="day" name="day" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>

                <label for="review_text">Review Text (optional):</label>
                <textarea id="review_text" name="review_text"></textarea>

                <button type="submit">Add Charge</button>
            </form>
        </div>

        <div class="navigation">
            <a href="home.php" class="nav-button">Back to Home</a>
        </div>
    </div>

</body>
</html>
