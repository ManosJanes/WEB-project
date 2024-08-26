<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
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
	<title>Welcome Rescuer</title>
</head>
<body>
   <table>
   <h1>Do you want to see your available items?</h1>
   <button onclick="window.location.href = 'res_items.php';">Your Items</button>
   <button onclick="window.location.href = 'res_map.php';">Show Map</button>
   
   </table>
 
</html>
