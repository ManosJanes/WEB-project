<?php
include 'access.php';

session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $citizen_id = $_POST['citizen_id'];
    
    $sql = "UPDATE citizen SET cit_lat = ?, cit_lng = ? WHERE cit_id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ddi", $lat, $lng, $citizen_id);
        $stmt->execute();
        $stmt->close();

        // Send a JSON response indicating success
        echo json_encode(['success' => true]);
        exit();
    } else {
        // Send a JSON response indicating an error
        echo json_encode(['success' => false, 'error' => $con->error]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
        }

        #confirmButton {
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Pass citizen_id as a data attribute to the map div -->
    <div id="map" data-citizen-id="<?php echo isset($_GET['user_id']) ? (int)$_GET['user_id'] : ''; ?>"></div>
    <button id="confirmButton" onclick="showConfirmationDialog()">Confirm Location</button>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Â© OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([0, 0], { draggable: true }).addTo(map);

        // Function to show the confirmation dialog
        function showConfirmationDialog() {
            var isConfirmed = confirm("Are you sure you want to confirm this location?");

            if (isConfirmed) {
                // Update the citizen's location on the server
                var markerLatLng = marker.getLatLng();
                updateCitizenLocation(markerLatLng.lat, markerLatLng.lng);
                // Disable dragging after confirming the location
                marker.dragging.disable();
            } else {
                alert("You can continue to drag the marker.");
            }
        }

        // Function to update the citizen's location on the server
        function updateCitizenLocation(lat, lng) {
            var mapElement = document.getElementById('map');
            var citizen_id = mapElement.dataset.citizenId;

            if (citizen_id) {
                fetch('register_map.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng) + '&citizen_id=' + encodeURIComponent(citizen_id),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Location confirmed and updated!");
                        // Redirect to demo.php after successful confirmation
                        window.location.href = 'demo.php';
                    } else {
                        alert("Error updating location: " + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error updating location:', error);
                });
            } else {
                alert("Invalid user ID.");
            }
        }
    </script>
</body>
</html>