<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON content type

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = 'debug.log';
file_put_contents($logFile, "Starting PHP script\n", FILE_APPEND);

if (!isset($_SESSION['user_id'])) {
    file_put_contents($logFile, "User not logged in\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents($logFile, "Data received: " . print_r($data, true) . "\n", FILE_APPEND);

if (!isset($data['announcement_id']) || !isset($data['item_id']) || !isset($data['quantity']) || !isset($data['citizen_acceptance_date'])) {
    file_put_contents($logFile, "Invalid data\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$annId = $data['announcement_id'];
$itemId = $data['item_id'];
$quantity = $data['quantity'];
$citizenAcceptanceDate = $data['citizen_acceptance_date'];

$announcementsFile = 'announcements.json';

if (file_exists($announcementsFile)) {
    $announcementsData = json_decode(file_get_contents($announcementsFile), true);
    
    if (!is_array($announcementsData)) {
        file_put_contents($logFile, "Invalid JSON format in announcements file\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON format in announcements file']);
        exit();
    }

    $updated = false;
    foreach ($announcementsData as &$announcement) {
        if (isset($announcement['announcement_id']) && $announcement['announcement_id'] == $annId) {
            foreach ($announcement['items'] as &$item) {
                if (isset($item['item_id']) && $item['item_id'] === $itemId && empty($item['citizen_id'])) {
                    $item['citizen_id'] = $user_id;
                    $item['quantity'] = $quantity;
                    $item['citizen_acceptance_date'] = $citizenAcceptanceDate;
                    
                    if (file_put_contents($announcementsFile, json_encode($announcementsData, JSON_PRETTY_PRINT)) !== false) {
                        $updated = true;
                    }
                    break 2;
                }
            }
        }
    }

    if ($updated) {
        file_put_contents($logFile, "Debug info: Starting JSON output\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Item accepted successfully']);
    } else {
        file_put_contents($logFile, "Item not found or already accepted\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Item not found or already accepted']);
    }
} else {
    file_put_contents($logFile, "Announcements file not found\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Announcements file not found']);
}
?>
