<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
    <link rel="stylesheet" href="annoucements.css">
    <title>Admin Announcement</title>
</head>
<body>

    <div class="announcement">
        <h1>Admin Announcement</h1>
        <p>We are in need of more items. If you have the items listed below, please consider sending them to us:</p>
        <div id="itemSelectorsContainer">
            <select class="itemSelector">
                <!-- The options will be populated dynamically using JavaScript -->
            </select>
        </div>
        <button type="button" onclick="addNewSelector()">Select Other</button>
        <button type="button" onclick="sendAnnouncement()">Send Announcement</button>
        <button onclick="window.location.href = 'admin.php';" class="action-button">GO BACK</button>
    </div>

    <script src="annoucements.js"></script>
</body>
</html>