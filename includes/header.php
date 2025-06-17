<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Cafe</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/imagestyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/assets/js/javascript.js"></script>
</head>

<body>
<div class="site-container">
    <header class="site-header">
        <ul>
            <li><h1><a href="/index.php">GALLERY CAFE</a></h1></li>
        </ul>
        <ul class="auth-links">
            <?php if (isset($_SESSION['user']) && $_SESSION['usertype'] === 'customer'): ?>
                <li class="dropdown">
                    <div class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                    </div>
                    <div class="dropdown-menu">
                        <a href="/users/customer/dashboard.php">🏠 Dashboard</a>
                        <a href="/logout.php">🚪 Logout</a>
                    </div>
                </li>
            <?php elseif (isset($_SESSION['user']) && $_SESSION['usertype'] === 'staff'): ?>
                <li class="dropdown">
                    <div class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                    </div>
                    <div class="dropdown-menu">
                        <a href="/users/staff/dashboard.php">🏠 Dashboard</a>
                        <a href="/logout.php">🚪 Logout</a>
                    </div>
                </li>
            <?php elseif (isset($_SESSION['user']) && $_SESSION['usertype'] === 'admin'): ?>
                <li class="dropdown">
                    <div class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                    </div>
                    <div class="dropdown-menu">
                        <a href="/users/admin/user-management.php">🏠 Dashboard</a>
                        <a href="/logout.php">🚪 Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="/pages/login.php">Login</a></li>
                <li><a href="/pages/signup.php">Signup</a></li>
            <?php endif; ?>
        </ul>
    </header>

    <div>
        <nav class="nav_bar">
            <ul>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/pages/menu.php">Menu</a></li>
                <li><a href="/pages/reser.php">Reservation</a></li>
                <li><a href="/pages/pre-order.php">Pre-Order</a></li>
                <li><a href="/index.php#story">Our Story</a></li>
                <li><a href="/pages/contactus.php">Contact</a></li>
            </ul>
        </nav>
    </div>
</div>
<div class="main-site-container">





















