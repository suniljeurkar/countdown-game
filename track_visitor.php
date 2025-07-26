<?php
require_once 'db_cred_mag.php';


$fingerprint = $_POST['fingerprint'] ?? '';
$visit_date = date('Y-m-d H:i:s');

// Log every visit
$stmt = $conn->prepare("INSERT INTO visitors (fingerprint, visit_date) VALUES (?, ?)");
$stmt->bind_param("ss", $fingerprint, $visit_date);
$stmt->execute();
$stmt->close();

// Get total hits (no DISTINCT)
$result = $conn->query("SELECT COUNT(*) AS total FROM visitors");
$total = $result->fetch_assoc()['total'] ?? 0;

// Get today’s hits
$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) AS today FROM visitors WHERE DATE(visit_date) = '$today'");
$todayHits = $result->fetch_assoc()['today'] ?? 0;

echo json_encode([
    "status" => "logged",
    "totalVisitors" => $total,
    "todayVisitors" => $todayHits
]);
?>
