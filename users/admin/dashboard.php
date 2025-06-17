<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: /pages/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Gallery Cafe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="admin-header">
    <div class="admin-user-dropdown">
        <i class="fas fa-user-circle"></i>
        <span><?= htmlspecialchars($_SESSION['user']) ?></span>
    </div>
</header>

<aside class="admin-sidebar">
    <nav>
        <ul>
            <li><a href="/users/admin/user-management.php"><i class="fas fa-user-plus"></i> User Management</a></li>
            <li><a href="/users/admin/menu-management.php"><i class="fas fa-plus-circle"></i> Menu Management</a></li>
            <li><a href="/users/admin/reservation-management.php"><i class="fas fa-calendar-check"></i> Reservation Management</a></li>
            <li><a href="/users/admin/pre-order-management.php"><i class="fas fa-utensils"></i> Pre-order Management</a></li>
            <li><a href="/users/admin/contact-message-management.php"><i class="fas fa-envelope"></i> Contact Messages</a></li>
            <li><a href="/users/admin/profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>

<main class="admin-main">
