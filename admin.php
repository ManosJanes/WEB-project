<?php
// admin.php
include 'access.php'; // Include for session management and access control

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
    <link rel="stylesheet" href="admin.css">
    <title>Welcome Admin</title>
</head>
<body>
    <!-- Your admin page content goes here -->
    <table>
        <button onclick="window.location.href = 'map_greece.php';">Show Map</button>
        <h1>&nbsp; &nbsp; &nbsp; &nbsp; What do you want to see?<br></br><br></br><br></br><br></h1>
        <button onclick="window.location.href = 'food.php';">Inventory</button>
        <button onclick="window.location.href = 'res_register.php';">Register a Rescuer</button>
        <button onclick="window.location.href = 'annoucements.php';">Make an Announcement</button>
        <button onclick="window.location.href = 'charts.php';">View Charts</button> <!-- New button to access charts.php -->
    </table>
</body>
</html>
