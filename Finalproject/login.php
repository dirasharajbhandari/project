<?php
session_start();
require 'Connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SELECT query
    $sql = "SELECT u_id, u_username, u_pwd FROM users WHERE u_username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['u_pwd'])) {

                // Login success → create session
                $_SESSION['user_id'] = $user['u_id'];
                $_SESSION['username'] = $user['u_username'];
                $_SESSION['role'] = 'user';

                header("Location: view.php");
                exit;

            } else {
                $error = "Invalid password";
            }

        } else {
            $error = "Invalid username";
        }

    } else {
        $error = "Database error";
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
                <li><a href="signup.php" class="nav-link">Register</a></li>
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
                <div class="login-card">
                    <h2>SpotOn Parking</h2>
                    <p>Secure Login to Manage Parking Slots</p>

                    <form action="" method="post" id="loginForm">
                        
                        <div class="input-group">
                            <input type="text" id="username" name="username" required>
                            <label for="username">Username</label>
                        </div>
                        
                        <div class="input-group">
                            <input type="password" id="password" name="password" required>
                            <label for="password">Password</label>
                        </div>

                        <button type="submit">Login</button>

                      <p>Do you have an account? <a href="signup.php">Signup</a></p>

                   

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

</body>
</html>
