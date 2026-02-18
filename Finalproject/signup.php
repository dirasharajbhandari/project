<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require 'Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO users (u_username, u_pwd) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);

        $stmt->execute();

        // success alert + redirect
        echo "<script>
                alert('Signup successful — please login');
                window.location.href='login.php';
              </script>";

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            echo "<script>
                    alert('User already exists — please login');
                    window.location.href='login.php';
                  </script>";
        } else {
            echo "<script>alert('Database error');</script>";
        }
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
                    <p>Secure SignUP to Manage Parking Slots</p>

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
