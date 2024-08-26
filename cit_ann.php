<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
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
    <link rel="stylesheet" href="cit_ann.css">
    <title>Citizen Announcements</title>
</head>
<body>

    <div class="announcement">
        <h1>Citizen Announcements</h1>
        <div id="announcementsContainer">
            <!-- The announcements will be populated dynamically using JavaScript -->
        </div>
    </div>
    <button onclick="window.location.href = 'citizen.php';" class="action-button">GO BACK</button>
    <button onclick="window.location.href = 'accept_history.php';" class="action-button">Your accepted history</button>
    <script src="cit_ann.js"></script>
</body>
</html>