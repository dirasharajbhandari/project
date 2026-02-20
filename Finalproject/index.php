<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 0️⃣ Check if user already has an active booking
$activeBookingCheck = mysqli_query($conn, "
    SELECT reserve_id, start_time, end_time 
    FROM reserve 
    WHERE user_id = $user_id 
      AND start_time <= NOW() 
      AND end_time >= NOW()
");
$activeBooking = mysqli_fetch_assoc($activeBookingCheck);

// 1️⃣ Fetch all slots and check if currently reserved
$result = mysqli_query($conn, "
    SELECT ps.slot_no, ps.status,
           r.start_time, r.end_time
    FROM parking_slots ps
    LEFT JOIN reserve r 
        ON ps.slot_no = r.reserve_id
        AND r.start_time <= NOW() 
        AND r.end_time >= NOW()
    ORDER BY ps.slot_no
");

$slots = [];
while ($row = mysqli_fetch_assoc($result)) {
    // If there is a current reservation, mark as booked
    if (!empty($row['start_time']) && !empty($row['end_time'])) {
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
.parking-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:15px; max-width:400px; margin:30px auto; }
.slot-btn { padding:20px; font-size:18px; border:none; border-radius:8px; background:#ccc; cursor:pointer; }
.slot-btn:hover { background:#aaa; }
.slot-btn.selected { background:green; color:white; }
.slot-btn.booked { background:red; color:white; cursor:not-allowed; }
.book-btn { padding:10px 20px; font-size:16px; border:none; border-radius:5px; background:#4CAF50; color:white; cursor:pointer; }
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

<h2 style="text-align:center;">Parking Slot Booking</h2>

<form id="slotForm">
    <input type="hidden" id="selected_slot" name="slot_no">

    <div class="parking-grid">
        <?php foreach ($slots as $slot): ?>
            <button 
                type="button" 
                class="slot-btn <?= ($slot['status'] === 'booked') ? 'booked' : '' ?>" 
                data-spot="<?= $slot['slot_no'] ?>" 
                <?= ($slot['status'] === 'booked') ? 'disabled' : '' ?>
                title="<?= ($slot['status']==='booked') ? "Reserved from ".$slot['start_time']." to ".$slot['end_time'] : "" ?>"
            >
                <?= $slot['slot_no'] ?> - <?= ($slot['status'] === 'booked') ? 'Booked' : 'Free' ?>
            </button>
        <?php endforeach; ?>
    </div>

<div style="display:flex; justify-content: space-between; max-width:400px; margin:20px auto;">
    <button type="button" onclick="goBack()" 
            style="padding:10px 20px; font-size:16px; border:none; border-radius:5px; background:#2196F3; color:white; cursor:pointer;">
        ← Back
    </button>
    <button type="submit" class="book-btn">Book Slot</button>
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

// ✅ Track if user has booked a slot in this session
let alreadyBooked = false;

// Slot selection
buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        if (btn.classList.contains('booked')) return;

        buttons.forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        hiddenInput.value = btn.dataset.spot;
        selectedButton = btn;
    });
});

// Booking
// Booking
form.addEventListener('submit', (e) => {
    e.preventDefault();

    if (!hiddenInput.value) { 
        alert("Please select a slot"); 
        return; 
    }

    // ✅ Alert if already booked and redirect
    if (alreadyBooked) {
        alert("You can only book one slot at a time!");
        // Redirect to booking history after clicking OK
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

        // ✅ Mark booking done in this session
        alreadyBooked = true;

        // ✅ Confirm before redirecting to booking history
        let goHistory = confirm("Do you want to go to your booking history?");
        if(goHistory){
            window.location.href = 'booking_history.php';
        }
        // else: stay on the page
    });
});

</script>
</body>
</html>
