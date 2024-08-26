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
	<!-- Setting the pages character encoding -->
	<meta charset="UTF-8">

	 <!-- Link to my stylesheet -->
	 <link rel="stylesheet" href="admin.css">
	<title>Welcome Citizen</title>
</head>
<body>
   <table>
   <h1>Do you want to do a request?</h1>
   <button onclick="window.location.href = 'request.php';">Request</button>   
   <button onclick="window.location.href = 'cit_ann.php';">See Annoucements</button>   
   </table>
 
</html>
