<?php
// charts.php

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
    <title>Admin Dashboard - Metrics</title>
    <link rel="stylesheet" href="charts.css"> <!-- Link to the CSS file -->
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard - Metrics</h1>
        <form id="filterForm">
            <label for="chartType">Chart Type:</label>
            <select id="chartType" name="chartType">
                <option value="bar">Bar</option>
                <option value="line">Line</option>
                <option value="pie">Pie</option>
            </select>
            
            <label for="startDate">Start Date:</label>
            <input type="date" id="startDate" name="startDate">
            
            <label for="endDate">End Date:</label>
            <input type="date" id="endDate" name="endDate">
            
            <button type="submit">Update Chart</button>
        </form>
        <div id="chart-container">
            <canvas id="myChart"></canvas>
        </div>
        <div class="button-container">
            <button onclick="window.location.href = 'admin.php';" class="action-button">GO BACK</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="charts.js"></script> <!-- Link to the JavaScript file -->
</body>
</html>
