<?php

require 'Connection.php';

if ($connection === false) {
    $conn_error = "Database connection not found.";
} else {
    $sql = "SELECT * FROM bookings ORDER BY spot_id DESC";
    $result = mysqli_query($connection, $sql);

    if (!$result) {
        $db_error = "Query error: " . mysqli_error($connection);
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
    }
}

if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $del_sql = "DELETE FROM bookings WHERE spot_id = $delete_id";
    if (!mysqli_query($connection, $del_sql)) {
        $db_error = "Failed to delete record: " . mysqli_error($connection);
    } else {
        
        header("Location: history.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SpotOn - Reserved Slots</title>
    <link rel="stylesheet" href="history.css">

  </head>

  <body>
    <header class="header">
      <nav class="nav">
        <div class="nav-container">
          <div class="nav-brand">
            <span class="nav-logo">&#127199;&#65039;</span>
            <span class="nav-title">SpotOn</span>
          </div>
          <ul class="nav-menu">
            <li><a href="history.php" class="nav-link" style='  margin-left: 700px;'>History</a></li>
            <li><a href="home.php" class="nav-link">Logout</a></li>
          </ul>
            <div class="last-updated">
            <div class="label">Admin User</div>
            <div class="time">2:45:32 PM</div>
          </div>
          <button class="nav-toggle"><span></span><span></span><span></span></button>
        </div>
      </nav>
    </header>

   
     <main>
      <section class="booking-section">
        <h2 class="section-title">Reserved Parking Slots</h2>

        <?php if ($connection_error): ?>
          <div class="error"><?= htmlspecialchars($conn_error) ?></div>
        <?php elseif ($db_error): ?>
          <div class="error"><?= htmlspecialchars($db_error) ?></div>
        <?php endif; ?>

        <div class="table-container">
          <?php if (!empty($bookings)): ?>
            <table class="booking-table" style='margin-left:300px; margin-top: 50px'>
                <thead>
                <tr>
                <th>Spot ID</th>
                <th>License Plate</th>
                <th>Vehicle Type</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Date</th>
                <th>Location</th>
                <th>Delete</th>
                </tr>
                <thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['spot_id']) ?></td>
                    <td><?= htmlspecialchars($b['license_plate']) ?></td>
                    <td><?= htmlspecialchars($b['vehicle_type']) ?></td>
                    <td><?= htmlspecialchars($b['start_time']) ?></td>
                    <td><?= htmlspecialchars($b['end_time']) ?></td>
                    <td><?= htmlspecialchars($b['booking_date']) ?></td>
                    <td><?= htmlspecialchars($b['location']) ?></td>
                     <td>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                    <input type="hidden" name="delete_id" value="<?= $b['spot_id'] ?>">
                    <button type="submit" class="delete-btn" style='background-color: red'>Delete</button>
                </form>
            </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>

          <?php else: ?>
            <p class="no-data">No parking reservations found.</p>
          <?php endif; ?>
        </div>
      </section>
    </main> 

    <footer class="footer">
      <div class="footer-container">
        <div class="footer-content">
          <div class="footer-brand">
            <span class="footer-logo">&#127199;&#65039;</span>
            <span class="footer-title">SpotOn</span>
          </div>
          <p class="footer-text">Making parking simple, secure, and convenient for everyone.</p>
        </div>
        <div class="footer-bottom">
          <p>&copy; <?= date('Y') ?> SpotOn. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </body>
</html>
