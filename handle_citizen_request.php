<?php
// handle_citizen_request.php
// This script handles citizen requests and notifies the admin

// Assuming you have a database connection, include it here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the item details and the number of people from the POST request
    $type = $_POST['type'];
    $item = $_POST['item'];
    $brand = $_POST['brand'];
    $numPeople = $_POST['numPeople'];

    // Notify the admin or store the request in a database
    // You can send an email to the admin or insert the request into a table

    // For demonstration purposes, we'll just print the information
    echo "Item Requested: Type: $type, Item: $item, Brand: $brand, Number of People: $numPeople";

} else {
    // Handle the request method not being POST
    echo 'Invalid request method';
}
?>
