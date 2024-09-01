<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has rescuer privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'rescuer') {
    header("Location: demo.php?block=1");
    exit();
}

// Fetch the rescuer's details from the database
$sqlRescuerDetails = "SELECT u.id AS res_id, u.name AS res_name, u.surname AS res_surname FROM users u JOIN rescuer r ON u.id = r.res_id WHERE u.id = ?";
$stmtRescuerDetails = $con->prepare($sqlRescuerDetails);
$stmtRescuerDetails->bind_param("i", $_SESSION['user_id']);
$stmtRescuerDetails->execute();
$rescuerResult = $stmtRescuerDetails->get_result();
$rescuerDetails = $rescuerResult->fetch_assoc();

$rescuerId = $_SESSION['user_id'];
$rescuerName = $rescuerDetails['res_name'];
$rescuerSurname = $rescuerDetails['res_surname'];

// Function to count total accepted items
function countTotalAcceptedItems($rescuerName, $rescuerSurname) {
    $totalAccepted = 0;

    // Count accepted requests
    $requestsData = file_get_contents('requests.json');
    $requests = json_decode($requestsData, true);
    foreach ($requests as $request) {
        if (isset($request['rescuerName']) && isset($request['rescuerSurname']) &&
            $request['rescuerName'] === $rescuerName &&
            $request['rescuerSurname'] === $rescuerSurname &&
            $request['status'] === 'Accepted by rescuer') {
            $totalAccepted++;
        }
    }

    // Count accepted announcements
    $announcementsData = file_get_contents('announcements.json');
    $announcements = json_decode($announcementsData, true);
    foreach ($announcements as $announcement) {
        foreach ($announcement['items'] as $item) {
            if (isset($item['rescuer_first_name']) && isset($item['rescuer_last_name']) &&
                $item['rescuer_first_name'] === $rescuerName &&
                $item['rescuer_last_name'] === $rescuerSurname &&
                !empty($item['rescuer_acceptance_date']) && 
                empty($item['delivery_completion_date'])) {
                $totalAccepted++;
            }
        }
    }

    return $totalAccepted;
}

// Check if the request is for accepting an announcement
if (isset($_POST['accept_announcement']) && $_POST['accept_announcement'] == true) {
    $totalAcceptedItems = countTotalAcceptedItems($rescuerName, $rescuerSurname);

    if ($totalAcceptedItems < 4) {
        $announcementId = $_POST['announcementId'];
        $itemId = $_POST['itemId'];
        $acceptedAt = date('Y-m-d H:i:s');

        // Load and decode the JSON file
        $jsonAnnouncementsData = file_get_contents('announcements.json');
        $announcements = json_decode($jsonAnnouncementsData, true);

        // Find the specific announcement and item to update
        $announcementFound = false;
        foreach ($announcements as &$announcement) {
            if ($announcement['announcement_id'] == $announcementId) {
                foreach ($announcement['items'] as &$item) {
                    if ($item['item_id'] == $itemId) {
                        $item['rescuer_acceptance_date'] = $acceptedAt;
                        $item['rescuer_first_name'] = $rescuerName;
                        $item['rescuer_last_name'] = $rescuerSurname;
                        $announcementFound = true;
                        break 2;
                    }
                }
            }
        }

        if ($announcementFound) {
            // Save the updated JSON data back to the file
            file_put_contents('announcements.json', json_encode($announcements, JSON_PRETTY_PRINT));

            echo json_encode(['success' => true, 'message' => 'Announcement accepted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Announcement not found.']);
        }
    } else {
        // Display an error message
        echo json_encode(['success' => false, 'message' => 'You cannot accept more than 4 tasks at a time.']);
    }
    exit();
}

// Check if the request is for accepting a request
if (isset($_POST['accept_request']) && $_POST['accept_request'] == true) {
    $totalAcceptedItems = countTotalAcceptedItems($rescuerName, $rescuerSurname);

    if ($totalAcceptedItems < 4) {
        $requestId = $_POST['requestId'];
        $acceptedAt = date('Y-m-d H:i:s');

        // Load and decode the JSON file
        $jsonRequestsData = file_get_contents('requests.json');
        $requests = json_decode($jsonRequestsData, true);

        // Find the specific request to update
        $requestFound = false;
        foreach ($requests as &$request) {
            if ($request['request_id'] == $requestId) {
                $request['accepted_at'] = $acceptedAt;
                $request['rescuerName'] = $rescuerName;
                $request['rescuerSurname'] = $rescuerSurname;
                $request['status'] = "Accepted by rescuer";
                $requestFound = true;
                break;
            }
        }

        if ($requestFound) {
            // Save the updated JSON data back to the file
            file_put_contents('requests.json', json_encode($requests, JSON_PRETTY_PRINT));

            echo json_encode(['success' => true, 'message' => 'Request accepted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Request not found.']);
        }
    } else {
        // Display an error message
        echo json_encode(['success' => false, 'message' => 'You cannot accept more than 4 tasks at a time.']);
    }
    exit();
}

