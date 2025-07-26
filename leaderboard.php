<?php
header("Content-Type: application/json");

// DB CONNECTION
require_once 'db_cred_mag.php';


// Last 10 attempts of the day
$date = date("Y-m-d");
$tries = [];
$res = $conn->query("SELECT name, time, status FROM maggie_game WHERE DATE(played_at) = '$date' ORDER BY id DESC LIMIT 10");
while ($row = $res->fetch_assoc()) {
  $tries[] = $row;
}

// All winners (status='WIN')
$winners = [];
$res2 = $conn->query("SELECT name, time, played_at, winner_id FROM maggie_game WHERE status = 'WIN' ORDER BY id DESC");
while ($row = $res2->fetch_assoc()) {
  $winners[] = $row;
}

echo json_encode(["tries" => $tries, "winners" => $winners]);
?>
