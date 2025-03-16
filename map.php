<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Live Incident Map</title>
    
    <!-- Load Google Maps & Places API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAfXn66vy5cwGPEt6VNTTMEPTy5y_ysYGw&libraries=places"></script>
    
    <script>
        var map;
        var markers = [];
        var geocoder;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 40.5795, lng: -74.1502 }, // Staten Island Center
                zoom: 12
            });

            geocoder = new google.maps.Geocoder();
            loadIncidents();
            setInterval(loadIncidents, 5000); // Auto-refresh every 5 sec

            // Initialize Google Places Autocomplete for Address Input
            var addressInput = document.getElementById("address");
            var autocomplete = new google.maps.places.Autocomplete(addressInput);
            autocomplete.setFields(["formatted_address", "geometry"]);

            autocomplete.addListener("place_changed", function () {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    alert("No details available for this address.");
                    return;
                }
                
                // Auto-fill the latitude & longitude
                document.getElementById("latitude").value = place.geometry.location.lat();
                document.getElementById("longitude").value = place.geometry.location.lng();
            });
        }

        function loadIncidents() {
            fetch("get_ongoing_incidents.php")
                .then(response => response.json())
                .then(data => {
                    clearMarkers();
                    data.forEach(incident => {
                        var marker = new google.maps.Marker({
                            position: { lat: parseFloat(incident.latitude), lng: parseFloat(incident.longitude) },
                            map: map,
                            icon: getIncidentIcon(incident.type),
                            title: `${incident.type} - ${incident.priority}`
                        });

                        var infoWindow = new google.maps.InfoWindow({
                            content: `
                                <h3>${incident.type}</h3>
                                <p>${incident.description}</p>
                                <p><strong>Priority:</strong> ${incident.priority}</p>
                                <p><button onclick="viewIncident(${incident.id})">View Details</button></p>
                            `
                        });

                        marker.addListener("click", function () {
                            infoWindow.open(map, marker);
                        });

                        markers.push(marker);
                    });
                })
                .catch(error => console.error("Error loading incidents:", error));
        }

        function clearMarkers() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
        }

        function getIncidentIcon(type) {
            switch (type) {
                case 'Fire': return 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
                case 'Accident': return 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png';
                case 'Theft': return 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
                default: return 'https://maps.google.com/mapfiles/ms/icons/green-dot.png';
            }
        }

        function viewIncident(id) {
            alert("Viewing incident details for ID: " + id);
        }

        function reportIncident() {
            var formData = new FormData(document.getElementById("reportForm"));
            fetch("report_incident.php", { method: "POST", body: formData })
                .then(response => response.text())
                .then(result => {
                    alert(result);
                    loadIncidents();
                })
                .catch(error => console.error("Error reporting incident:", error));
        }
    </script>
</head>
<body onload="initMap()">
    <h1>🔥 Live Incident Map (Staten Island)</h1>
    <div id="map" style="width: 100%; height: 500px;"></div>

    <h2>📢 Report an Incident</h2>
    <form id="reportForm">
        <label for="address">Address:</label>
        <input type="text" id="address" name="location" placeholder="Enter an address" required>

        <input type="text" id="latitude" name="latitude" placeholder="Latitude" readonly required>
        <input type="text" id="longitude" name="longitude" placeholder="Longitude" readonly required>

        <label for="type">Type:</label>
        <select name="type">
            <option value="Fire">🔥 Fire</option>
            <option value="Accident">🚑 Accident</option>
            <option value="Theft">🚨 Theft</option>
            <option value="Other">❓ Other</option>
        </select>

        <label for="priority">Priority:</label>
        <select name="priority">
            <option value="Low">🟢 Low</option>
            <option value="Medium">🟡 Medium</option>
            <option value="High">🔴 High</option>
        </select>

        <input type="text" name="description" placeholder="Description" required>
        <button type="button" onclick="reportIncident()">Submit</button>
    </form>
</body>
</html>
