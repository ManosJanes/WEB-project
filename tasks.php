<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has rescuer privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    header("Location: demo.php?block=1");
    exit();
}

// Fetch the rescuer's information
$rescuerId = $_SESSION['user_id'];

// SQL query to get rescuer name and surname
$sql = "SELECT r.res_id, u.name AS rescuerName, u.surname AS rescuerSurname
        FROM rescuer r
        JOIN users u ON r.res_id = u.id
        WHERE r.res_id = ?";

// Prepare and bind
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $rescuerId);
$stmt->execute();
$result = $stmt->get_result();
$rescuer = $result->fetch_assoc();

$rescuerName = $rescuer['rescuerName'] ?? '';
$rescuerSurname = $rescuer['rescuerSurname'] ?? '';

$stmt->close();

// Load JSON data
$requestsJson = file_get_contents('requests.json');
$announcementsJson = file_get_contents('announcements.json');

$requests = json_decode($requestsJson, true);
$announcements = json_decode($announcementsJson, true);

// Initialize empty arrays in case of null
if (!$requests) {
    $requests = [];
}
if (!$announcements) {
    $announcements = [];
}

// Normalize data structure
foreach ($requests as &$request) {
    // Ensure consistent naming for rescuer name and surname
    if (isset($request['rescuerName']) && !isset($request['rescuer_name'])) {
        $request['rescuer_name'] = $request['rescuerName'];
    }
    if (isset($request['rescuerSurname']) && !isset($request['rescuer_surname'])) {
        $request['rescuer_surname'] = $request['rescuerSurname'];
    }

    // Ensure all necessary fields are present and consistent
    $request['rescuer_name'] = $request['rescuer_name'] ?? '';
    $request['rescuer_surname'] = $request['rescuer_surname'] ?? '';
    $request['created_at'] = $request['created_at'] ?? '';
}

// Function to fetch user details by user_id
function fetchUserDetails($con, $userId) {
    $sql = "SELECT name, surname, phone FROM users WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

// Fetch all user details in a single query for better performance
function fetchAllUserDetails($con) {
    $sql = "SELECT id, name, surname, phone FROM users";
    $result = $con->query($sql);
    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[$user['id']] = $user;
    }
    return $users;
}

$allUserDetails = fetchAllUserDetails($con);

// Filter requests and announcements
$acceptedRequests = [];
$processedRequests = [];

foreach ($requests as $request) {
    $request_id = $request['request_id'];

    // Skip processing if the request is not accepted
    if ($request['status'] !== 'Accepted by rescuer') {
        continue;
    }

    // Add request_id to the processed list
    if (!in_array($request_id, $processedRequests)) {
        $processedRequests[] = $request_id;

        if ($request['rescuer_name'] === $rescuerName && $request['rescuer_surname'] === $rescuerSurname) {
            $acceptedRequests[] = $request;  // Add request to the list
        }
    }
}

$acceptedAnnouncements = [];
foreach ($announcements as $announcement) {
    foreach ($announcement['items'] as $item) {
        // Check if the item has an accepted_at date and matches the rescuer's name and surname
        if (empty($item['delivery_completion_date']) && !empty($item['rescuer_acceptance_date']) &&
            $item['rescuer_first_name'] === $rescuerName &&
            $item['rescuer_last_name'] === $rescuerSurname) {

            // Fetch user details for each announcement's citizen
            $userDetails = fetchUserDetails($con, $item['citizen_id']);
            
            $acceptedAnnouncements[] = [
                'announcement_id' => $announcement['announcement_id'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'citizen_acceptance_date' => $item['citizen_acceptance_date'],
                'rescuer_first_name' => $item['rescuer_first_name'],
                'rescuer_last_name' => $item['rescuer_last_name'],
                'userName' => $userDetails['name'] ?? '',
                'userSurname' => $userDetails['surname'] ?? '',
                'userPhone' => $userDetails['phone'] ?? ''
            ];
        }
    }
}

// Append user details to requests
foreach ($acceptedRequests as &$request) {
    $userDetails = $allUserDetails[$request['user_id']] ?? [];
    $request['userName'] = $userDetails['name'] ?? '';
    $request['userSurname'] = $userDetails['surname'] ?? '';
    $request['userPhone'] = $userDetails['phone'] ?? '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="tasks.css">
    <title>Accepted Tasks</title>
    <script src="tasks.js"></script>
</head>
<body>
    <h1>Accepted Requests</h1>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Item ID</th>
            <th>People Count</th>
            <th>Created At</th>
            <th>User Name</th>
            <th>User Surname</th>
            <th>User Phone</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($acceptedRequests as $request): ?>
        <tr>
            <td><?php echo htmlspecialchars($request['request_id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['item_id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['people_count'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['created_at'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['userName'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['userSurname'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($request['userPhone'] ?? ''); ?></td>
            <td>
                <button onclick="cancelTask('cancel', '<?php echo $request['request_id']; ?>', 'request')">Cancel</button>
                <button onclick="updateTask('complete', '<?php echo $request['request_id']; ?>', 'request')">Complete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h1>Accepted Announcements</h1>
    <table>
        <tr>
            <th>Announcement ID</th>
            <th>Item ID</th>
            <th>Quantity</th>
            <th>Created At</th>
            <th>User Name</th>
            <th>User Surname</th>
            <th>User Phone</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($acceptedAnnouncements as $announcement): ?>
        <tr>
            <td><?php echo htmlspecialchars($announcement['announcement_id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['item_id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['quantity'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['citizen_acceptance_date'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['userName'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['userSurname'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($announcement['userPhone'] ?? ''); ?></td>
            <td>
                <button onclick="cancelTask('cancel', '<?php echo $announcement['item_id']; ?>', 'announcement')">Cancel</button>
                <button onclick="handleAnnouncementCompletion('<?php echo $announcement['item_id']; ?>')">Complete</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>