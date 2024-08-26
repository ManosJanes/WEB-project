<?php
// Start or resume the session
session_start();

// Check if the user ID is set in the session
if (isset($_SESSION['user_id'])) {
    // Return the connected user's ID
    echo $_SESSION['user_id'];
} else {
    // Return an error or handle the case when the user ID is not set
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'User ID not set in the session']);
}
?>
