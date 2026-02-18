<?php
session_start();
require 'Connection.php';

/* Security check */
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized";
    exit;
}

if (!isset($_POST['slot_no'])) {
    echo "Invalid request";
    exit;
}

$slot_no = intval($_POST['slot_no']);

/* 1️⃣ Auto-free expired slots */
$conn->query("
    UPDATE parking_slots
    SET status = 'free',
        booked_until = NULL
    WHERE status = 'booked'
      AND booked_until < NOW()
");

/* 2️⃣ Check if slot is available now */
$check_sql = "
    SELECT status
    FROM parking_slots
    WHERE slot_no = ? AND status = 'free'
";

$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $slot_no);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo "Slot already booked!";
    exit;
}

/* 3️⃣ Book the slot for 1 minute */
$book_sql = "
    UPDATE parking_slots
    SET status = 'booked',
        booked_until = DATE_ADD(NOW(), INTERVAL 1 MINUTE)
    WHERE slot_no = ? AND status = 'free'
";

$book_stmt = $conn->prepare($book_sql);
if (!$book_stmt) {
    echo "Prepare failed: " . $conn->error;
    exit;
}

$book_stmt->bind_param("i", $slot_no);
$book_stmt->execute();

if ($book_stmt->affected_rows === 1) {
    echo "Slot booked successfully for 1 minute!";
} else {
    echo "Slot booking failed! Try again.";
}

$book_stmt->close();
$conn->close();
?>
