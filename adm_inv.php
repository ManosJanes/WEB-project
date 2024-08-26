<?php

// Read the JSON data from the request body
$jsonContent = file_get_contents('php://input');

// Decode the JSON data into an associative array
$data = json_decode($jsonContent, true);

// Check if the decoding was successful
if ($data !== null) {
    // Update the items.json file with the new data
    updateAdminJson($data);
} else {
    // Send an error response for invalid JSON data
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
}

// Function to update items.json
function updateAdminJson($data) {
    $filePath = 'items.json';

    // Encode the new data as JSON
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

    // Save the updated content back to items.json
    file_put_contents($filePath, $jsonContent);

    // Send a response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
}

?>