// Check if the request is for updating the marker
if (isset($_POST['update_marker']) && $_POST['update_marker'] == true) {
    $newLat = $_POST['lat'];
    $newLng = $_POST['lng'];

    $sqlUpdate = "UPDATE rescuer SET res_lat = ?, res_lng = ? WHERE res_id = ?";
    $stmtUpdate = $con->prepare($sqlUpdate);

    if (!$stmtUpdate) {
        error_log("Error in preparing statement: " . $con->error);
        echo json_encode(['success' => false, 'message' => 'Error preparing statement.']);
        exit();
    }

    $stmtUpdate->bind_param("ddi", $newLat, $newLng, $_SESSION['user_id']);
    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true, 'message' => 'Position updated successfully.']);
    } else {
        error_log("Error updating position: " . $stmtUpdate->error);
        echo json_encode(['success' => false, 'message' => 'Error executing statement.']);
    }
    exit();
}

// Fetch the admin's position
$sqlAdminMarker = "SELECT * FROM admin";
$resultAdminMarker = $con->query($sqlAdminMarker);

if (!$resultAdminMarker) {
    die("Error in fetching admin marker: " . $con->error);
}

$adminMarker = $resultAdminMarker->fetch_assoc();

// Fetch the rescuer's position
$sqlRescuerMarker = "SELECT * FROM rescuer WHERE res_id = ?";
$stmtRescuerMarker = $con->prepare($sqlRescuerMarker);

if (!$stmtRescuerMarker) {
    die("Error in preparing statement: " . $con->error);
}

$stmtRescuerMarker->bind_param("i", $_SESSION['user_id']);
$stmtRescuerMarker->execute();

if ($stmtRescuerMarker->error) {
    die("Error in executing statement: " . $stmtRescuerMarker->error);
}

$resultRescuerMarker = $stmtRescuerMarker->get_result();

if ($resultRescuerMarker === false) {
    die("Error in getting result: " . $con->error);
}

$rescuerMarker = $resultRescuerMarker->fetch_assoc();

// Close the result sets
$resultAdminMarker->close();
$resultRescuerMarker->close();
$stmtRescuerMarker->close();

// Load and decode the JSON files
$jsonRequestsData = file_get_contents('requests.json');
$requests = json_decode($jsonRequestsData, true);

$jsonAnnouncementsData = file_get_contents('announcements.json');
$announcements = json_decode($jsonAnnouncementsData, true);

$citizenData = [];

// Collect all requests by user ID
foreach ($requests as $request) {
    $userId = (int) $request['user_id'];
    if (!isset($citizenData[$userId])) {
        $citizenData[$userId] = ['requests' => [], 'announcements' => []];
    }
    $citizenData[$userId]['requests'][] = [
        'created_at' => $request['created_at'] ?? null,
        'request_id' => $request['request_id'] ?? null,
        'item_id' => $request['item_id'] ?? null,
        'people_count' => $request['people_count'] ?? null,
        'accepted_at' => $request['accepted_at'] ?? null,
        'completed_at' => $request['completed_at'] ?? null,
        'status' => $request['status'] ?? null
    ];
}

// Collect all announcements by user ID
foreach ($announcements as $announcement) {
    foreach ($announcement['items'] as $item) {
        $userId = (int) $item['citizen_id'];
        if ($userId === 0) {
            continue;
        }

        if (!isset($citizenData[$userId])) {
            $citizenData[$userId] = ['requests' => [], 'announcements' => []];
        }

        $citizenData[$userId]['announcements'][] = [
            'citizen_acceptance_date' => $item['citizen_acceptance_date'] ?? null,
            'announcement_id' => $announcement['announcement_id'] ?? null,
            'item_id' => $item['item_id'] ?? null,
            'quantity' => $item['quantity'] ?? null,
            'rescuer_acceptance_date' => $item['rescuer_acceptance_date'] ?? null,
            'delivery_completion_date' => $item['delivery_completion_date'] ?? null,
        ];
    }
}

$citizenIds = array_keys($citizenData);
$citizenMarkers = [];

