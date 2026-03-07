<?php
session_start();
require 'Connection.php';

$message = "";

// FORM SUBMITTED?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Trim username and password
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $action   = $_POST['action']; // "login" OR "signup"

    // ================= SIGNUP =================
    if ($action === 'signup') {
        // First, check if username already exists
        $checkStmt = $conn->prepare("SELECT admin_id FROM admin WHERE admin_username=?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            // Username already exists
            $message = "<span style='color:red'>Username already exists ❌</span>";
        } else {
            // Username is available, insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO admin (admin_username, admin_pw) VALUES (?, ?)");
            $insertStmt->bind_param("ss", $username, $hashedPassword);

            if ($insertStmt->execute()) {
                $message = "<span style='color:green'>Signup successful ✅</span>";
            } else {
                $message = "<span style='color:red'>Signup failed ❌</span>";
            }
        }
    }

    // ================= LOGIN =================
    if ($action === 'login') {
        $loginStmt = $conn->prepare("SELECT admin_id, admin_username, admin_pw FROM admin WHERE admin_username=?");
        $loginStmt->bind_param("s", $username);
        $loginStmt->execute();
        $result = $loginStmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['admin_pw'])) {
                // Successful login, store session
                $_SESSION['user_id'] = $user['admin_id'];
                $_SESSION['username'] = $user['admin_username'];
                $_SESSION['role'] = 'admin';
                header("Location: adminviewsample.php");
                exit;
            } else {
                $message = "<span style='color:red'>Invalid password ❌</span>";
            }
        } else {
            $message = "<span style='color:red'>User not found ❌</span>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SpotOns - Premium Parking Solutions</title>
    <link rel="stylesheet" href="home.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<style>
/* Reset and body */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
/* Update body and main layout */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f8;
}

/* Center login container horizontally only, not vertically */
.main-content {
    display: flex;
    justify-content: center;
    align-items: flex-start; /* start from top */
    padding: 50px 0; /* optional spacing from top */
}

/* Remove height from login-container */
.login-container {
    margin-top: 100px;
    width: 400px;
    min-height: 500px;  /* increased height from default */
    background: rgba(255,255,255,0.95);
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    padding: 20px;
    text-align: center;
}


h2 {
    margin-bottom:15px;
}

/* Switch tabs */
.switch {
    margin-bottom: 20px;
}
.switch span {
    cursor: pointer;
    font-weight: bold;
    color: #007bff;
    margin: 0 10px;
}
.switch span.active {
    text-decoration: underline;
}

/* Forms slider */
.forms {
    margin-left:400px;
    display:flex;
    width:800px; /* 2 forms x 400px */
    transition: transform 0.4s ease;
}

.form {
    width:400px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
}

/* Inputs */
.input-group {
    
    width: 100%;
    margin-bottom: 15px;
    position: relative;
}
input[type="text"], input[type="password"] {
    width: 100%;
    padding: 10px;
    border-radius:8px;
    border:1px solid #ccc;
}
label {
    position: absolute;
    left: 12px;
    top:10px;
    font-size:12px;
    color:#555;
    pointer-events:none;
    transition:0.2s;
}
input:focus + label,
input:not(:placeholder-shown) + label {
    top: -18px;   /* moved up from -8px to -15px */
    font-size: 10px;
    color: #007bff;
}


/* Button */
button {
    padding:10px;
    width:85%;
    border:none;
    border-radius:8px;
    background:#007bff;
    color:#fff;
    cursor:pointer;
    margin-top:5px;
}
button:hover { background:#0056b3; }

/* Message */
.message { margin-bottom: 15px; font-weight:bold; }

.subtitle {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;       /* smaller font */
    color: #555;           /* subtle gray color */
    margin: 5px 0;         /* small spacing between lines */
}


</style>
</head>
  <body>
   
    <header class="header">
      <nav class="nav">
        <div class="nav-container">
          <div class="nav-brand">
            <span class="nav-logo">&#x1F17F;&#xFE0F;</span>
            <span class="nav-title">SpotOn</span>
          </div>
              <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li><a href="login.php" class="nav-link">User</a></li>
                <li><a href="adminsample.php" class="nav-link">Admin</a></li>
              </ul>
          <button class="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
          </button>
        </div>
      </nav>
    </header>
 <main class="main-content">
<div class="login-container">
    <h2>SpotOn Parking</h2>
<p class="subtitle">Secure Login to Manage Parking Slots</p>
<p class="subtitle">Admin Information</p><br>



    <?php if(!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <div class="switch">
        <span id="loginTab" class="active" onclick="showLogin()">Login</span> |
        <span id="signupTab" onclick="showSignup()">Signup</span>
    </div>

    <div class="forms" id="forms">
        <!-- LOGIN -->
        <div class="form">
            <form method="post">
                <input type="hidden" name="action" value="login">
                <div class="input-group">
                    <input type="text" name="username" required placeholder=" ">
                    <label>Username</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>

        <!-- SIGNUP -->
        <div class="form">
            <form method="post">
                <input type="hidden" name="action" value="signup">
                <div class="input-group">
                    <input type="text" name="username" required placeholder=" ">
                    <label>Username</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>
                <button type="submit">Signup</button>
            </form>
        </div> 
    </div>
</div>
 </main>

   <!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-brand">
        <span class="footer-logo">&#x1F17F;&#xFE0F;</span>
        <span class="footer-title">SpotOn</span>
      </div>
      <p class="footer-text">Making parking simple, secure, and convenient for everyone.</p>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 SpotOn. All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
function showSignup() {
    document.getElementById("forms").style.transform = "translateX(-400px)";
    document.getElementById("loginTab").classList.remove("active");
    document.getElementById("signupTab").classList.add("active");
}
function showLogin() {
    document.getElementById("forms").style.transform = "translateX(0)";
    document.getElementById("signupTab").classList.remove("active");
    document.getElementById("loginTab").classList.add("active");
}
</script>


</body>
</html>
