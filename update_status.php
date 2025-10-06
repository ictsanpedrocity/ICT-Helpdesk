<?php
session_start();
include 'db.php';

// Allow only Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = intval($_POST['borrow_id']);
    $new_status = $conn->real_escape_string($_POST['status']);

    // Update status
    $sql = "UPDATE equipment_borrow SET status='$new_status' WHERE id=$borrow_id";
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Status updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating status: " . $conn->error;
    }
}

header("Location: index.php"); // adjust to your actual page
exit;
?>
