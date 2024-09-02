<?php
include 'access.php';
header('Content-Type: application/json');

session_start();

// Ensure the user is logged in and is a rescuer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    echo json_encode(['success' => false, 'message' => 'User not authorized']);
    exit();
}

// Get rescuer name and surname from session
$rescuerName = $_SESSION['rescuer_name'] ?? null;
$rescuerSurname = $_SESSION['rescuer_surname'] ?? null;

if (!$rescuerName || !$rescuerSurname) {
    echo json_encode(['success' => false, 'message' => 'Rescuer information missing from session']);
    exit();
}

// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);

error_log("Received data: " . json_encode($data));

if (!isset($data['id']) || !isset($data['type']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$id = $data['id'];
$type = $data['type'];
$quantity = intval($data['quantity']);
$now = date('Y-m-d H:i:s');

function updateOrAddItem(&$rescuerData, $id, $quantity, $userId, $itemToAdd = null, $type) {
    $itemFound = false;
    error_log("Starting updateOrAddItem function with ID: $id, Quantity: $quantity, Type: $type");

    foreach ($rescuerData['items'] as &$rescuerItem) {
        error_log("Checking rescuer item: " . json_encode($rescuerItem));
        if ($rescuerItem['id'] == $id && $rescuerItem['rescuerId'] == $userId) {
            $itemFound = true;
            foreach ($rescuerItem['details'] as &$detail) {
                if ($detail['detail_name'] == 'Quantity') {
                    if ($type == 'request') {
                        if ($detail['detail_value'] < $quantity) {
                            error_log("Insufficient quantity: have " . $detail['detail_value'] . ", need $quantity");
                            return ['success' => false, 'message' => 'Insufficient quantity'];
                        } else {
                            $detail['detail_value'] -= $quantity;
                            error_log("Quantity after reduction: " . $detail['detail_value']);
                        }
                    } else if ($type == 'announcement') {
                        $detail['detail_value'] += $quantity;
                        error_log("Quantity after addition: " . $detail['detail_value']);
                    }
                    return ['success' => true];
                }
            }
        }
    }

    if (!$itemFound && $type == 'announcement' && $itemToAdd) {
        $itemToAdd['rescuerId'] = intval($userId);
        foreach ($itemToAdd['details'] as &$detail) {
            if ($detail['detail_name'] == 'Quantity') {
                $detail['detail_value'] = $quantity;
                break;
            }
        }
        $rescuerData['items'][] = $itemToAdd;
        error_log("Added new item to rescuerData: " . json_encode($itemToAdd));
        return ['success' => true];
    }

    error_log("Item not found for ID: $id and rescuerId: $userId");
    return ['success' => false, 'message' => 'Item not found'];
}

function completeRequest($id, $quantity, $now) {
    global $rescuerName, $rescuerSurname;

    $jsonFile = 'requests.json';
    $rescuerFile = 'rescuer.json';
    $tasks = json_decode(file_get_contents($jsonFile), true);
    $rescuerData = json_decode(file_get_contents($rescuerFile), true);

    $itemId = null;
    foreach ($tasks as $task) {
        if ($task['request_id'] == $id && $task['rescuerName'] == $rescuerName && $task['rescuerSurname'] == $rescuerSurname) {
            $itemId = $task['item_id'];
            break;
        }
    }

    if (!$itemId) {
        error_log("Request ID not found or not associated with rescuer: $rescuerName $rescuerSurname");
        return ['success' => false, 'message' => 'Request ID not found'];
    }

    $response = updateOrAddItem($rescuerData, $itemId, $quantity, $_SESSION['user_id'], null, 'request');

    if ($response['success']) {
        foreach ($tasks as &$task) {
            if ($task['request_id'] == $id) {
                if ($task['status'] == 'Completed') {
                    return ['success' => false, 'message' => 'Request already completed'];
                }
                $task['status'] = 'Completed';
                $task['completed_at'] = $now;
                break;
            }
        }
        file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));
        file_put_contents($rescuerFile, json_encode($rescuerData, JSON_PRETTY_PRINT));
        return ['success' => true];
    } else {
        return $response;
    }
}

function completeAnnouncement($id, $now) {
    $jsonFile = 'announcements.json';
    $itemsFile = 'items.json';
    $tasks = json_decode(file_get_contents($jsonFile), true);
    $itemsData = json_decode(file_get_contents($itemsFile), true);

    $quantity = 0;
    foreach ($tasks as &$announcement) {
        foreach ($announcement['items'] as &$item) {
            if ($item['item_id'] == $id && empty($item['delivery_completion_date'])) {
                $item['delivery_completion_date'] = $now;
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
                break 2;
            }
        }
    }

    file_put_contents($jsonFile, json_encode($tasks, JSON_PRETTY_PRINT));

    $itemToAdd = null;
    foreach ($itemsData['items'] as $item) {
        if ($item['id'] == $id) {
            $itemToAdd = $item;
            break;
        }
    }

    if ($itemToAdd) {
        $rescuerFile = 'rescuer.json';
        $rescuerData = json_decode(file_get_contents($rescuerFile), true);
        $response = updateOrAddItem($rescuerData, $id, $quantity, $_SESSION['user_id'], $itemToAdd, 'announcement');

        if ($response['success']) {
            file_put_contents($rescuerFile, json_encode($rescuerData, JSON_PRETTY_PRINT));
            return ['success' => true];
        } else {
            return $response;
        }
    } else {
        return ['success' => false, 'message' => 'Item not found in items.json'];
    }
}

$response = ['success' => false, 'message' => 'Invalid task type'];

if ($type == 'request') {
    $response = completeRequest($id, $quantity, $now);
} else if ($type == 'announcement') {
    $response = completeAnnouncement($id, $now);
}

echo json_encode($response);
?>
