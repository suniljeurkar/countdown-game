<?php
$host = "localhost";
$db = "dbname";
$user = "dbuser";
$pass = "dbpassword";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}
?>
