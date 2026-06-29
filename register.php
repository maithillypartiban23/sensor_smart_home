<?php
session_start();
if(isset($_SESSION['user'])) { header("Location: index.php"); exit; }

$host = "localhost"; $user = "root"; $password = ""; $database = "iot_system";
$conn = new mysqli($host, $user, $password, $database);

// Get only AVAILABLE device IDs (not already assigned to a user)
$devices = [];
$res = $conn->query("SELECT device_id FROM device_settings WHERE device_id NOT IN (SELECT device_id FROM users)");
while($row = $res->fetch_assoc()) $devices[] = $row['device_id'];

$error = ""; $success = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $username  = trim($_POST['username']);
    $pass      = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $device_id = $_POST['device_id'];

    if(empty($full_name)||empty($email)||empty($username)||empty($pass)||empty($device_id)){
        $error = "All fields are required.";
    } elseif($pass !== $confirm){
        $error = "Passwords do not match.";
    } elseif(strlen($pass) < 6){
        $error = "Password must be at least 6 characters.";
    } else {
        // Check email/username taken
        $check = $conn->query("SELECT id FROM users WHERE email='$email' OR username='$username'");
        if($check->num_rows > 0){
            $error = "Email or username already exists.";
        } else {
            // Check device already taken
            $deviceCheck = $conn->query("SELECT id FROM users WHERE device_id='$device_id'");
            if($deviceCheck->num_rows > 0){
                $error = "This device is already registered to another user.";
            } else {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users (full_name,email,username,password,device_id) VALUES ('$full_name','$email','$username','$hashed','$device_id')");
                $success = "Account created! You can now log in.";
            }
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Register — Smart Home</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:"Segoe UI",Arial,sans-serif;}
body{background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column;}

.header{background:linear-gradient(135deg,#0f172a,#1e3a8a);color:white;padding:0 30px;height:64px;display:flex;align-items:center;gap:14px;}
.header-icon{width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.header-title{font-size:20px;font-weight:700;}

.page{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}

.card{background:white;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,0.09);padding:40px 40px 36px;width:100%;max-width:480px;}

.card-top{text-align:center;margin-bottom:28px;}
.card-icon{width:60px;height:60px;background:#eff6ff;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;}
.card-top h1{font-size:22px;font-weight:700;color:#0f172a;margin-bottom:4px;}
.card-top p{font-size:13px;color:#64748b;}

.field{margin-bottom:18px;}
.field label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;}
.input-wrap{display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;height:46px;background:white;transition:border-color 0.2s;}
.input-wrap:focus-within{border-color:#2563eb;}
.input-wrap input,.input-wrap select{border:none;outline:none;font-size:14px;color:#0f172a;background:transparent;width:100%;height:100%;}
.input-wrap .icon{font-size:16px;margin-right:8px;flex-shrink:0;}

.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}

.btn{width:100%;padding:14px;border:none;border-radius:12px;background:#2563eb;color:white;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;transition:background 0.2s;}
.btn:hover{background:#1d4ed8;}

.msg-error{background:#fff1f2;border:1px solid #fecdd3;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px;}
.msg-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px;}

.footer-link{text-align:center;margin-top:20px;font-size:13px;color:#64748b;}
.footer-link a{color:#2563eb;font-weight:600;text-decoration:none;}
.footer-link a:hover{text-decoration:underline;}

.divider{border:none;border-top:1px solid #f1f5f9;margin:20px 0;}
</style>
</head>
<body>

<div class="header">
  <div class="header-icon">🏠</div>
  <span class="header-title">Smart Home IoT System</span>
</div>

<div class="page">
  <div class="card">
    <div class="card-top">
      <div class="card-icon">📝</div>
      <h1>Create Account</h1>
      <p>Register to access your smart home dashboard</p>
    </div>

    <?php if($error): ?>
      <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="msg-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="field">
        <label>Full Name</label>
        <div class="input-wrap">
          <span class="icon">👤</span>
          <input type="text" name="full_name" placeholder="e.g. Ahmad Faris" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>
      </div>

      <div class="field">
        <label>Email Address</label>
        <div class="input-wrap">
          <span class="icon">✉️</span>
          <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <span class="icon">🔖</span>
          <input type="text" name="username" placeholder="Choose a username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label>Password</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="password" placeholder="Min. 6 characters" required>
          </div>
        </div>
        <div class="field">
          <label>Confirm Password</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="confirm_password" placeholder="Repeat password" required>
          </div>
        </div>
      </div>

      <div class="field">
        <label>Select Device</label>
        <?php if(empty($devices)): ?>
          <div style="background:#fff1f2;border:1px solid #fecdd3;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;">
            ⚠️ No available devices. All devices are already registered.
          </div>
        <?php else: ?>
        <div class="input-wrap">
          <span class="icon">📡</span>
          <select name="device_id" required>
            <option value="" disabled selected>-- Choose your ESP32 device --</option>
            <?php foreach($devices as $d): ?>
              <option value="<?= $d ?>" <?= (($_POST['device_id'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn" <?= empty($devices) ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' ?>>Create Account</button>
    </form>

    <hr class="divider">
    <div class="footer-link">Already have an account? <a href="login.php">Sign in</a></div>
  </div>
</div>

</body>
</html>