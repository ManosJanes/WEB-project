<?php
// Assuming you have a session variable storing the logged-in user's ID
function getLoggedInRescuerId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}
?>