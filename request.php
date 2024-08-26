<?php
session_start();

// Check if the user is logged in (assuming you have a login mechanism)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    // Redirect to login page or display an error message
    header("Location: demo.php?block=1");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="request.css">
    <title>Create New Request</title>
</head>
<body>
    <div class="request">
        <h1>Create a New Request</h1>
        <form id="requestForm">
            <label for="categorySelector">Select Category:</label>
            <select id="categorySelector" onchange="populateItems()">
                <!-- Categories will be populated dynamically using JavaScript -->
            </select>
            <label for="itemSelector">Select Item:</label>
            <select id="itemSelector">
                <!-- Items will be populated dynamically using JavaScript -->
            </select>
            <label for="peopleCount">Number of People:</label>
            <input type="number" id="peopleCount" required>
            <button type="submit">Submit Request</button>
        </form>
    </div>
    <button onclick="window.location.href = 'citizen.php';" class="action-button">Go Back</button>
    <button onclick="window.location.href = 'view_requests.php';" class="action-button">View Your Requests</button>
    <script src="request.js"></script>
</body>
</html>
