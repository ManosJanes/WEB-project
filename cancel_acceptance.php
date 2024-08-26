<?php
session_start();

header('Content-Type: application/json'); // Ensure JSON content type is set

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['annId']) || !isset($data['itemId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$ann_id = $data['annId'];
$item_id = $data['itemId'];
$user_id = $_SESSION['user_id'];

$announcementsFile = 'announcements.json';

if (file_exists($announcementsFile)) {
    $announcementsData = json_decode(file_get_contents($announcementsFile), true);

    if (!is_array($announcementsData)) {
        echo json_encode(['success' => false, 'message' => 'Invalid announcements data format']);
        exit();
    }

    $updated = false;

    // Iterate through announcements and items to update the data
    foreach ($announcementsData as &$announcement) {
        if (isset($announcement['announcement_id']) && $announcement['announcement_id'] == $ann_id) {
            foreach ($announcement['items'] as &$item) {
                if (isset($item['item_id']) && $item['item_id'] === $item_id && isset($item['citizen_id']) && $item['citizen_id'] == $user_id) {
                    // Remove acceptance
                    $item['citizen_id'] = null;
                    $item['quantity'] = null;
                    $item['citizen_acceptance_date'] = null;

                    $updated = true;
                    break 2;
                }
            }
        }
    }

    if ($updated) {
        // Save the updated announcements data
        file_put_contents($announcementsFile, json_encode($announcementsData, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Acceptance canceled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found or not accepted by this user']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Announcements file not found']);
}
?>
