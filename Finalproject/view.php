<?php
include "connection.php";

// Total spots
$totalQuery = "SELECT COUNT(*) AS total FROM parking_slots WHERE status IS NOT NULL";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalSpots = $totalRow['total'];

// Occupied spots
$occupiedQuery = "SELECT COUNT(*) AS occupied FROM parking_slots WHERE status='booked'";
$occupiedResult = mysqli_query($conn, $occupiedQuery);
$occupiedRow = mysqli_fetch_assoc($occupiedResult);
$occupiedSpots = $occupiedRow['occupied'];

// Available spots
$availableSpots = $totalSpots - $occupiedSpots;

// Occupancy rate
$rate = $totalSpots > 0 ? ($occupiedSpots / $totalSpots) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Parking Management Dashboard</title>
  <link rel="stylesheet" href="view.css" />
    <link rel="stylesheet" href="home.css" />


  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"
  />
  <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

</head>
<body>

  <header class="header">
    <div class="container">
      <div class="header-content">
        <div class="logo-section">
          <div class="logo">&#x1F17F;&#xFE0F;</div>
          <div class="title-section">
            <h1>SpotOn Dashboard</h1>
            <p class="location">&#x1F4CD; Nepal's Parking Complex</p>
          </div>
        </div>
        <nav class="nav-menu">
          <ul>
            <li><a href="view.php" class="nav-link">Dashboard</a></li>
            <li><a href="sample.php" class="nav-link">Reserve</a></li>
            <li><a href="booking_history.php" class="nav-link">My Bookings</a></li>
                        <li><a href="home.php" class="nav-link">Logout</a></li>

          </ul>
        </nav>
        <div class="status-section">
          <div class="last-updated">
            <div class="label">Last Updated</div>
            <div class="time">2:45:32 PM</div>
          </div>
          <div class="live-indicator"></div>
        </div>
      </div>
    </div>
  </header>


  <main class="main">
    <div class="container">
    
      <section class="stats-section" style="margin-top: 50px;">
  <div class="stats-grid" style="gap: 30px;">
    <div class="stat-card" style="padding: 20px;">
      <div class="stat-content">
        <div class="stat-info">
          <h3>Total Spots</h3>
          <div class="stat-value" id="totalSpots">0</div>
          <div class="stat-subtitle">Across all levels</div>
        </div>
        <div class="stat-icon blue">&#x1F697;</div>
      </div>
    </div>

    <div class="stat-card" style="padding: 20px;">
      <div class="stat-content">
        <div class="stat-info">
          <h3>Available</h3>
          <div class="stat-value" id="availableSpots">0</div>
          <div class="stat-subtitle">39.4% free</div>
        </div>
        <div class="stat-icon green">&#9989;</div>
      </div>
    </div>

    <div class="stat-card" style="padding: 20px;">
      <div class="stat-content">
        <div class="stat-info">
          <h3>Occupied</h3>
          <div class="stat-value" id="occupiedSpots">0</div>
          <div class="stat-subtitle">Currently in use</div>
        </div>
        <div class="stat-icon red">&#x1F6AB;</div>
      </div>
    </div>

    <div class="stat-card" style="padding: 20px;">
      <div class="stat-content">
        <div class="stat-info">
          <h3>Occupancy Rate</h3>
          <div class="stat-value" id="occupancyRate">0%</div>
          <div class="stat-subtitle">Overall utilization</div>
        </div>
        <div class="stat-icon purple">&#x1F4CA;</div>
      </div>
    </div>
  </div>
</section>


  <script>
  const totalSpots = <?php echo $totalSpots; ?>;
  const occupiedSpots = <?php echo $occupiedSpots; ?>;
  const availableSpots = <?php echo $availableSpots; ?>;
  const occupancyRate = <?php echo round($rate, 2); ?>;

  document.getElementById("totalSpots").innerText = totalSpots;
  document.getElementById("occupiedSpots").innerText = occupiedSpots;
  document.getElementById("availableSpots").innerText = availableSpots;
  document.getElementById("occupancyRate").innerText = occupancyRate + "%";
