<?php
require_once 'db_cred_mag.php';


// Read incoming JSON POST
$data = json_decode(file_get_contents('php://input'), true);

// Extract fields safely
$name = $data['name'] ?? '';
$time = $data['time'] ?? '';
$status = $data['status'] ?? '';
$fingerprint = $data['fingerprint'] ?? '';
$enforce = $data['enforce'] ?? 'Y'; // 'Y' to block multiple attempts

// Validate required fields
if (!$name || !$time || !$status || !$fingerprint) {
    echo json_encode(["error" => "Missing data"]);
    exit;
}

// 🔐 Enforce max 3 plays per fingerprint per day
if ($enforce === 'Y') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) FROM maggie_game WHERE fingerprint = ? AND DATE(played_at) = ?");
    $stmt->bind_param("ss", $fingerprint, $today);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count >= 3) {
        echo json_encode(["error" => "Limit reached: 3 plays per device per day"]);
        exit;
    }
}

// 🎟️ Generate a winner ID if won
$winner_id = null;
if ($status === 'WIN') {
    $prefix = 'MAG';
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $winner_id = $prefix . $code;
}

// ✅ Insert game result
$stmt = $conn->prepare("INSERT INTO maggie_game (name, time, status, winner_id, fingerprint) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sdsss", $name, $time, $status, $winner_id, $fingerprint);
$stmt->execute();
$stmt->close();

// ✅ Success response
echo json_encode([
    "success" => true,
    "winner_id" => $winner_id
]);
?>
