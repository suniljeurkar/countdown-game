<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#4CAF50">
  <title>QR Code Verification</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f4;
      color: #333;
      padding: 0;
      text-align: center;
    }
    h1 {
      background: #222;
      color: #fff;
      margin: 0;
      padding: 15px;
      font-size: 1.4em;
    }
    #reader {
      width: 90vw;
      max-width: 400px;
      margin: 20px auto;
    }
    #resultDisplay {
      margin-top: 20px;
      padding: 15px;
      background: #fff;
      max-width: 90vw;
      margin-inline: auto;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .btn-container {
      margin-top: 20px;
    }
    button {
      padding: 10px 18px;
      margin: 8px;
      font-size: 1em;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .scan-btn { background-color: #4CAF50; color: white; }
    .capture-btn { background-color: #673AB7; color: white; }
    .verify-btn { background-color: #009688; color: white; }
    .redeem-btn { background-color: #ff9800; color: white; }
    .refresh-btn { background-color: #2196F3; color: white; }
  </style>
</head>
<body>
<div id="loginScreen" style="padding: 20px;">
  <h2 style="margin-top: 20px;">🔐 Staff Login</h2>
  <input type="password" id="adminPass" placeholder="Enter passcode" style="padding: 12px; width: 80%; font-size: 1.1em; border-radius: 6px; border: 1px solid #ccc;" />
  <br><br>
  <button onclick="checkLogin()" style="padding: 10px 20px; font-size: 1em; border-radius: 6px; background: #007bff; color: white; border: none;">Login</button>
</div>
<div id="scannerSection" style="display:none;">
<h1>🎟️ Verify Maggi Coupon</h1>

<div class="btn-container">
  <button class="scan-btn" id="scanBtn">Start Scanning</button>
  <button class="capture-btn" id="captureBtn" disabled>Capture</button>
  <button class="verify-btn" id="verifyBtn" disabled>Verify</button>
  <button class="redeem-btn" id="redeemBtn" disabled>Redeem</button>
  <button class="refresh-btn" onclick="location.reload()">Refresh</button>
</div>

<div id="reader"></div>
<pre id="resultDisplay">Waiting for scan...</pre>
</div>
<script>
  
  let html5QrCode;
  let lastScannedText = "";
  let scannedData = null;

  const resultEl = document.getElementById('resultDisplay');
  const scanBtn = document.getElementById('scanBtn');
  const captureBtn = document.getElementById('captureBtn');
  const verifyBtn = document.getElementById('verifyBtn');
  const redeemBtn = document.getElementById('redeemBtn');
  
  scanBtn.addEventListener('click', () => {
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
      { facingMode: "environment" },
      { fps: 10, qrbox: 250 },
      (decodedText, result) => {
        // Store last scanned result without verifying
        lastScannedText = decodedText;
        resultEl.innerText = "QR captured, now click Verify!";
        captureBtn.disabled = false;
        verifyBtn.disabled = false;
        html5QrCode.pause(); // Pause camera
      },
      error => {
        // Do nothing on scan failure
      }
    );
    scanBtn.disabled = true;
  });

  captureBtn.addEventListener('click', () => {
    if (html5QrCode) {
      html5QrCode.resume(); // Resume scanning
      resultEl.innerText = "Scanning resumed. Awaiting new QR...";
      captureBtn.disabled = true;
      verifyBtn.disabled = true;
    }
  });

  verifyBtn.addEventListener('click', () => {
    if (!lastScannedText) return;
    let parsed;
    try {
      parsed = JSON.parse(lastScannedText);
      scannedData = parsed;
    } catch (e) {
      resultEl.innerText = "❌ Invalid QR format";
      return;
    }

    resultEl.innerText = "Validating...";

    fetch("validate_coupon.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(parsed)
    })
    .then(res => res.json())
    .then(result => {
      if (result.valid) {
        resultEl.innerHTML = `✅ Verified<br><strong>Name:</strong> ${result.name}<br><strong>Time:</strong> ${result.time}`;
        redeemBtn.disabled = false;
      } else {
        resultEl.innerHTML = `❌ Invalid or Already Used`;
        redeemBtn.disabled = true;
      }
    })
    .catch(err => {
      resultEl.innerText = "⚠️ Error validating QR";
      console.error(err);
    });
  });

  redeemBtn.addEventListener('click', () => {
    if (!scannedData) return;
    if (confirm("Mark this coupon as REDEEMED?")) {
      fetch("validate_coupon.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...scannedData, redeem: true })
      })
      .then(res => res.json())
      .then(result => {
        if (result.valid && result.redeemed) {
          alert("✅ Coupon marked as redeemed.");
          location.reload();
        } else {
          alert("❌ Could not redeem (may be invalid or already used).");
        }
      })
      .catch(err => {
        alert("⚠️ Error during redemption.");
        console.error(err);
      });
    }
  });

  function checkLogin() {
  const pass = document.getElementById('adminPass').value.trim();
  if (pass === "yourSafePassword") {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('scannerSection').style.display = 'block';
  } else {
    alert("❌ Incorrect passcode!");
  }
}

</script>

</body>
</html>
