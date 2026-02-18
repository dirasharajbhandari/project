<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Cancel Booking & Free Slot
// Handle Cancel Booking & Free Slot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If Cancel button clicked
    if (isset($_POST['reserve_id'])) {
        $reserve_id = intval($_POST['reserve_id']);

        // Get slot_no for this reservation
        $slot_res = mysqli_query($conn, "SELECT slot_no FROM booking_history WHERE reserve_id='$reserve_id' AND user_id='$user_id'");
        $slot_row = mysqli_fetch_assoc($slot_res);
        $slot_no = $slot_row['slot_no'] ?? null;

        // Delete from reserve table
        mysqli_query($conn, "DELETE FROM reserve WHERE reserve_id='$reserve_id' AND user_id='$user_id'");

        if ($slot_no) {
            // Free the slot
            mysqli_query($conn, "UPDATE parking_slots SET status='free' WHERE slot_no='$slot_no'");
            // Delete from booking_history
            mysqli_query($conn, "DELETE FROM booking_history WHERE reserve_id='$reserve_id' AND user_id='$user_id'");
            $message = "Booking canceled and Slot #$slot_no is now free!";
        } else {
            $message = "Booking canceled!";
        }
    }

    // If Free button clicked
    if (isset($_POST['slot_no'])) {
        $slot_no = $_POST['slot_no'];

        // Free the slot
        mysqli_query($conn, "UPDATE parking_slots SET status='free' WHERE slot_no='$slot_no'");
        // Delete from booking_history
        mysqli_query($conn, "DELETE FROM booking_history WHERE slot_no='$slot_no' AND user_id='$user_id'");
        $message = "Slot #$slot_no is now free!";
    }

}


// Fetch username
$user_result = mysqli_query($conn, "SELECT u_username FROM users WHERE u_id='$user_id'");
$user = mysqli_fetch_assoc($user_result);

// Fetch booking history
$history_result = mysqli_query($conn, "
    SELECT r.reserve_id, r.location, r.vehicle_type, r.vehicle_num, r.date, r.start_time, r.end_time,
           bh.slot_no
    FROM reserve r
    LEFT JOIN booking_history bh ON r.reserve_id = bh.reserve_id
    WHERE r.user_id = '$user_id'
    ORDER BY r.date DESC, r.start_time DESC
");

// Fetch booked slots
$slots_result = mysqli_query($conn, "
    SELECT bh.slot_no, ps.status
    FROM booking_history bh
    JOIN parking_slots ps ON bh.slot_no = ps.slot_no
    WHERE bh.user_id='$user_id'
    ORDER BY bh.slot_no ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Parking Dashboard</title>
<link rel="stylesheet" href="view.css" />
<style>
body { font-family: Arial; background:#f7f7f7; margin:0; padding:0; }
h2,h3 { text-align:center; margin:20px 0; }
table { width:90%; max-width:900px; margin:20px auto 50px; border-collapse:collapse; background:#fff; }
th,td { padding:12px 15px; border:1px solid #ddd; text-align:center; }
th { background:#4CAF50; color:white; }
tr:nth-child(even){ background:#f2f2f2; }
.cancel-btn, .free-btn { padding:5px 10px; background:#ff5722; color:#fff; border:none; border-radius:5px; cursor:pointer; }
.cancel-btn:hover, .free-btn:hover { background:#ff784e; }
.message { text-align:center; color:green; font-weight:bold; margin-bottom:20px; }
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
          <li><a href="" class="nav-link">My Bookings</a></li>
          <li><a href="home.php" class="nav-link">Logout</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<h2>My Parking Booking History</h2>
<h3>Username: <?= htmlspecialchars($user['u_username']) ?></h3>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<!-- Booking History Table -->
<?php if(mysqli_num_rows($history_result) > 0): ?>
<table>
<tr>
<th>Slot No</th>
<th>Location</th>
<th>Vehicle Type</th>
<th>Vehicle Number</th>
<th>Date</th>
<th>Start Time</th>
<th>End Time</th>
<th>Payment (Rs.)</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($history_result)): ?>
<tr>
<td><?= htmlspecialchars($row['slot_no'] ?? '-') ?></td>
<td><?= htmlspecialchars($row['location']) ?></td>
<td><?= htmlspecialchars($row['vehicle_type']) ?></td>
<td><?= htmlspecialchars($row['vehicle_num']) ?></td>
<td><?= htmlspecialchars($row['date']) ?></td>
<td><?= htmlspecialchars($row['start_time']) ?></td>
<td><?= htmlspecialchars($row['end_time']) ?></td>
<td>
<?php
$rate_per_hour = ($row['vehicle_type'] == 'two') ? 40 : (($row['vehicle_type'] == 'four') ? 60 : 0);
if($row['start_time'] && $row['end_time']) {
    $start = new DateTime($row['start_time']);
    $end = new DateTime($row['end_time']);
    $diff_seconds = $end->getTimestamp() - $start->getTimestamp();
    $total_hours = ceil($diff_seconds / 3600);
    echo $total_hours * $rate_per_hour;
} else {
    echo '-';
}
?>
</td>
<td>
<form method="POST">
    <input type="hidden" name="reserve_id" value="<?= $row['reserve_id'] ?>">
    <button type="submit" class="cancel-btn">Cancel</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">You have no booking history yet.</p>
<?php endif; ?>

<!-- Booked Slots Table -->
<h2 style="text-align:center;">My Booked Slots</h2>
<?php if(mysqli_num_rows($slots_result) > 0): ?>
<table>
<tr>
<th>Slot No</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($slot = mysqli_fetch_assoc($slots_result)): ?>
<tr>
<td><?= htmlspecialchars($slot['slot_no']) ?></td>
<td><?= htmlspecialchars($slot['status']) ?></td>
<td>
<form method="POST">
<input type="hidden" name="slot_no" value="<?= $slot['slot_no'] ?>">
<button type="submit" class="free-btn">Free</button>
</form>
</td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No booked slots found.</p>
<?php endif; ?>

</body>
</html>
