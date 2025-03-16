<?php
session_start();
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Live Incident Map & Login</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAfXn66vy5cwGPEt6VNTTMEPTy5y_ysYGw&libraries=places"></script>
    <script>
        var map;
        var markers = [];
        
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 40.5795, lng: -74.1502 }, // Staten Island Default
                zoom: 12
            });
            
            loadIncidents();
            setInterval(loadIncidents, 5000); // Auto-refresh every 5 sec
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
                            content: `<h3>${incident.type}</h3><p>${incident.description}</p><p><strong>Priority:</strong> ${incident.priority}</p>`
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
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        #map {
            flex: 2;
            height: 100%;
        }
        .login-container {
            flex: 1;
            padding: 20px;
            background: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body onload="initMap()">
    <div id="map"></div>
    
    <div class="login-container">
        <h2>Login</h2>
        <?php if (!isset($_SESSION["user_id"])): ?>
            <form method="POST" action="capstone.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
            </form>
            
            <h2>Register</h2>
            <form method="POST" action="register.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <button type="submit" name="register">Register</button>
            </form>

            <a href="admin_dashboard.php"><button style="background-color: #007bff;">Admin Panel</button></a>
        
        <?php else: ?>
            <h2>Welcome, <?= $_SESSION["name"] ?>!</h2>
            <a href="logout.php"><button>Logout</button></a>
        <?php endif; ?>
    </div>
</body>
</html>
