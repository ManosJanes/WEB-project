<?php

session_start();

include 'resid_finder.php';

$rescuerId = getLoggedInRescuerId();

// Check if the request method is POST (for updating the rescuer's JSON content)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON data from the request body
    $jsonContent = file_get_contents('php://input');
    
    // Decode the JSON data into an associative array
    $data = json_decode($jsonContent, true);

    // Check if the decoding was successful
    if ($data !== null) {
        // Update the rescuer.json file with the new data
        updateRescuerJson($data);
    } else {
        // Send an error response for invalid JSON data
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
    }
} else {
    // Handle the case when the user is not logged in
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
}

// Function to update rescuer.json
function updateRescuerJson($data) {
    $filePath = 'rescuer.json';

    // Encode the new data as JSON
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

    // Save the updated content back to rescuer.json
    file_put_contents($filePath, $jsonContent);


    // Send a response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}

?>
