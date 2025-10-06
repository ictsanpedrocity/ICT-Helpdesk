<?php
include __DIR__ . '/../db.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM tickets WHERE id=$id");
header("Location: /index.php");
exit;
?>