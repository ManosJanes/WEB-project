<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has rescuer privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
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
    <title>Item Inventory</title>
</head>
<body>
    <h2>Admin's Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Select</th>
                <th>Name</th>
                <th>Category</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody id="admin-data-output">
            <!-- Products from the admin's localStorage will be inserted here -->
        </tbody>
    </table>

    <h2>Rescuer's Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Select</th>
                <th>Name</th>
                <th>Category</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody id="rescuer-data-output">
            <!-- Products from the rescuer's localStorage will be inserted here -->
        </tbody>
    </table>

    <label for="quantityInput">Quantity:</label>
    <input type="number" id="quantityInput" min="1" value="1">

    <button id="transferButton">Transfer to Rescuer Inventory</button>
    <button id="sendButton">Send to Admin Inventory</button>
    <button onclick="window.location.href = 'rescuer.php';" class="action-button">GO BACK</button>


    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="res_items.js"></script>

</body>
</html>
