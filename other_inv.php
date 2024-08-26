<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to the login page or display an error message
    header("Location: demo.php?block=1");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="items.css">
    <title>Rescuer's Inventory</title>
</head>
<body>
    <h2>Rescuer's Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Detail</th>
                <th>Rescuer ID</th>
            </tr>
        </thead>
        <tbody id="rescuer-data-output">
            <!-- Products from the rescuer.json file will be inserted here -->
        </tbody>
    </table>

    <div class="button-container">
    <button onclick="window.location.href = 'food.php';" class="action-button">GO BACK</button>
  </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="other_inv.js"></script>
</body>
</html>