if (!empty($citizenIds)) {
    $placeholders = implode(',', array_fill(0, count($citizenIds), '?'));

    $sqlCitizens = "
        SELECT u.id AS cit_id, u.name AS cit_name, u.surname AS cit_surname, u.phone AS cit_phone, c.cit_lat, c.cit_lng
        FROM users u
        JOIN citizen c ON u.id = c.cit_id
        WHERE u.id IN ($placeholders)";

    $stmtCitizens = $con->prepare($sqlCitizens);

    if (!$stmtCitizens) {
        die("Error in preparing statement: " . $con->error);
    }

    $stmtCitizens->bind_param(str_repeat('i', count($citizenIds)), ...$citizenIds);
    $stmtCitizens->execute();

    if ($stmtCitizens->error) {
        die("Error in executing statement: " . $stmtCitizens->error);
    }

    $resultCitizens = $stmtCitizens->get_result();

    if ($resultCitizens === false) {
        die("Error in getting result: " . $con->error);
    }

    while ($row = $resultCitizens->fetch_assoc()) {
        $citizenId = $row['cit_id'];

        $citizenMarkers[] = [
            'cit_id' => $citizenId,
            'cit_name' => $row['cit_name'],
            'cit_surname' => $row['cit_surname'],
            'cit_phone' => $row['cit_phone'],
            'cit_lat' => $row['cit_lat'],
            'cit_lng' => $row['cit_lng'],
            'requests' => $citizenData[$citizenId]['requests'],
            'announcements' => $citizenData[$citizenId]['announcements']
        ];
    }

    $stmtCitizens->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescuer Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        .red-marker-icon {
            background-color: red;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-block;
        }
        .default-marker-icon {
            background-color: blue;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-block;
        }
    </style>
</head>
<body style="margin: 0;">
    <div class="filter-container">
        <button id="toggleAcceptedRequests">Toggle Accepted Requests</button>
        <button id="togglePendingRequests">Toggle Pending Requests</button>
        <button id="toggleAnnouncements">Toggle Announcements</button>
        <button id="toggleLines">Toggle Lines</button>
    </div>
    <div id="map" style="height: 80vh;"
         data-admin-lat="<?php echo $adminMarker['adm_lat']; ?>"
         data-admin-lng="<?php echo $adminMarker['adm_lng']; ?>"
         data-rescuer-lat="<?php echo $rescuerMarker['res_lat']; ?>"
         data-rescuer-lng="<?php echo $rescuerMarker['res_lng']; ?>"
         data-citizen-markers='<?php echo json_encode($citizenMarkers); ?>'
         data-announcements='<?php echo json_encode($announcements); ?>'></div>

    <div id="controls">
        <button id="changeLocation">Change Position</button>
        <button onclick="window.location.href = 'tasks.php';">Your Tasks</button>
    </div>

    <script>
        function acceptRequest(requestId) {
            fetch('res_map.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'accept_request=true&requestId=' + requestId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Request accepted successfully.');
                    location.reload();
                } else {
                    alert('Error accepting request: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function acceptAnnouncement(announcementId, itemId) {
            fetch('res_map.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'accept_announcement=true&announcementId=' + announcementId + '&itemId=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Announcement accepted successfully.');
                    location.reload();
                } else {
                    alert('Error accepting announcement: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        document.addEventListener("DOMContentLoaded", function () {
            var mapElement = document.getElementById('map');
            
            var adminLat = mapElement.getAttribute('data-admin-lat');
            var adminLng = mapElement.getAttribute('data-admin-lng');
            var rescuerLat = mapElement.getAttribute('data-rescuer-lat');
            var rescuerLng = mapElement.getAttribute('data-rescuer-lng');
            var citizenMarkers = JSON.parse(mapElement.getAttribute('data-citizen-markers'));
            var announcements = JSON.parse(mapElement.getAttribute('data-announcements'));

            var map = L.map('map').setView([rescuerLat, rescuerLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Display marker for the admin
            L.marker([adminLat, adminLng]).addTo(map).bindPopup("Admin's marker.");

            // Display marker for the logged-in rescuer
            var rescuerMarker = L.marker([rescuerLat, rescuerLng], { draggable: false }).addTo(map);
            var originalPosition = rescuerMarker.getLatLng(); // Save the original position

            var wasDragged = false;

            function changeLocation() {
                if (!wasDragged) {
                    // Enable dragging when the button is clicked for the first time
                    rescuerMarker.dragging.enable();
                    wasDragged = true;
                } else {
                    // Disable dragging when the button is clicked again
                    rescuerMarker.dragging.disable();
                    wasDragged = false;

                    if (confirm('Do you want to keep this position?')) {
                        var newLatLng = rescuerMarker.getLatLng();
                        localStorage.setItem('markerPosition', JSON.stringify(newLatLng));

                        // Update the marker position in the same file
                        updateMarkerPosition(newLatLng.lat, newLatLng.lng)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Position updated successfully.');
                                } else {
                                    alert('Error updating position. Please try again.');
                                    // Reset the marker to its original position in case of error
                                    rescuerMarker.setLatLng(originalPosition);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while updating the position.');
                                // Reset the marker to its original position in case of error
                                rescuerMarker.setLatLng(originalPosition);
                            });
                    } else {
                        // If the user cancels, reset the marker position to the original
                        rescuerMarker.setLatLng(originalPosition);
                    }
                }
            }

            function updateMarkerPosition(lat, lng) {
                return fetch('res_map.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        update_marker: true,
                        lat: lat,
                        lng: lng,
                    }),
                });
            }

            document.getElementById('changeLocation').addEventListener('click', changeLocation);

            function addMarkersAndLines() {
                citizenLayerGroup.clearLayers();
                lineLayerGroup.clearLayers();

                var polylines = [];

                // Add citizen markers with requests and announcements
                citizenMarkers.forEach(function(marker) {
                    
                    if (marker.cit_lat && marker.cit_lng) {
                        var citizenMarker = L.marker([marker.cit_lat, marker.cit_lng]).addTo(citizenLayerGroup);
                        var popupContent = `
                            <b>Citizen:</b> ${marker.cit_name} ${marker.cit_surname}<br>
                            <b>Phone:</b> ${marker.cit_phone}<br>
                        `;

                        // Add requests to the popup content
                        marker.requests.forEach(function(request) {
                            if (request.status !== "Completed" && request.status !== "Cancelled") {
                                var requestContent = `
                                    <b>Request ID:</b> ${request.request_id}<br>
                                    <b>Item ID:</b> ${request.item_id}<br>
                                    <b>People Count:</b> ${request.people_count}<br>
                                    <b>Accepted At:</b> ${request.accepted_at}<br>
                                    <b>Status:</b> ${request.status}<br>
                                `;
                            }

                            if (request.status !== "Completed" && request.status !== "Cancelled") {
                                if (request.status !== "Accepted by rescuer") {
                                    requestContent += `
                                        <button onclick="acceptRequest('${request.request_id}')">Accept Request</button>
                                    `;
                                }
                                requestContent += `<hr>`;
                            }
                            

                            if (request.status === "Accepted by rescuer" && showAcceptedRequests) {
                                popupContent += requestContent;
                                if (showLines) {
                                    polylines.push(L.polyline([
                                        [rescuerLat, rescuerLng],
                                        [marker.cit_lat, marker.cit_lng]
                                    ], { color: 'blue' }).addTo(lineLayerGroup));
                                }
                            } else if (request.status !== "Accepted by rescuer" && showPendingRequests) {
                                popupContent += requestContent;
                            }
                        });

                        // Add announcements to the popup content
                        marker.announcements.forEach(function(announcement) {
                            if (!announcement.delivery_completion_date && showAnnouncements) {
                                var announcementContent = `
                                    <b>Announcement ID:</b> ${announcement.announcement_id}<br>
                                    <b>Item ID:</b> ${announcement.item_id}<br>
                                    <b>Quantity:</b> ${announcement.quantity}<br>
                                    <b>Created At:</b> ${announcement.citizen_acceptance_date}<br>
                                    <b>Accepted At:</b> ${announcement.rescuer_acceptance_date || 'Not accepted yet'}<br>
                                `;
                                if (!announcement.rescuer_acceptance_date) {
                                    announcementContent += `
                                        <button onclick="acceptAnnouncement('${announcement.announcement_id}', '${announcement.item_id}')">Accept Announcement</button>
                                    `;
                                }
                                announcementContent += `<hr>`;
                                popupContent += announcementContent;

                                if (announcement.rescuer_acceptance_date && showLines) {
                                    polylines.push(L.polyline([
                                        [rescuerLat, rescuerLng],
                                        [marker.cit_lat, marker.cit_lng]
                                    ], { color: 'green' }).addTo(lineLayerGroup));
                                }
                            }
                        });

                        // Bind popup to citizen marker
                        citizenMarker.bindPopup(popupContent);
                    }
                });
            }

            var citizenLayerGroup = L.layerGroup().addTo(map);
            var lineLayerGroup = L.layerGroup().addTo(map);
            var showAcceptedRequests = true;
            var showPendingRequests = true;
            var showAnnouncements = true;
            var showLines = true;

            addMarkersAndLines();

            function toggleAcceptedRequests() {
                showAcceptedRequests = !showAcceptedRequests;
                addMarkersAndLines();
            }

            function togglePendingRequests() {
                showPendingRequests = !showPendingRequests;
                addMarkersAndLines();
            }

            function toggleAnnouncements() {
                showAnnouncements = !showAnnouncements;
                addMarkersAndLines();
            }

            function toggleLines() {
                showLines = !showLines;
                addMarkersAndLines();
            }

            document.getElementById('toggleAcceptedRequests').addEventListener('click', toggleAcceptedRequests);
            document.getElementById('togglePendingRequests').addEventListener('click', togglePendingRequests);
            document.getElementById('toggleAnnouncements').addEventListener('click', toggleAnnouncements);
            document.getElementById('toggleLines').addEventListener('click', toggleLines);
        });
    </script>
</body>
</html>
