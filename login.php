<?php
session_start();
if(isset($_SESSION['user'])) { header("Location: index.php"); exit; }

$host = "localhost"; $user = "root"; $password = ""; $database = "iot_system";
$conn = new mysqli($host, $user, $password, $database);

$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pass     = $_POST['password'];

    if(empty($username) || empty($pass)){
        $error = "Please enter your username and password.";
    } else {
        $result = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$username' LIMIT 1");
        if($result->num_rows === 1){
            $row = $result->fetch_assoc();
            if(password_verify($pass, $row['password'])){
                $_SESSION['user']      = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['device_id'] = $row['device_id'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that username or email.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login — Smart Home</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:"Segoe UI",Arial,sans-serif;}
body{background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column;}

.header{background:linear-gradient(135deg,#0f172a,#1e3a8a);color:white;padding:0 30px;height:64px;display:flex;align-items:center;gap:14px;}
.header-icon{width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.header-title{font-size:20px;font-weight:700;}

.page{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;}

.card{background:white;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,0.09);padding:40px 40px 36px;width:100%;max-width:420px;}

.card-top{text-align:center;margin-bottom:28px;}
.card-icon{width:60px;height:60px;background:#eff6ff;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;}
.card-top h1{font-size:22px;font-weight:700;color:#0f172a;margin-bottom:4px;}
.card-top p{font-size:13px;color:#64748b;}

.field{margin-bottom:18px;}
.field label{display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;}
.input-wrap{display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;height:46px;background:white;transition:border-color 0.2s;}
.input-wrap:focus-within{border-color:#2563eb;}
.input-wrap input{border:none;outline:none;font-size:14px;color:#0f172a;background:transparent;width:100%;height:100%;}
.input-wrap .icon{font-size:16px;margin-right:8px;flex-shrink:0;}

.btn{width:100%;padding:14px;border:none;border-radius:12px;background:#2563eb;color:white;font-size:15px;font-weight:700;cursor:pointer;margin-top:8px;transition:background 0.2s;}
.btn:hover{background:#1d4ed8;}

.msg-error{background:#fff1f2;border:1px solid #fecdd3;color:#dc2626;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:16px;}

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
      <div class="card-icon">🔐</div>
      <h1>Welcome Back</h1>
      <p>Sign in to your smart home dashboard</p>
    </div>

    <?php if($error): ?>
      <div class="msg-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="field">
        <label>Username or Email</label>
        <div class="input-wrap">
          <span class="icon">👤</span>
          <input type="text" name="username" placeholder="Enter username or email" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <span class="icon">🔒</span>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>
      </div>

      <button type="submit" class="btn">Sign In</button>
    </form>

    <hr class="divider">
    <div class="footer-link">Don't have an account? <a href="register.php">Register here</a></div>
  </div>
</div>

</body>
</html>