<?php
include 'db.php';
$result = $conn->query("SELECT concern FROM concern_category");
$concern = [];

while ($row = $result->fetch_assoc()) {
    $concern[] = $row;
}
echo json_encode($concern);
?>