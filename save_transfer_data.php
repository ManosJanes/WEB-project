<?php

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $postData = file_get_contents("php://input");

    // Decode the JSON data
    $transferData = json_decode($postData, true);

    // Check if decoding was successful
    if ($transferData !== null) {
        // Process and save the transfer data as needed
        saveTransferData($transferData);

        // Send a success response
        http_response_code(200);
        echo json_encode(['message' => 'Transfer data saved successfully']);
    } else {
        // Send an error response for invalid JSON
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
    }
} else {
    // Send an error response for unsupported request method
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Function to process and save transfer data
function saveTransferData($transferData) {
    // Extract relevant data from $transferData
    $quantityInput = $transferData['quantityInput'];
    $selectedItems = $transferData['selectedItems'];
    $rescuerId = $transferData['rescuerId'];

    $filePath = 'rescuer.json';
    file_put_contents($filePath, json_encode($transferData));

}

?>
