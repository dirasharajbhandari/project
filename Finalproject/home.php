<?php
require('Connection.php'); 
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SpotOns - Premium Parking Solutions</title>
    <link rel="stylesheet" href="home.css">
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
                <li><a href="#features" class="nav-link">Features</a></li>
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

    <section id="home" class="hero">
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title">Find Your Perfect Parking Spot</h1>
          <p class="hero-subtitle">Secure, convenient, and affordable parking solutions in prime locations. Reserve your spot in seconds and park with confidence.</p>
          <div class="hero-buttons">
            <button class="btn btn-primary"><a href="signup.php">Reserve Now</a></button>
            <button class="btn btn-secondary">Learn More</button>
          </div>
        </div>
        <div class="hero-image">
          <div class="parking-visual">
            <div class="parking-grid">
              <div class="parking-spot available">&#x1F697;</div>
              <div class="parking-spot occupied">&#x1F699;</div>
              <div class="parking-spot available">&#x1F697;</div>
              <div class="parking-spot occupied">&#x1F690;</div>
              <div class="parking-spot available"></div>
              <div class="parking-spot occupied">&#x1F697;</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="features">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Why Choose SpotOn?</h2>
          <p class="section-subtitle">Experience the future of parking with our innovative solutions</p>
        </div>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">&#x1F4CD;</div>
            <h3 class="feature-title">Prime Locations</h3>
            <p class="feature-description">Strategic parking spots in kathmandu, shopping centers, and business districts.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#x1F512;</div>
            <h3 class="feature-title">Secure Parking</h3>
            <p class="feature-description">24/7 security monitoring and covered parking options for your vehicle's safety.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#x26A1;</div>
            <h3 class="feature-title">Instant Booking</h3>
            <p class="feature-description">Reserve your parking spot in under 30 seconds with real-time availability.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#x1F4B0;</div>
            <h3 class="feature-title">Best Prices</h3>
            <p class="feature-description">Competitive rates with flexible hourly, daily, and monthly parking options.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#x1F4F1;</div>
            <h3 class="feature-title">Easy Access</h3>
            <p class="feature-description">QR code entry and mobile app control for seamless parking experience.</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">&#x1F504;</div>
            <h3 class="feature-title">Flexible Terms</h3>
            <p class="feature-description">No long-term commitments. Cancel or modify your reservation anytime.</p>
          </div>
        </div>
      </div>
    </section>


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
  </body>
</html>