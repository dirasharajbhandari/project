<?php
require 'Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Password hashing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepared statement
    $sql = "INSERT INTO admin (admin_username, admin_pw) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful');</script>";
        } else {
            echo "<script>alert('Username already exists');</script>";
        }
    } else {
         // Get detailed error
    $error = $stmt->error;
    echo "<script>alert('Error: " . addslashes($error) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Management Login</title>

    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="home.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

</head>
<body>
    <div class="page-wrapper">
        

        <header class="header">
          <nav class="nav">
            <div class="nav-container">
              <div class="nav-brand">
                <span class="nav-logo">&#x1F17F;&#xFE0F;</span>
                <span class="nav-title">SpotOn</span>
              </div>

              <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Home</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#pricing" class="nav-link">Pricing</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <li><a href="login.php" class="nav-link">Login</a></li>
                <li><a href="register.php" class="nav-link">Register</a></li>
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
                <div class="login-card">
                    <h2>SpotOn Parking</h2>
                    <p>Secure Login to Manage Parking Slots</p>
                    <p>Admin signup</p>

                    <form action="" method="post" id="loginForm">
                        
                        <div class="input-group">
                            <input type="text" id="username" name="username" required>
                            <label for="username">Username</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" id="password" name="password" required>
                            <label for="password">Password</label>
                        </div>

                        <button type="submit">Signup</button>

                       

                    </form>
                </div>
            </div>
        </main>

        <footer class="footer">
          <div class="container">
            <div class="footer-content">
              <div class="footer-brand">
                <span class="footer-logo">&#x1F17F;&#xFE0F; </span>
                <span class="footer-title">SpotOn</span>
              </div>
              <p class="footer-text">Making parking simple, secure, and convenient for everyone.</p>
            </div>
            <div class="footer-bottom">
              <p>&copy; 2025 SpotOn. All rights reserved.</p>
            </div>
          </div>
        </footer>
    </div>

    <script src="script.js"></script>
</body>
</html>
