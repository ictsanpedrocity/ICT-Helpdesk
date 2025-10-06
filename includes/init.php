<?php
include __DIR__ . '/../db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'includes/InsertUpdateFetch.php';

// Define role shortcut
$isAdmin = $_SESSION['role'] === 'admin';
$isStaff = $_SESSION['role'] === 'staff';

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$equipmentList = $conn->query("SELECT * FROM equipment ORDER BY name ASC");
if (!$equipmentList) {
    die("Query failed: " . $conn->error);
}


// Fetch staff users
$staffList = $conn->query("SELECT username FROM users WHERE role = 'staff'");
$staffUsers = [];
while ($s = $staffList->fetch_assoc()) {
    $staffUsers[] = $s['username'];
}

// Fetch personnel
$personnelList = $conn->query("SELECT fullname FROM personnel");
$personnel = [];
while ($s = $personnelList->fetch_assoc()) {
    $personnel[] = $s['fullname'];
}

// Handle Approve/Reject actions securely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    if (isset($_POST['approve_id'])) {
        $id = intval($_POST['approve_id']);
        $conn->query("UPDATE borrow_requests SET status = 'Approved' WHERE id = $id");
    }

    if (isset($_POST['reject_id'])) {
        $id = intval($_POST['reject_id']);
        $conn->query("UPDATE borrow_requests SET status = 'Rejected' WHERE id = $id");
    }
}
?>