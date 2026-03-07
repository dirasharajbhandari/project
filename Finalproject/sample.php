<?php
ob_start();
session_start();
require 'Connection.php';

/* GET LOCATIONS FROM PARKING_SLOTS TABLE */
$locations = mysqli_query($conn, "SELECT DISTINCT location FROM parking_slots ORDER BY location ASC");

/* ===================== USER LOGIN CHECK ===================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

/* ===================== HANDLE RESERVATION FORM ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id    = $_SESSION['user_id'];
    $location   = trim($_POST['location']);
    $vehicle    = trim($_POST['vehicleType']);
    $numplate   = trim($_POST['numplate']);
    $date       = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time   = $_POST['end_time'];

    // Validate times
    if ($end_time <= $start_time) {
        $message = "End time must be later than start time.";
    } else {

        // Check if user exists in database (foreign key safety)
        $stmt_check = $conn->prepare("SELECT u_id FROM users WHERE u_id = ?");
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows === 0) {
            die("Invalid user. Please login again.");
        }

        // Insert reservation
        $sql = "INSERT INTO reserve 
                (user_id, location, vehicle_type, vehicle_num, date, start_time, end_time)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("SQL Prepare Failed: " . $conn->error);
        }

        $stmt->bind_param(
            "issssss",
            $user_id,
            $location,
            $vehicle,
            $numplate,
            $date,
            $start_time,
            $end_time
        );

        if ($stmt->execute()) {
            header("Location: index.php"); // redirect after successful reservation
            exit;
        } else {
            $message = "Insert failed: " . $stmt->error;
        }
    }
}

?>

<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Parking Reservation</title>
<link rel="stylesheet" href="view.css" />
<link rel="stylesheet" href="reserve.css" />
<style>
.message { color:red; font-weight:bold; margin:10px 0; }
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

<div class="message">
    <?= htmlspecialchars($message) ?>
</div>

<form action="" method="POST" id="booking-form">
    <div class="booking-step active">
        <h2>1. Choose Location & Time</h2>

        <label>Parking Location:</label>
<select name="location" required>
<option value="" disabled selected hidden>Select a location</option>

<?php while($loc = mysqli_fetch_assoc($locations)): ?>
<option value="<?= $loc['location'] ?>">
<?= ucfirst($loc['location']) ?>
</option>
<?php endwhile; ?>

</select><br/>

        <label>Vehicle type:</label>
        <select name="vehicleType" required>
            <option value="" disabled selected hidden>Select vehicle type</option>
            <option value="two">Two-Wheeler</option>
            <option value="four">Four-Wheeler</option>
        </select><br/>

        <label>Vehicle number plate:</label>
        <input type="text" name="numplate" required><br/>

<label>Date:</label>

<input type="date" name="date" required 
       min="<?= date('Y-m-d') ?>" 
       max="<?= date('Y-m-d') ?>"><br/>



        <label>Start Time:</label>
        <input type="time" name="start_time" required><br/>

        <label>End Time:</label>
        <input type="time" name="end_time" required><br/>

        <button type="submit" class="btn btn-primary">
            Search Available Spots
        </button>

        <p>Total Payment: Rs <span id="payment">0</span></p>
        <p id="summary"></p>
    </div>
</form>

<script>
const startTimeInput = document.querySelector('input[name="start_time"]');
const endTimeInput = document.querySelector('input[name="end_time"]');
const locationSelect = document.querySelector('select[name="location"]');
const vehicleSelect = document.querySelector('select[name="vehicleType"]');
const paymentDisplay = document.getElementById('payment');
const summaryDisplay = document.getElementById('summary');

function calculatePayment() {
    const start = startTimeInput.value;
    const end = endTimeInput.value;
    const location = locationSelect.value;
    const vehicle = vehicleSelect.value;

    if (start && end && vehicle) {
        const startDate = new Date(`1970-01-01T${start}:00`);
        const endDate = new Date(`1970-01-01T${end}:00`);
        let diffMinutes = (endDate - startDate) / (1000 * 60);

        if (diffMinutes <= 0) {
            paymentDisplay.textContent = "0";
            summaryDisplay.textContent = "";
        } else {
            // Determine rate based on vehicle type
            let ratePerHour = vehicle === "four" ? 60 : 40;
            let payment = (diffMinutes / 60) * ratePerHour;
            paymentDisplay.textContent = Math.ceil(payment);

            const hours = Math.floor(diffMinutes / 60);
            const minutes = Math.floor(diffMinutes % 60);
            summaryDisplay.textContent = `Location: ${location}, Vehicle: ${vehicle === "four" ? "Four-Wheeler" : "Two-Wheeler"}, Time: ${start} - ${end} (Interval: ${hours}h ${minutes}m)`;
        }
    }
}

startTimeInput.addEventListener('change', calculatePayment);
endTimeInput.addEventListener('change', calculatePayment);
locationSelect.addEventListener('change', calculatePayment);
vehicleSelect.addEventListener('change', calculatePayment);
</script>


</body>
</html>
