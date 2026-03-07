<?php
session_start();
require 'Connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must login first!";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_no'])) {
    $slot_no = $_POST['slot_no'];

    // Check if slot is free
    $slot_check = mysqli_query($conn, "SELECT status FROM parking_slots WHERE slot_no='$slot_no'");
    if ($slot_check && mysqli_num_rows($slot_check) > 0) {
        $slot = mysqli_fetch_assoc($slot_check);
        if ($slot['status'] === 'free') {

            // Update slot as booked
            $booked_until = date('Y-m-d H:i:s', strtotime('+1 hour')); // 1-hour booking
            mysqli_query($conn, "UPDATE parking_slots SET status='booked', booked_until='$booked_until' WHERE slot_no='$slot_no'");

            // Insert booking history
            mysqli_query($conn, "INSERT INTO booking_history (user_id, slot_no, booked_at, booked_until, status) 
                                 VALUES ('$user_id', '$slot_no', NOW(), '$booked_until', 'booked')");

            echo "Slot $slot_no booked successfully!";
        } else {
            echo "Slot $slot_no is already booked!";
        }
    } else {
        echo "Slot not found!";
    }
}
?>
