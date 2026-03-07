<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if user has an active booking
$activeBookingCheck = mysqli_query($conn, "
    SELECT reserve_id, start_time, end_time
    FROM reserve
    WHERE user_id = $user_id
      AND start_time <= NOW()
      AND end_time >= NOW()
");
$activeBooking = mysqli_fetch_assoc($activeBookingCheck);

// Get latest reservation for vehicle type & location
$latestReserve = mysqli_query($conn, "
    SELECT vehicle_type, location
    FROM reserve
    WHERE user_id = $user_id
    ORDER BY reserve_id DESC
    LIMIT 1
");
$reserveData = mysqli_fetch_assoc($latestReserve);

$vehicleType = $reserveData['vehicle_type'] ?? '';
$location = $reserveData['location'] ?? 'kathmandu mall';

// Determine slot limit
$slotLimit = ($vehicleType === 'two') ? 10 : 5;

// Fetch parking slots
$result = mysqli_query($conn, "
    SELECT ps.slot_no, ps.status
    FROM parking_slots ps
    WHERE ps.location = '$location'
    AND ps.vehicle_type = '$vehicleType'
    ORDER BY ps.slot_no
");

// Mark booked slots
$slots = [];
while($row = mysqli_fetch_assoc($result)){
    $slotNo = $row['slot_no'];
    $check = mysqli_query($conn, "
        SELECT reserve_id
        FROM reserve
        WHERE location = '$location'
          AND vehicle_type = '$vehicleType'
          AND start_time <= NOW()
          AND end_time >= NOW()
          AND reserve_id = $slotNo
    ");
    if(mysqli_num_rows($check) > 0){
        $row['status'] = 'booked';
    }
    $slots[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Parking Management Dashboard</title>
<link rel="stylesheet" href="view.css" />

<style>
body { font-family: Arial; background:#f7f7f7; margin:0; padding:0; }
.parking-grid {
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:15px;
    max-width:400px;
    margin:30px auto;
}
.slot-btn {
    padding:20px;
    font-size:18px;
    border:none;
    border-radius:8px;
    background:#ccc;
    cursor:pointer;
}
.slot-btn:hover { background:#aaa; }
.slot-btn.selected { background:green; color:white; }
.slot-btn.booked { background:red; color:white; cursor:not-allowed; }
.book-btn {
    padding:10px 20px;
    font-size:16px;
    border:none;
    border-radius:5px;
    background:#4CAF50;
    color:white;
    cursor:pointer;
}
.details-box {
    max-width:400px;
    margin:20px auto;
    padding:15px;
    border:1px solid #ccc;
    border-radius:8px;
    background:white;
    display:none;
}
</style>
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
    </div>
  </div>
</header>
<h2 style="text-align:center;">Parking Slot Booking</h2>

<form id="slotForm">
<input type="hidden" id="selected_slot" name="slot_no">

<div class="parking-grid">
<?php foreach ($slots as $slot): ?>
    <button
        type="button"
        class="slot-btn <?= ($slot['status'] === 'booked') ? 'booked' : '' ?>"
        data-spot="<?= $slot['slot_no'] ?>"
        data-location="<?= $location ?>"
        data-vehicle="<?= $vehicleType ?>"
        <?= ($slot['status'] === 'booked') ? 'disabled' : '' ?>
    >
        <?= $slot['slot_no'] ?> - <?= ($slot['status'] === 'booked') ? 'Booked' : 'Free' ?>
    </button>
<?php endforeach; ?>
</div>

<!-- SLOT DETAILS (NEW ADDITION ONLY) -->
<div id="slotDetails" class="details-box">
    <h3>Selected Slot Details</h3>
    <p><strong>Location:</strong> <span id="detail_location"></span></p>
    <p><strong>Slot No:</strong> <span id="detail_slot"></span></p>
    <p><strong>Vehicle Type:</strong> <span id="detail_vehicle"></span></p>
</div>
<div style="text-align:center; margin:20px; display:flex; justify-content:center; gap:15px;">

    <button type="button" onclick="goBack()" 
    style="padding:10px 20px; font-size:16px; border:none; border-radius:5px; background:#777; color:white; cursor:pointer;">
        Back
    </button>

    <button type="submit" class="book-btn">
        Book Slot
    </button>

</div>
</form>

<script>
function goBack() {
    window.history.back();
}

const form = document.getElementById('slotForm');
const hiddenInput = document.getElementById('selected_slot');
const buttons = document.querySelectorAll('.slot-btn');
let selectedButton = null;
let alreadyBooked = false;

// Slot selection
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.classList.contains('booked')) return;

        buttons.forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        hiddenInput.value = btn.dataset.spot;
        selectedButton = btn;

        // ✅ ONLY ADDED THIS PART
        document.getElementById('detail_location').innerText = btn.dataset.location;
        document.getElementById('detail_slot').innerText = btn.dataset.spot;
        document.getElementById('detail_vehicle').innerText =
            btn.dataset.vehicle === 'two' ? 'Two Wheeler' : 'Four Wheeler';
        document.getElementById('slotDetails').style.display = 'block';
    });
});

// Booking (UNCHANGED)
form.addEventListener('submit', (e) => {
    e.preventDefault();

    if (!hiddenInput.value) {
        alert("Please select a slot");
        return;
    }

    if (alreadyBooked) {
        alert("You can only book one slot at a time!");
        window.location.href = 'booking_history.php';
        return;
    }

    fetch('book_slot.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'slot_no=' + hiddenInput.value
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);

        if(selectedButton){
            selectedButton.classList.remove('selected');
            selectedButton.classList.add('booked');
            selectedButton.innerText = selectedButton.dataset.spot + ' - Booked';
            selectedButton.disabled = true;
            hiddenInput.value = '';
        }

        alreadyBooked = true;

        let goHistory = confirm("Do you want to go to your booking history?");
        if(goHistory){
            window.location.href = 'booking_history.php';
        }
    });
});
</script>

</body>
</html>