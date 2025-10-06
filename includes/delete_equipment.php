<?php
include __DIR__ . '/../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Force delete equipment
    $sql = "DELETE FROM equipment WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        header("Location: ../equipment_admin.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    // Re-enable foreign key checks (failsafe)
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
}
