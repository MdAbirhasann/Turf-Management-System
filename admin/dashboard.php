<?php
$pageTitle = 'Dashboard';
require_once 'includes_admin_header.php';
$stats = [];
$stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$stats['today'] = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date = CURDATE()")->fetchColumn();
$stats['pending'] = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
$stats['confirmed'] = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Confirmed'")->fetchColumn();
$stats['cancelled'] = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Cancelled'")->fetchColumn();
$stats['completed'] = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Completed'")->fetchColumn();
$stats['revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status IN ('Confirmed','Completed')")->fetchColumn();
$revenueByService = $pdo->query("SELECT s.name, COALESCE(SUM(b.total_amount),0) total FROM services s LEFT JOIN bookings b ON b.service_id=s.id AND b.status IN ('Confirmed','Completed') GROUP BY s.id ORDER BY s.id")->fetchAll();
$recent = $pdo->query("SELECT b.*, s.name service_name, p.method FROM bookings b JOIN services s ON s.id=b.service_id LEFT JOIN payments p ON p.id=b.payment_id ORDER BY b.id DESC LIMIT 8")->fetchAll();
?>
<div class="stats">
    <div class="stat-card"><span>Total Bookings</span><strong><?= $stats['total'] ?></strong></div>
    <div class="stat-card"><span>Today’s Bookings</span><strong><?= $stats['today'] ?></strong></div>
    <div class="stat-card"><span>Pending</span><strong><?= $stats['pending'] ?></strong></div>
    <div class="stat-card"><span>Confirmed</span><strong><?= $stats['confirmed'] ?></strong></div>
    <div class="stat-card"><span>Cancelled</span><strong><?= $stats['cancelled'] ?></strong></div>
    <div class="stat-card"><span>Completed</span><strong><?= $stats['completed'] ?></strong></div>
    <div class="stat-card"><span>Total Revenue</span><strong><?= money($stats['revenue']) ?></strong></div>
    <?php foreach($revenueByService as $r): ?><div class="stat-card"><span><?= e($r['name']) ?> Revenue</span><strong><?= money($r['total']) ?></strong></div><?php endforeach; ?>
</div>
<div class="panel">
    <h2>Recent bookings</h2>
    <table><thead><tr><th>Invoice</th><th>Customer</th><th>Service</th><th>Date/Time</th><th>Payment</th><th>Status</th><th>Amount</th></tr></thead><tbody>
    <?php foreach($recent as $b): ?><tr><td><?= e($b['invoice_number']) ?></td><td><?= e($b['customer_name']) ?><br><?= e($b['phone']) ?></td><td><?= e($b['service_name']) ?></td><td><?= e($b['booking_date']) ?><br><?= e(date('h:i A', strtotime($b['start_time']))) ?></td><td><?= e($b['method'] ?? '-') ?></td><td><span class="pill <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td><td><?= money($b['total_amount']) ?></td></tr><?php endforeach; ?>
    </tbody></table>
</div>
<?php require_once 'includes_admin_footer.php'; ?>
