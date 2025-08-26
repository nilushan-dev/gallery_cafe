<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['usertype'] !== 'customer') {
    header('Location: /pages/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard - Gallery Cafe</title>
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
            <li><a href="/users/customer/view-reservations.php"><i class="fas fa-calendar-check"></i> My Reservations</a></li>
            <li><a href="/users/customer/view-pre-orders.php"><i class="fas fa-utensils"></i> My Pre-orders</a></li>
            <li><a href="/users/customer/profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="/index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></li>  <!-- Added back option -->
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>

<main class="admin-main">
