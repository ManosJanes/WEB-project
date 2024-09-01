<?php
include 'access.php';
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    echo json_encode(['success' => false, 'message' => 'User not authorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$type = $data['type'];

if ($type == 'request') {
    $jsonFile = 'requests.json';
    $tasks = json_decode(file_get_contents($jsonFile), true);
    foreach ($tasks as &$task) {
        if ($task['request_id'] == $id) {
            $task['status'] = 'Pending';
            $task['accepted_at'] = null;
            $task['rescuerName'] = null;
            $task['rescuerSurname'] = null;
            break;
        }
    }
} else if ($type == 'announcement') {
    $jsonFile = 'announcements.json';
    $tasks = json_decode(file_get_contents($jsonFile), true);
    foreach ($tasks as &$announcement) {
        foreach ($announcement['items'] as &$item) {
            if ($item['item_id'] == $id) {
                $item['rescuer_acceptance_date'] = null;
                $item['rescuer_first_name'] = null;
                $item['rescuer_last_name'] = null;
                break 2;
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid task type']);
    exit();
}

file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
echo json_encode(['success' => true]);
?>
