<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$request_id = $data['request_id'];

$requests_file = 'requests.json';

if (file_exists($requests_file)) {
    $requests_data = json_decode(file_get_contents($requests_file), true);

    foreach ($requests_data as &$request) {
        if ($request['request_id'] == $request_id && $request['user_id'] == $user_id) {
            $request['status'] = 'Cancelled';
            $request['accepted_at'] = null;
            $request['completed_at'] = null;
            file_put_contents($requests_file, json_encode($requests_data));
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Request not found or not authorized']);
?>
