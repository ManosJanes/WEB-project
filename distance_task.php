<?php
include 'access.php';
header('Content-Type: application/json');

session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και αν είναι rescuer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    echo json_encode(['success' => false, 'message' => 'User not authorized']);
    exit();
}

// Λήψη δεδομένων από το αίτημα
$data = json_decode(file_get_contents('php://input'), true);

// Καταγραφή των δεδομένων που λαμβάνονται
error_log("Received data: " . json_encode($data));

// Έλεγχος αν τα δεδομένα είναι σωστά
if (!isset($data['id']) || !isset($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$id = $data['id'];
$type = $data['type'];

// Λήψη των συντεταγμένων του διασώστη
$rescuer_id = $_SESSION['user_id'];
$rescuer_query = $con->prepare("SELECT res_lat, res_lng FROM rescuer WHERE res_id = ?");
$rescuer_query->bind_param('i', $rescuer_id);
$rescuer_query->execute();
$rescuer_result = $rescuer_query->get_result();
if ($rescuer_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rescuer not found']);
    exit();
}
$rescuer = $rescuer_result->fetch_assoc();

function haversine($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // σε χιλιόμετρα

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earth_radius * $c;

    return $distance;
}

// Λήψη των συντεταγμένων του πολίτη από τα JSON αρχεία
if ($type == 'request') {
    $requests = json_decode(file_get_contents('requests.json'), true);
    $citizen_id = null;
    foreach ($requests as $request) {
        if ($request['request_id'] == $id) {
            $citizen_id = $request['user_id'];
            break;
        }
    }
} else {
    $announcements = json_decode(file_get_contents('announcements.json'), true);
    $citizen_id = null;
    foreach ($announcements as $announcement) {
        foreach ($announcement['items'] as $item) {
            if ($item['item_id'] == $id) {
                $citizen_id = $item['citizen_id'];
                break;
            }
        }
        if ($citizen_id) break;
    }
}

if ($citizen_id === null) {
    echo json_encode(['success' => false, 'message' => 'Citizen not found']);
    exit();
}

// Λήψη των συντεταγμένων του πολίτη από τη βάση δεδομένων
$citizen_query = $con->prepare("SELECT cit_lat, cit_lng FROM citizen WHERE cit_id = ?");
$citizen_query->bind_param('i', $citizen_id);
$citizen_query->execute();
$citizen_result = $citizen_query->get_result();
if ($citizen_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Citizen not found']);
    exit();
}
$citizen = $citizen_result->fetch_assoc();

$distance = haversine($rescuer['res_lat'], $rescuer['res_lng'], $citizen['cit_lat'], $citizen['cit_lng']) * 1000; // σε μέτρα

echo json_encode(['success' => true, 'distance' => $distance]);
?>
