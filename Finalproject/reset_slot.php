<?php
session_start();
require 'Connection.php';

/* Allow only admin */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized";
    exit;
}

if (!isset($_POST['slot_no'])) {
    echo "Invalid request";
    exit;
}

$slot_no = intval($_POST['slot_no']);

/* Reset slot */
$sql = "
    UPDATE parking_slots
    SET 
        status = 'free',
        user_id = NULL,
        booked_until = NULL
    WHERE slot_no = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $slot_no);
$stmt->execute();

if ($stmt->affected_rows === 1) {
    echo "Slot reset successfully!";
} else {
    echo "Reset failed (maybe slot already free)";
}
