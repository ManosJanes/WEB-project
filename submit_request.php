<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has citizen privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'citizen') {
    header("Location: demo.php?block=1");
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemId']) || !isset($data['peopleCount']) || !isset($data['createdAt'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Retrieve data from the request
$item_id = $data['itemId'];
$people_count = $data['peopleCount'];
$created_at = $data['createdAt'];

// Generate a unique request ID
$request_id = uniqid();

// Prepare the new request data
$newRequest = [
    'request_id' => $request_id,
    'user_id' => $_SESSION['user_id'],
    'item_id' => $item_id,
    'people_count' => $people_count,
    'status' => 'Pending',
    'accepted_at' => null,
    'completed_at' => null,
    'created_at' => $created_at  // Add the created_at field
];

// Read existing requests from file
$requests = json_decode(file_get_contents('requests.json'), true);
if (!is_array($requests)) {
    $requests = [];
}

// Add the new request to the list
$requests[] = $newRequest;

// Save the updated requests to file
file_put_contents('requests.json', json_encode($requests, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Request added successfully.']);
?>