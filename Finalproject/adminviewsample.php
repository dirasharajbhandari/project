<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminsample.php");
    exit;
}

# ===================== DELETE USER =====================
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $stmt = $conn->prepare("DELETE FROM reserve WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM users WHERE u_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

# ===================== UPDATE USER =====================
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

# ===================== ADD NEW SLOT =====================
if (isset($_POST['add_slot'])) {

    $location = trim($_POST['location']);
    $vehicle_type = $_POST['vehicle_type'];

    $stmt = $conn->prepare("SELECT MAX(slot_no) as max_slot FROM parking_slots");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_slot = $row['max_slot'] ? $row['max_slot'] + 1 : 1;

    $stmt = $conn->prepare("INSERT INTO parking_slots (slot_no, location, vehicle_type, status) VALUES (?, ?, ?, 'free')");
    $stmt->bind_param("iss", $next_slot, $location, $vehicle_type);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

# ===================== RESET SLOT =====================
if (isset($_POST['reset_slot'])) {

    $slot_no = intval($_POST['slot_no']);

    $stmt = $conn->prepare("UPDATE parking_slots 
                            SET status='free', booked_until=NULL, fk_reserve_id=NULL 
                            WHERE slot_no=?");
    $stmt->bind_param("i", $slot_no);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

# ===================== EDIT SLOT =====================
if (isset($_POST['edit_slot'])) {

    $slot_no = intval($_POST['slot_no']);
    $location = trim($_POST['location']);
    $vehicle_type = $_POST['vehicle_type'];

    $stmt = $conn->prepare("UPDATE parking_slots 
                            SET location=?, vehicle_type=? 
                            WHERE slot_no=?");
    $stmt->bind_param("ssi", $location, $vehicle_type, $slot_no);
    $stmt->execute();

    header("Location: adminviewsample.php");
    exit;
}

# ===================== FETCH USERS =====================
$users = $conn->query("SELECT * FROM users ORDER BY u_id ASC");

# ===================== FETCH PARKING SLOTS =====================
$slots = $conn->query("SELECT * FROM parking_slots ORDER BY slot_no ASC");
?>

<!DOCTYPE html>
<html>
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Management Login</title>

    
    <link rel="stylesheet" href="home.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />


<title>Admin Dashboard</title>
<style>
table { border-collapse: collapse; width:100%; margin-top:30px;}
th,td { border:1px solid #ccc; padding:8px;}
th { background:#007bff; color:white;}
button { padding:5px 10px; background:#007bff; color:white; border:none;}
button:hover { background:#0056b3;}
.free { color:green; font-weight:bold;}
.booked { color:red; font-weight:bold;}
</style>
</head>
<body>
    <header class="header">
      <nav class="nav">
        <div class="nav-container">
          <div class="nav-brand">
            <span class="nav-logo">&#x1F17F;&#xFE0F;</span>
            <span class="nav-title">SpotOn</span>
          </div>
              <ul class="nav-menu">
                <li><a href="adminviewsample.php" class="nav-link">Dashboard</a></li>
                <li><a href="home.php" class="nav-link">Logout</a></li>
              
              </ul>
          <button class="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
      </nav>
    </header>
<h1>Admin Dashboard</h1>

<!-- ================= USER MANAGEMENT (UNCHANGED) ================= -->
<h2 style="margin-top: 30px;">User Management</h2>
<table>
<tr>
<th>ID</th>
<th>Username</th>
<th>Created</th>
<th>Updated</th>
<th>Action</th>
</tr>
<?php while($u = $users->fetch_assoc()): ?>
<tr>
<td><?= $u['u_id'] ?></td>
<td><?= htmlspecialchars($u['u_username']) ?></td>
<td><?= $u['u_created'] ?></td>
<td><?= $u['u_updated'] ?></td>
<td>

<!-- EDIT USER -->
<form method="post" style="display:inline;">
<input type="hidden" name="update_id" value="<?= $u['u_id'] ?>">

<input type="text" name="username" 
value="<?= htmlspecialchars($u['u_username']) ?>" required>

<input type="password" name="password" placeholder="New Password">

<button type="submit">Edit</button>
</form>

<!-- DELETE USER -->
<a href="?delete_id=<?= $u['u_id'] ?>" onclick="return confirm('Delete user?')" style="color:red;">
Delete
</a>

</td>
</tr>
<?php endwhile; ?>
</table>

<!-- ================= PARKING SLOT MANAGEMENT ================= -->
<h2>Parking Slot Management</h2>

<h3>Add New Slot</h3>
<form method="post">
Location:
<input type="text" name="location" required>

Vehicle Type:
<select name="vehicle_type" required>
<option value="four">Four Wheeler</option>
<option value="two">Two Wheeler</option>
</select>

<button type="submit" name="add_slot">Add Slot</button>
</form>

<table>
<tr>
<th>Slot No</th>
<th>Location</th>
<th>Vehicle Type</th>
<th>Status</th>
<th>Booked Until</th>
<th>Action</th>
</tr>

<?php while($s = $slots->fetch_assoc()): ?>
<tr>
<td><?= $s['slot_no'] ?></td>
<td><?= htmlspecialchars($s['location']) ?></td>
<td><?= ucfirst($s['vehicle_type']) ?></td>
<td class="<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></td>
<td><?= $s['booked_until'] ? $s['booked_until'] : '-' ?></td>
<td>

<!-- RESET BUTTON -->
<form method="post" style="display:inline;">
<input type="hidden" name="slot_no" value="<?= $s['slot_no'] ?>">
<button name="reset_slot">Reset</button>
</form>

<!-- EDIT BUTTON -->
<form method="post" style="display:inline;">
<input type="hidden" name="slot_no" value="<?= $s['slot_no'] ?>">
<input type="text" name="location" value="<?= htmlspecialchars($s['location']) ?>" required>
<select name="vehicle_type">
<option value="four" <?= $s['vehicle_type']=='four'?'selected':'' ?>>Four</option>
<option value="two" <?= $s['vehicle_type']=='two'?'selected':'' ?>>Two</option>
</select>
<button name="edit_slot">Edit</button>
</form>

</td>
</tr>
<?php endwhile; ?>
</table>
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