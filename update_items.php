<?php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON data from the request body
    $jsonContent = file_get_contents('php://input');
    
    // Decode the JSON data into an associative array
    $data = json_decode($jsonContent, true);

    // Check if the decoding was successful
    if ($data !== null) {
        // Update the items.json file with the new data
        updateItemsJson($data);

        // Send a response
        echo json_encode(['success' => true]);
    } else {
        // Send an error response for invalid JSON data
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
    }
} else {
    // Send an error response for unsupported request methods
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Function to update items.json without merging
function updateItemsJson($data) {
    $filePath = 'items.json';

    // Encode the new data as JSON
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT);

    // Save the updated content back to items.json, overwriting the existing content
    file_put_contents($filePath, $jsonContent);
}

?>
