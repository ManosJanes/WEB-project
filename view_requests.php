<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has citizen privileges
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
    <link rel="stylesheet" href="view_requests.css">
    <title>View Requests</title>
</head>
<body>
    <input type="hidden" id="userId" value="<?php echo $_SESSION['user_id']; ?>" />
    <div class="requests">
        <h1>Your Requests</h1>
        <div id="requestsContainer">
            <!-- The requests will be populated dynamically using JavaScript -->
        </div>
    </div>
    <button onclick="window.location.href = 'citizen.php';" class="action-button">GO BACK</button>
    <button onclick="window.location.href = 'request.php';" class="action-button">New Request</button>
    <script src="view_requests.js"></script>
</body>
</html>
