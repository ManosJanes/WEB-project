<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has rescuer privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    header("Location: demo.php?block=1");
    exit();
}

// Fetch the rescuer's information
$rescuerId = $_SESSION['user_id'];

// SQL query to get rescuer name and surname
$sql = "SELECT r.res_id, u.name AS rescuerName, u.surname AS rescuerSurname
        FROM rescuer r
        JOIN users u ON r.res_id = u.id
        WHERE r.res_id = ?";

// Prepare and bind
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $rescuerId);
$stmt->execute();
$result = $stmt->get_result();
$rescuer = $result->fetch_assoc();

$rescuerName = $rescuer['rescuerName'] ?? '';
$rescuerSurname = $rescuer['rescuerSurname'] ?? '';

$stmt->close();

// Load JSON data
$requestsJson = file_get_contents('requests.json');
$requests = json_decode($requestsJson, true);

// Initialize empty array in case of null
if (!$requests) {
    $requests = [];
}

// Debugging: Output raw requests data
echo "<pre>Raw Requests Data:\n";
print_r($requests);
echo "</pre>";

$acceptedRequests = [];

// Process each request and output the unique keys being checked
foreach ($requests as $request) {
    $uniqueKey = $request['request_id'] . '_' . $request['item_id'];

    echo "<pre>Processing Request: $uniqueKey\n";
    print_r($request);
    echo "</pre>";

    // Use the correct key names for rescuer name and surname
    if (isset($request['status']) && $request['status'] === 'Accepted by rescuer' &&
        $request['rescuerName'] === $rescuerName &&
        $request['rescuerSurname'] === $rescuerSurname) {

        $acceptedRequests[] = $request;  // Add request to the list
    }
}

// Debugging: Output accepted requests data
echo "<pre>Final Accepted Requests Data:\n";
print_r($acceptedRequests);
echo "</pre>";
?>
