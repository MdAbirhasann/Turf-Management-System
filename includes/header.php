<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>TS Sports Arena</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="loading-line"></div>
<header class="site-header">
    <nav class="nav container">
        <a class="brand" href="index.php">
            <img src="assets/images/logo.jpg" alt="TS Sports Arena Logo">
            <span>TS Sports Arena</span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
        <div class="nav-links" id="navLinks">
            <a href="index.php">Home</a>
            <a href="index.php#services">Services</a>
            <a href="booking.php">Slot Booking</a>
            <a href="index.php#pricing">Pricing</a>
            <a href="index.php#gallery">Gallery</a>
            <a href="index.php#contact">Contact</a>
            <a href="admin/login.php" class="btn btn-outline">Manager Panel</a>
            <a href="https://wa.me/<?= preg_replace('/\D+/', '', CONTACT_PHONE) ?>" class="btn btn-glow">Call / WhatsApp</a>
        </div>
    </nav>
</header>
<main>
