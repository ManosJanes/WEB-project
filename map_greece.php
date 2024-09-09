<?php
include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: demo.php?block=1");
    exit();
}

// Fetch the admin's details from the database
$sqlAdminDetails = "SELECT id, name, surname FROM users WHERE id = ?";
$stmtAdminDetails = $con->prepare($sqlAdminDetails);
$stmtAdminDetails->bind_param("i", $_SESSION['user_id']);
$stmtAdminDetails->execute();
$adminResult = $stmtAdminDetails->get_result();
$adminDetails = $adminResult->fetch_assoc();

if (isset($_POST['update_marker']) && $_POST['update_marker'] == true) {
    $newLat = $_POST['lat'];
    $newLng = $_POST['lng'];

    $sqlUpdate = "UPDATE admin SET adm_lat = ?, adm_lng = ? WHERE adm_id = ?";
    $stmtUpdate = $con->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ddi", $newLat, $newLng, $_SESSION['user_id']);
    $stmtUpdate->execute();

    if ($stmtUpdate->error) {
        error_log("Error updating position: " . $stmtUpdate->error);
        echo json_encode(['success' => false, 'message' => 'Error updating position.']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Position updated successfully.']);
    exit();
}

// Fetch the admin's position
$sqlAdminMarker = "SELECT * FROM admin WHERE adm_id = ?";
$stmtAdminMarker = $con->prepare($sqlAdminMarker);
$stmtAdminMarker->bind_param("i", $_SESSION['user_id']);
$stmtAdminMarker->execute();
$adminResult = $stmtAdminMarker->get_result();
$adminMarker = $adminResult->fetch_assoc();

// Fetch the rescuer's position and details
$sqlRescuerMarker = "SELECT u.id AS res_id, u.name AS res_name, u.surname AS res_surname, u.phone AS res_phone, r.res_lat, r.res_lng
    FROM users u
    JOIN rescuer r ON u.id = r.res_id";
$resultRescuerMarker = $con->query($sqlRescuerMarker);

if (!$resultRescuerMarker) {
    die("Error in fetching rescuer marker: " . $con->error);
}

$rescuerMarkers = [];
while ($rescuerMarker = $resultRescuerMarker->fetch_assoc()) {
    $rescuerId = $rescuerMarker['res_id'];

    // Load and decode the JSON files
    $jsonAnnouncementsData = file_get_contents('announcements.json');
    $announcements = json_decode($jsonAnnouncementsData, true);

    $jsonRequestsData = file_get_contents('requests.json');
    $requests = json_decode($jsonRequestsData, true);

    // Filter announcements for the rescuer
    $filteredAnnouncements = [];
    foreach ($announcements as $announcement) {
        foreach ($announcement['items'] as $item) {
            if (isset($item['rescuer_first_name'], $item['rescuer_last_name']) &&
                $item['rescuer_first_name'] === $rescuerMarker['res_name'] &&
                $item['rescuer_last_name'] === $rescuerMarker['res_surname'] &&
                is_null($item['delivery_completion_date'])) {
                $filteredAnnouncements[] = [
                    'citizen_acceptance_date' => $item['citizen_acceptance_date'] ?? null,
                    'announcement_id' => $announcement['announcement_id'] ?? null,
                    'item_id' => $item['itemId'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'citizen_id' => $item['citizen_id'] ?? null  // Add citizen_id
                ];
            }
        }
    }

    // Filter requests for the rescuer
    $filteredRequests = [];
    foreach ($requests as $request) {
        if (isset($request['rescuer_id'], $request['status']) &&
            $request['rescuer_id'] == $rescuerId &&
            $request['status'] === 'Accepted by rescuer') {
            $filteredRequests[] = [
                'request_id' => $request['request_id'] ?? null,
                'item_id' => $request['item_id'] ?? null,
                'people_count' => $request['people_count'] ?? null,
                'accepted_at' => $request['accepted_at'] ?? null,
                'completed_at' => $request['completed_at'] ?? null,
                'citizen_id' => $request['user_id'] ?? null  // Add citizen_id
            ];
        }
    }

    $rescuerMarkers[] = [
        'res_id' => $rescuerId,
        'res_name' => $rescuerMarker['res_name'],
        'res_surname' => $rescuerMarker['res_surname'],
        'res_phone' => $rescuerMarker['res_phone'],
        'res_lat' => $rescuerMarker['res_lat'],
        'res_lng' => $rescuerMarker['res_lng'],
        'announcements' => $filteredAnnouncements,
        'requests' => $filteredRequests
    ];
}

// Fetch the citizen's position
$sqlCitizenMarker = "SELECT * FROM citizen";
$resultCitizenMarker = $con->query($sqlCitizenMarker);

if (!$resultCitizenMarker) {
    die("Error in fetching citizen marker: " . $con->error);
}

// Load and decode the JSON files
$jsonRequestsData = file_get_contents('requests.json');
$requests = json_decode($jsonRequestsData, true);

$citizenData = [];

// Collect all requests by user ID, excluding completed ones
foreach ($requests as $request) {
    if ($request['status'] === 'Completed') {
        continue;
    }

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

    if ($stmtCitizens === false) {
        die("Error in preparing statement: " . $con->error);
    }

    $types = str_repeat('i', count($citizenIds));
    $stmtCitizens->bind_param($types, ...$citizenIds);
    $stmtCitizens->execute();
    $resultCitizens = $stmtCitizens->get_result();

    while ($citizenMarker = $resultCitizens->fetch_assoc()) {
        $citizenId = $citizenMarker['cit_id'];
        $citizenMarkers[] = array_merge($citizenMarker, $citizenData[$citizenId]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map {
            height: 80vh;
        }
        .filter-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .filter-container button {
            margin: 0 10px;
        }
        .filter-container button.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body style="margin: 0;">
    <div class="filter-container">
        <button id="toggleAcceptedRequests">Toggle Accepted Requests</button>
        <button id="togglePendingRequests">Toggle Pending Requests</button>
        <button id="toggleAnnouncements">Toggle Announcements</button>
        <button id="toggleActiveTasks">Toggle Active Tasks</button>
        <button id="toggleInactiveTasks">Toggle Inactive Tasks</button>
        <button id="toggleLines">Toggle Lines</button>
    </div>
    <div id="map" data-admin-lat="<?php echo $adminMarker['adm_lat']; ?>"
         data-admin-lng="<?php echo $adminMarker['adm_lng']; ?>"
         data-rescuer-markers='<?php echo json_encode($rescuerMarkers); ?>'
         data-citizen-markers='<?php echo json_encode($citizenMarkers); ?>'></div>

    <div>
        <button id="changeLocation">Change Location</button>
        <button onclick="window.location.href = 'admin.php';" class="action-button">GO BACK</button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var map = L.map('map').setView([
            parseFloat(document.getElementById('map').dataset.adminLat),
            parseFloat(document.getElementById('map').dataset.adminLng)
        ], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Define the custom icons for admin, rescuer, and citizen
        var adminIcon = L.icon({
            iconUrl: 'blue.png',
            iconSize: [45, 50],  // Icon width and height
            iconAnchor: [22, 50], // Anchor point of the icon
            popupAnchor: [1, -34] // Popup anchor offset
        });

        var rescuerIcon = L.icon({
            iconUrl: 'red.png',
            iconSize: [45, 50],
            iconAnchor: [22, 50],
            popupAnchor: [1, -34]
        });

        var citizenIcon = L.icon({
            iconUrl: 'purple.png',
            iconSize: [45, 50],
            iconAnchor: [22, 50],
            popupAnchor: [1, -34]
        });

        // Add the admin marker with custom icon
        var adminLat = parseFloat(document.getElementById('map').dataset.adminLat);
        var adminLng = parseFloat(document.getElementById('map').dataset.adminLng);
        var adminMarker = L.marker([adminLat, adminLng], { icon: adminIcon }).addTo(map);

        // Parse the rescuer and citizen marker data from the dataset
        var rescuerMarkers = JSON.parse(document.getElementById('map').dataset.rescuerMarkers);
        var citizenMarkers = JSON.parse(document.getElementById('map').dataset.citizenMarkers);

        var toggles = {
            acceptedRequests: true,
            pendingRequests: true,
            announcements: true,
            activeTasks: false,
            inactiveTasks: false
        };

        var markerLayers = [];
        var polylines = [];

        // Define the function to create popups
        function createPopupContent(marker) {
            var popupContent = '';

            if (marker.res_name && marker.res_surname) {
                popupContent = '<strong>' + marker.res_name + ' ' + marker.res_surname + '</strong><br>' +
                    'Phone: ' + marker.res_phone + '<br>';
            } else if (marker.cit_name && marker.cit_surname) {
                popupContent = '<strong>' + marker.cit_name + ' ' + marker.cit_surname + '</strong><br>' +
                    'Phone: ' + marker.cit_phone + '<br>';
            }

            if (toggles.announcements && marker.announcements && marker.announcements.length > 0) {
                popupContent += '<strong>Pending Announcements:</strong><ul>';
                marker.announcements.forEach(function (announcement) {
                    popupContent += '<li>Item ID: ' + announcement.item_id + ', Quantity: ' + announcement.quantity + '</li>';
                });
                popupContent += '</ul>';
            }

            if (toggles.acceptedRequests && marker.requests && marker.requests.length > 0) {
                popupContent += '<strong>Accepted Requests:</strong><ul>';
                marker.requests.forEach(function (request) {
                    popupContent += '<li>Request ID: ' + request.request_id + ', Item ID: ' + request.item_id + ', People Count: ' + request.people_count + ', Accepted At: ' + request.accepted_at + '</li>';
                });
                popupContent += '</ul>';
            }

            return popupContent;
        }

        // Add rescuer markers with custom icon
        rescuerMarkers.forEach(function (markerData) {
            var marker = L.marker([markerData.res_lat, markerData.res_lng], { icon: rescuerIcon })
                .bindPopup(createPopupContent(markerData))
                .addTo(map);

            markerLayers.push({
                marker: marker,
                data: markerData
            });
        });

        // Add citizen markers with custom icon
        citizenMarkers.forEach(function (markerData) {
            var marker = L.marker([markerData.cit_lat, markerData.cit_lng], { icon: citizenIcon })
                .bindPopup(createPopupContent(markerData))
                .addTo(map);

            markerLayers.push({
                marker: marker,
                data: markerData
            });
        });

        // Draw polylines between rescuers and citizens
        function drawPolylines() {
            polylines.forEach(function (polyline) {
                map.removeLayer(polyline);
            });
            polylines = [];

            rescuerMarkers.forEach(function (rescuer) {
                rescuer.announcements.forEach(function (announcement) {
                    var citizen = citizenMarkers.find(c => c.cit_id == announcement.citizen_id);
                    if (citizen) {
                        var latlngs = [
                            [rescuer.res_lat, rescuer.res_lng],
                            [citizen.cit_lat, citizen.cit_lng]
                        ];
                        var polyline = L.polyline(latlngs, { color: 'blue' }).addTo(map);
                        polylines.push(polyline);
                    }
                });

                rescuer.requests.forEach(function (request) {
                    var citizen = citizenMarkers.find(c => c.cit_id == request.citizen_id);
                    if (citizen) {
                        var latlngs = [
                            [rescuer.res_lat, rescuer.res_lng],
                            [citizen.cit_lat, citizen.cit_lng]
                        ];
                        var polyline = L.polyline(latlngs, { color: 'green' }).addTo(map);
                        polylines.push(polyline);
                    }
                });
            });
        }

        drawPolylines();

        // Function to toggle polylines visibility
        function togglePolylines(polylines) {
            polylines.forEach(function (polyline) {
                if (map.hasLayer(polyline)) {
                    map.removeLayer(polyline);
                } else {
                    map.addLayer(polyline);
                }
            });
        }

        // Update markers based on task visibility toggles
        function updateMarkers() {
            markerLayers.forEach(function (layer) {
                var isRescuer = layer.data.res_name && layer.data.res_surname;
                var hasAnnouncements = layer.data.announcements && layer.data.announcements.length > 0;
                var hasRequests = layer.data.requests && layer.data.requests.length > 0;

                var isActive = hasAnnouncements || hasRequests;
                var isInactive = !isActive;

                var shouldShowMarker = true;

                if (isRescuer) {
                    if (toggles.activeTasks && isActive) {
                        shouldShowMarker = false;
                    }

                    if (toggles.inactiveTasks && isInactive) {
                        shouldShowMarker = false;
                    }
                }

                if (!shouldShowMarker) {
                    map.removeLayer(layer.marker);
                } else {
                    if (!map.hasLayer(layer.marker)) {
                        layer.marker.addTo(map);
                    }
                    var content = createPopupContent(layer.data);
                    layer.marker.setPopupContent(content);
                }
            });
        }

        // Event listeners for toggling marker visibility
        document.getElementById('toggleAcceptedRequests').addEventListener('click', function () {
            this.classList.toggle('active');
            toggles.acceptedRequests = !toggles.acceptedRequests;
            updateMarkers();
        });

        document.getElementById('togglePendingRequests').addEventListener('click', function () {
            this.classList.toggle('active');
            toggles.pendingRequests = !toggles.pendingRequests;
            updateMarkers();
        });

        document.getElementById('toggleAnnouncements').addEventListener('click', function () {
            this.classList.toggle('active');
            toggles.announcements = !toggles.announcements;
            updateMarkers();
        });

        document.getElementById('toggleActiveTasks').addEventListener('click', function () {
            this.classList.toggle('active');
            toggles.activeTasks = !toggles.activeTasks;
            updateMarkers();
        });

        document.getElementById('toggleInactiveTasks').addEventListener('click', function () {
            this.classList.toggle('active');
            toggles.inactiveTasks = !toggles.inactiveTasks;
            updateMarkers();
        });

        document.getElementById('toggleLines').addEventListener('click', function () {
            this.classList.toggle('active');
            togglePolylines(polylines);
        });

        // Initial update of markers and polylines
        updateMarkers();

        // Handle admin marker dragging and position update
        var wasDragged = false;

        function changeLocation() {
            if (!wasDragged) {
                adminMarker.dragging.enable();
                wasDragged = true;
            } else {
                adminMarker.dragging.disable();
                wasDragged = false;

                if (confirm('Do you want to keep this position?')) {
                    var newLatLng = adminMarker.getLatLng();
                    updateMarkerPosition(adminMarker, newLatLng.lat, newLatLng.lng);
                } else {
                    adminMarker.setLatLng([adminLat, adminLng]);
                }
            }
        }

        function updateMarkerPosition(marker, lat, lng) {
            fetch('map_greece.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    update_marker: true,
                    lat: lat,
                    lng: lng,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Position updated successfully.');
                } else {
                    alert('Error updating position. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the position.');
            });
        }

        document.getElementById('changeLocation').addEventListener('click', changeLocation);
    });
</script>

</body>
</html>
