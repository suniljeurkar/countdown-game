<?php
session_start();

// 🔐 Login handling
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: admin.php");
  exit;
}

if (isset($_POST['password']) && $_POST['password'] === 'yourSafePassword') {
  $_SESSION['admin'] = true;
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
  echo '<!DOCTYPE html>
  <html><head><title>Admin Login</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #fdfbfb, #ebedee);
      display: flex; justify-content: center; align-items: center;
      height: 100vh; margin: 0;
    }
    form {
      background: white; padding: 30px 40px; border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    input[type=password], input[type=submit] {
      width: 100%; padding: 10px; margin-bottom: 15px;
      border: 1px solid #ccc; border-radius: 6px; font-size: 16px;
    }
    input[type=submit] {
      background-color: #007bff; color: white; border: none; cursor: pointer;
    }
    input[type=submit]:hover {
      background-color: #0056b3;
    }
  </style></head>
  <body>
    <form method="POST">
      <h2 style="text-align:center;">🔒 Admin Login</h2>
      <input type="password" name="password" placeholder="Enter Admin Password" required />
      <input type="submit" value="Login" />
    </form>
  </body></html>';
  exit;
}

// ✅ DB setup
require_once 'db_cred_mag.php';

// 🏆 Winners
$winners = $conn->query("SELECT * FROM maggie_game WHERE status='WIN' ORDER BY played_at DESC");

// 📊 Visitor Stats
$today = date('Y-m-d');
$totalRes = $conn->query("SELECT COUNT(DISTINCT fingerprint) AS total FROM visitors");
$totalVisitors = $totalRes->fetch_assoc()['total'] ?? 0;

$todayRes = $conn->query("SELECT COUNT(*) AS today FROM visitors WHERE visit_date = '$today'");
$todayVisitors = $todayRes->fetch_assoc()['today'] ?? 0;

// 🕒 Recent Plays
$recent = $conn->query("SELECT * FROM maggie_game ORDER BY played_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: #f7f9fc;
      padding: 30px;
    }
    .logout {
      float: right;
      margin-top: -20px;
    }
    .logout a {
      background: #dc3545;
      color: #fff;
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 6px;
    }
    .logout a:hover {
      background: #b52a35;
    }
    h2 {
      color: #333;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      margin-top: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    th, td {
      border: 1px solid #e3e6ea;
      padding: 12px;
      text-align: center;
      font-size: 15px;
    }
    th {
      background-color: #f0f4f8;
    }
    .status-yes {
      color: green;
      font-weight: bold;
    }
    .status-no {
      color: red;
      font-weight: bold;
    }
    .stats {
      margin-top: 20px;
      font-size: 18px;
    }
    .stats span {
      font-weight: bold;
      color: #007bff;
    }
  </style>
</head>
<body>

  <h2>🏆 Verified Winners</h2>
  <div class="logout"><a href="?logout=1">🚪 Logout</a></div>

  <table>
    <tr>
      <th>Winner ID</th>
      <th>Name</th>
      <th>Time</th>
      <th>Verified?</th>
      <th>Played At</th>
    </tr>
    <?php while($row = $winners->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['winner_id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
        <td class="<?= $row['is_verified'] ? 'status-yes' : 'status-no' ?>">
          <?= $row['is_verified'] ? '✅ Yes' : '❌ No' ?>
        </td>
        <td><?= htmlspecialchars($row['played_at']) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <div class="stats">
    👁️ Total Visitors: <span><?= $totalVisitors ?></span><br>
    📅 Today's Visitors: <span><?= $todayVisitors ?></span>
  </div>

  <h2>🕒 Recent Plays</h2>
  <table>
    <tr>
      <th>Name</th>
      <th>Time</th>
      <th>Status</th>
      <th>Fingerprint</th>
      <th>Played At</th>
    </tr>
    <?php while($row = $recent->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= htmlspecialchars($row['fingerprint']) ?></td>
        <td><?= htmlspecialchars($row['played_at']) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

</body>
</html>
