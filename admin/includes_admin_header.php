<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pendingCount = pendingBookingCount();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Manager Panel</title><link rel="stylesheet" href="../assets/css/admin.css"></head>
<body>
<aside class="sidebar">
    <a class="admin-brand" href="dashboard.php"><img src="../assets/images/logo.jpg" alt="Logo"><span>TS Manager</span></a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="bookings.php">Bookings <?php if($pendingCount): ?><b><?= $pendingCount ?></b><?php endif; ?></a>
        <a href="manual_booking.php">Manual Booking</a>
        <a href="blocked_slots.php">Blocked Slots</a>
        <a href="pricing.php">Pricing</a>
        <a href="gallery.php">Gallery</a>
        <a href="reviews.php">Reviews</a>
        <a href="export_pdf.php">Print/PDF Export</a>
        <a href="../index.php" target="_blank">View Website</a>
        <a href="logout.php">Logout</a>
    </nav>
</aside>
<main class="admin-main">
<header class="admin-top"><div><h1><?= e($pageTitle ?? 'Dashboard') ?></h1><p>Welcome, <?= e(currentUser()['name']) ?></p></div><a class="btn" href="bookings.php?status=Pending">Pending: <?= $pendingCount ?></a></header>
<?php if(!empty($_GET['success'])): ?><div class="notice success"><?= e($_GET['success']) ?></div><?php endif; ?>
<?php if(!empty($_GET['error'])): ?><div class="notice error"><?= e($_GET['error']) ?></div><?php endif; ?>
