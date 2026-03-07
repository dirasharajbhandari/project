<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$rate_per_hour = 40;
$message = "";

// Handle delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Bulk delete
    if(isset($_POST['booking_ids'])) {
        $booking_ids = $_POST['booking_ids'];
        if(!empty($booking_ids)) {
            $ids_str = implode(',', array_map('intval', $booking_ids));

            $slots_res = mysqli_query($conn, "SELECT slot_no FROM booking_history WHERE id IN ($ids_str) AND user_id=$user_id");
            $slot_nos = [];
            while($row = mysqli_fetch_assoc($slots_res)) {
                $slot_nos[] = intval($row['slot_no']);
            }

            mysqli_query($conn, "DELETE FROM booking_history WHERE id IN ($ids_str) AND user_id=$user_id");

            if(!empty($slot_nos)) {
                $slots_str = implode(',', $slot_nos);
                mysqli_query($conn, "UPDATE parking_slots SET status='free' WHERE slot_no IN ($slots_str)");
            }

            $message = "Selected bookings deleted successfully!";
        } else {
            $message = "No bookings selected!";
        }
    }

    // Single delete
    elseif(isset($_POST['booking_id'])) {
        $id = intval($_POST['booking_id']);
        $res = mysqli_query($conn, "SELECT slot_no FROM booking_history WHERE id=$id AND user_id=$user_id");
        if($row = mysqli_fetch_assoc($res)) {
            $slot_no = intval($row['slot_no']);
            mysqli_query($conn, "DELETE FROM booking_history WHERE id=$id AND user_id=$user_id");
            mysqli_query($conn, "UPDATE parking_slots SET status='free' WHERE slot_no=$slot_no");
            $message = "Booking deleted successfully!";
        }
    }
}

// Fetch username
$user_result = mysqli_query($conn, "SELECT u_username FROM users WHERE u_id=$user_id");
$user = mysqli_fetch_assoc($user_result);

// Fetch booking history
$history_result = mysqli_query($conn, "SELECT id, slot_no, booked_at, booked_until, status FROM booking_history WHERE user_id=$user_id ORDER BY booked_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Parking Booking History</title>
<link rel="stylesheet" href="view.css">
<style>
body { font-family: Arial; background:#f7f7f7; margin:0; padding:0; }
h2,h3 { text-align:center; margin:20px 0; }
table { width:90%; max-width:900px; margin:20px auto 50px; border-collapse:collapse; background:#fff; }
th,td { padding:12px 15px; border:1px solid #ddd; text-align:center; }
th { background:#4CAF50; color:white; }
tr:nth-child(even){ background:#f2f2f2; }
.cancel-btn, .bulk-delete-btn { padding:5px 10px; background:#ff5722; color:#fff; border:none; border-radius:5px; cursor:pointer; }
.cancel-btn:hover, .bulk-delete-btn:hover { background:#ff784e; }
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
          <li><a href="booking_history.php" class="nav-link">My Bookings</a></li>
          <li><a href="home.php" class="nav-link">Logout</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<h2>My Parking Booking History</h2>
<h3>Username: <?= htmlspecialchars($user['u_username'] ?? '') ?></h3>

<?php if($message) echo "<div class='message'>$message</div>"; ?>

<?php if(mysqli_num_rows($history_result) > 0): ?>
<form method="POST" onsubmit="return confirmBulkDelete();">
<table>
<tr>
<th><input type="checkbox" id="select_all"></th>
<th>Slot No</th>
<th>Booked At</th>
<th>Booked Until</th>
<th>Status</th>
<th>Payment (Rs.)</th>
</tr>

<?php while($row = mysqli_fetch_assoc($history_result)): ?>
<tr>
<td><input type="checkbox" name="booking_ids[]" value="<?= $row['id'] ?>" class="select_box"></td>
<td><?= htmlspecialchars($row['slot_no']) ?></td>
<td><?= htmlspecialchars($row['booked_at']) ?></td>
<td><?= htmlspecialchars($row['booked_until']) ?></td>
<td><?= htmlspecialchars($row['status']) ?></td>
<td>
<?php
if($row['booked_at'] && $row['booked_until']) {
    $start = new DateTime($row['booked_at']);
    $end = new DateTime($row['booked_until']);
    $diff_seconds = abs($end->getTimestamp() - $start->getTimestamp());
    $hours = ceil($diff_seconds / 3600);
    echo $hours * $rate_per_hour;
} else {
    echo "-";
}
?>
</td>

</tr>
<?php endwhile; ?>
</table>

<div style="text-align:center; margin:20px;">
<button type="submit" class="bulk-delete-btn">Delete Selected</button>
</div>
</form>

<script>
const selectAll = document.getElementById('select_all');
const checkboxes = document.querySelectorAll('.select_box');

selectAll.addEventListener('change', function() {
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
});

function confirmBulkDelete() {
    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
    if(!anyChecked){
        alert("Please select at least one booking to delete.");
        return false;
    }
    return confirm("Are you sure you want to delete the selected bookings?");
}
</script>

<?php else: ?>
<p style="text-align:center;">No booking history found.</p>
<?php endif; ?>

</body>
</html>