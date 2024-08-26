<?php
include 'access.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['announcement_created_at']) && isset($data['items'])) {
    $announcementCreatedAt = $data['announcement_created_at'];
    $items = $data['items'];

    $filePath = 'announcements.json';

    if (file_exists($filePath)) {
        $announcements = json_decode(file_get_contents($filePath), true);
        if (!is_array($announcements)) {
            $announcements = [];
        }
    } else {
        $announcements = [];
    }

    $annId = count($announcements) + 1;

    $newAnnouncement = [
        'announcement_id' => $annId,
        'announcement_created_at' => $announcementCreatedAt,
        'items' => $items
    ];

    $announcements[] = $newAnnouncement;

    $jsonData = json_encode($announcements, JSON_PRETTY_PRINT);
    if (file_put_contents($filePath, $jsonData) === false) {
        echo json_encode(['success' => false, 'message' => 'Error saving announcement']);
    } else {
        $_SESSION['announcement'] = $newAnnouncement;
        echo json_encode(['success' => true, 'message' => 'Announcement saved successfully']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
