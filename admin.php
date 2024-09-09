<?php
// admin.php
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
    <!-- Setting the pages character encoding -->
    <meta charset="UTF-8">

    <!-- Link to my stylesheet -->
    <link rel="stylesheet" href="admin.css">
    <title>Welcome Admin</title>
</head>
<body>
    <div class="container">
        <button class="go-back" onclick="window.location.href = 'demo.php';">Log Out</button>
        <h1>What do you want to see?</h1>
        <button onclick="window.location.href = 'map_greece.php';">Show Map</button>
        <button onclick="window.location.href = 'food.php';">Inventory</button>
        <button onclick="window.location.href = 'res_register.php';">Register a Rescuer</button>
        <button onclick="window.location.href = 'annoucements.php';">Make an announcement</button>
        <button onclick="window.location.href = 'charts.php';">Show Stats</button>
    </div>
</body>
</html>