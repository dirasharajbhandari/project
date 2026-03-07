<?php
session_start();
require 'Connection.php';

$error = ""; // To store login errors

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepared SELECT query
    $sql = "SELECT admin_id, admin_username, admin_pw FROM admin WHERE admin_username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // User found?
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify hashed password
            if (password_verify($password, $user['admin_pw'])) {
                
                // Create session
                $_SESSION['user_id'] = $user['admin_id'];
                $_SESSION['username'] = $user['admin_username'];
                $_SESSION['role'] = 'admin';

                header("Location: index.php");
                exit;
                
            } else {
                $error = "Invalid password ❌";
            }

        } else {
            $error = "Invalid username ❌";
        }

    } else {
        $error = "Database error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>

  <link rel="stylesheet" href="login.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  <div class="page-wrapper">

    <main class="main-content">
      <div class="login-container">
        <div class="login-card">
          <h2>SpotOn Parking</h2>
          <p>Admin Login</p>

          <!-- Show error -->
          <?php if (!empty($error)) { ?>
              <div style="color:red; margin-bottom:10px; font-weight:bold;">
                <?= $error ?>
              </div>
          <?php } ?>

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

          </form>
        </div>
      </div>
    </main>

  </div>
</body>
</html>