</script>



  

      <section class="map-section">
        <h2>Parking Locations in Kathmandu</h2>
        <div
          id="map"
          style="height: 400px; border-radius: 8px; margin-bottom: 2rem"
        ></div>

        <script>
        
        
          const parkingSpots = [
            {
              name: "New road,Kathmandu",
              lat: 27.7172,
              lng: 85.324,
              available: true,
              availableSlots: 25,
            },
            {
              name: "Kathmandu Mall",
              lat: 27.7185,
              lng: 85.32,
              available: false,
              availableSlots: 0,
            },
            {
              name: "Ranjana Complex",
              lat: 27.715,
              lng: 85.31,
              available: true,
              availableSlots: 15,
            },
            {
              name: "Rising Mall",
              lat: 27.715,
              lng: 85.31,
              available: true,
              availableSlots: 20,
            },
{
  name: "Labim Mall (Patan)",
  lat: 27.6710,
  lng: 85.3188,
  available: true,
  availableSlots: 10,
}
,
          ];

          var map = L.map("map").setView([27.7172, 85.324], 13);


          L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "&copy; OpenStreetMap contributors",
          }).addTo(map);

          // Red dot icon
var redDot = L.divIcon({
  html: '<div style="width:16px; height:16px; background:red; border-radius:50%; border:2px solid white;"></div>',
  className: "",
  iconSize: [16, 16],
  iconAnchor: [8, 8]
});

// Add red markers for main parking areas
parkingSpots.forEach((spot) => {
  L.marker([spot.lat, spot.lng], { icon: redDot })
    .addTo(map)
    .bindPopup(`<b>${spot.name}</b><br>Parking Location`);
});

        
          var markersGroup = L.layerGroup().addTo(map);

        var redDot = L.divIcon({
  html: '<div style="width:16px; height:16px; background:red; border-radius:50%; border:2px solid white;"></div>',
  className: "",
  iconSize: [16, 16],
  iconAnchor: [8, 8]
});

parkingSpots.forEach((spot) => {

  var marker = L.marker([spot.lat, spot.lng], { icon: redDot }).addTo(map);

  var popupContent = `
    <div class="popup-link" style="cursor:pointer;">
      <b>${spot.name}</b><br>Parking Location
    </div>
  `;

  marker.bindPopup(popupContent);

  marker.on("mouseover", function () {
    this.openPopup();
  });

  marker.on("mouseout", function () {
    this.closePopup();
  });

  marker.on("popupopen", function () {
    document.querySelector(".popup-link").addEventListener("click", function () {
      window.location.href = "sample.php";
    });
  });

}); 
          var greenDot = L.divIcon({
            html:
              '<div style="width:16px; height:16px; background:green; border-radius:50%; border: 2px solid white;"></div>',
            className: "",
            iconSize: [16, 16],
            iconAnchor: [8, 8],
          });

          var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
          }).addTo(map);

       
          geocoder.on("markgeocode", function (e) {
            var center = e.geocode.center;
            map.setView(center, 15);

            markersGroup.clearLayers();

            parkingSpots.forEach((spot) => {
              var spotLatLng = L.latLng(spot.lat, spot.lng);
              var distance = center.distanceTo(spotLatLng);

              if (spot.available && distance < 2000) {
                L.marker(spotLatLng, { icon: greenDot })
                  .addTo(markersGroup)
                  .bindPopup(
                    `<b>${spot.name}</b><br>Parking Available<br>Slots Available: ${spot.availableSlots}`
                  );
              }
            });
          });
        </script>
      </section>


    </div>

  </main>
      <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-brand">
            <span class="footer-logo">&#x1F17F;&#xFE0F; </span>
            <span class="footer-title">SpotOn</span>
          </div>
          <p class="footer-text">Making parking simple, secure, and convenient for everyone.</p>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 SpotOn. All rights reserved.</p>
        </div>
      </div>
    </footer>
</body>
</html>