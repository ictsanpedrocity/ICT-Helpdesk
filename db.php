<?php
$host = "localhost";
$user = "root"; // adjust if needed
$pass = "";
$dbname = "ict_helpdesk";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
