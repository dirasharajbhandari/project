<?php
session_start();
require 'Connection.php';

// Ensure admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminsample.php");
    exit;
}

// ===================== DELETE USER =====================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Delete child records first
    $stmt = $conn->prepare("DELETE FROM reserve WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    // Now delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE u_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

// ===================== UPDATE USER =====================
if (isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET u_username=?, u_pwd=?, u_updated=NOW() WHERE u_id=?");
        $stmt->bind_param("ssi", $new_username, $hashed_password, $update_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET u_username=?, u_updated=NOW() WHERE u_id=?");
        $stmt->bind_param("si", $new_username, $update_id);
    }
    $stmt->execute();
    header("Location: adminviewsample.php");
    exit;
}

// ===================== ADD NEW PARKING SLOT =====================
if (isset($_POST['add_slot'])) {
    $result = $conn->query("SELECT MAX(slot_no) AS max_slot FROM parking_slots");
    $row = $result->fetch_assoc();
    $next_slot_no = $row['max_slot'] + 1;

    $stmt = $conn->prepare("INSERT INTO parking_slots (slot_no, status, booked_until, fk_reserve_id) VALUES (?, 'free', NULL, NULL)");
    $stmt->bind_param("i", $next_slot_no);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

// ===================== RESET PARKING SLOT =====================
if (isset($_POST['reset_slot'])) {
    $slot_no = intval($_POST['slot_no']);

    $stmt = $conn->prepare(
        "UPDATE parking_slots
         SET status='free', booked_until=NULL, fk_reserve_id=NULL
         WHERE slot_no=?"
    );
    $stmt->bind_param("i", $slot_no);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

// ===================== FETCH USERS =====================
$users = $conn->query("SELECT * FROM users ORDER BY u_id ASC");

// ===================== FETCH PARKING SLOTS WITH LATEST BOOKING =====================
$slots = $conn->query("
    SELECT ps.slot_no, ps.status, ps.booked_until, bh.user_id, u.u_username
    FROM parking_slots ps
    LEFT JOIN (
        SELECT r1.*
        FROM booking_history r1
        INNER JOIN (
            SELECT slot_no, MAX(booked_at) AS latest_booking
            FROM booking_history
            WHERE status='booked'
            GROUP BY slot_no
        ) r2 ON r1.slot_no = r2.slot_no AND r1.booked_at = r2.latest_booking
        WHERE r1.status='booked'
    ) bh ON ps.slot_no = bh.slot_no
    LEFT JOIN users u ON bh.user_id = u.u_id
    ORDER BY ps.slot_no ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Parking Management Dashboard</title>
<link rel="stylesheet" href="view.css" />
<link rel="stylesheet" href="reserve.css" />
<style>
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background-color: #007bff; color: white; }
a.button, button { padding: 5px 10px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-right: 5px; cursor:pointer; }
a.button:hover, button:hover { background: #0056b3; }
form.inline { display: inline; }
h2 { margin-top:30px; }
.free { color:green; font-weight:bold; }
.available { color:blue; font-weight:bold; }
.booked { color:red; font-weight:bold; }
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
        <li><a href="adminviewsample.php" class="nav-link">Dashboard</a></li>
        <li><a href="home.php" class="nav-link">Logout</a></li>
      </ul>
    </nav>
    <div class="status-section">
      <div class="last-updated">
        <div class="label">Admin User</div>
        <div class="time">2:45:32 PM</div>
      </div>
      <div class="live-indicator"></div>
    </div>
  </div>
</div>
</header>

<h1>Admin Dashboard</h1>

<!-- ================= USER MANAGEMENT ================= -->
<h2>User Management</h2>
<?php if ($users && $users->num_rows > 0): ?>
<table>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Password</th>
    <th>Created</th>
    <th>Updated</th>
    <th>Actions</th>
</tr>
<?php while ($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= $u['u_id'] ?></td>
    <td><?= htmlspecialchars($u['u_username']) ?></td>
    <td>********</td>
    <td><?= $u['u_created'] ?></td>
    <td><?= $u['u_updated'] ?></td>
    <td>
        <a class="button" href="?edit_id=<?= $u['u_id'] ?>">Edit</a>
        <a class="button" href="?delete_id=<?= $u['u_id'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
    </td>
</tr>
<?php if (isset($_GET['edit_id']) && $_GET['edit_id'] == $u['u_id']): ?>
<tr>
<form method="post">
    <td><?= $u['u_id'] ?></td>
    <td><input type="text" name="username" value="<?= htmlspecialchars($u['u_username']) ?>" required></td>
    <td><input type="password" name="password" placeholder="New password"></td>
    <td><?= $u['u_created'] ?></td>
    <td><?= $u['u_updated'] ?></td>
    <td>
        <input type="hidden" name="update_id" value="<?= $u['u_id'] ?>">
        <button type="submit">Update</button>
        <a class="button" href="adminviewsample.php">Cancel</a>
    </td>
</form>
</tr>
<?php endif; ?>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No users found.</p>
<?php endif; ?>

<!-- ================= PARKING SLOT MANAGEMENT ================= -->
<h2>Parking Slot Management</h2>
<form method="post">
    <button type="submit" name="add_slot" onclick="return confirm('Add a new parking slot?')">Add New Slot</button>
</form>
<?php if ($slots && $slots->num_rows > 0): ?>
<table>
<tr>
    <th>Slot No</th>
    <th>Status</th>
    <th>Booked Until</th>
    <th>Booked By (User)</th>
    <th>Action</th>
</tr>
<?php while ($s = $slots->fetch_assoc()): ?>
<tr>
    <td><?= $s['slot_no'] ?></td>
    <td class="<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></td>
    <td><?= $s['status'] === 'booked' ? $s['booked_until'] : '-' ?></td>
    <td>
        <?= ($s['status'] === 'booked' && $s['user_id']) 
            ? $s['user_id'] . " (" . htmlspecialchars($s['u_username']) . ")" 
            : '-' ?>
    </td>
    <td>
        <form method="post" class="inline">
            <input type="hidden" name="slot_no" value="<?= $s['slot_no'] ?>">
            <button type="submit" name="reset_slot" onclick="return confirm('Reset this slot?')">Reset</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<?php else: ?>
<p>No parking slots found.</p>
<?php endif; ?>

<footer class="footer">
  <div class="container">
    <p>&copy; 2025 SpotOn. All rights reserved.</p>
  </div>
</footer>
</body>
</html>
