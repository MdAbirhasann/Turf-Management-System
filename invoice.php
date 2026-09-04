<?php
$pageTitle = 'Invoice';
require_once 'includes/header.php';
$invoice = $_GET['invoice'] ?? '';
$stmt = $pdo->prepare("SELECT b.*, s.name service_name, s.slug, p.method, p.transaction_id, p.status payment_status, i.issued_at
    FROM bookings b
    JOIN services s ON s.id=b.service_id
    LEFT JOIN payments p ON p.id=b.payment_id
    LEFT JOIN invoices i ON i.booking_id=b.id
    WHERE b.invoice_number = ? LIMIT 1");
$stmt->execute([$invoice]);
$booking = $stmt->fetch();
if (!$booking): ?>
<section class="notfound"><div class="container"><h1>404</h1><p>Invoice not found.</p><a class="btn btn-glow" href="booking.php">Back to Booking</a></div></section>
<?php else: ?>
<?php if(isset($_GET['new'])): ?><div class="container"><div class="alert success">Booking request submitted successfully. Your booking is pending until manager payment verification.</div></div><?php endif; ?>
<div class="print-actions"><button class="btn btn-glow" data-print>Print / Download Invoice</button><a class="btn btn-outline" href="booking.php">Book Another Slot</a></div>
<section class="invoice">
    <div class="invoice-head">
        <div><img src="assets/images/logo.jpg" alt="TS Sports Arena"><h2>TS Sports Arena</h2><p>Football | Swimming | Badminton<br><?= e(ADDRESS) ?><br><?= e(CONTACT_PHONE) ?></p></div>
        <div style="text-align:right"><h1>INVOICE</h1><p><strong><?= e($booking['invoice_number']) ?></strong></p><p>Issued: <?= e(date('d M Y, h:i A', strtotime($booking['issued_at'] ?? $booking['created_at']))) ?></p><span class="pill <?= e($booking['status']) ?>"><?= e($booking['status']) ?></span></div>
    </div>
    <h3>Customer Details</h3>
    <table><tbody>
        <tr><td>Name</td><td><?= e($booking['customer_name']) ?></td></tr>
        <tr><td>Phone</td><td><?= e($booking['phone']) ?></td></tr>
        <tr><td>Email</td><td><?= e($booking['email'] ?: '-') ?></td></tr>
    </tbody></table>
    <h3 style="margin-top:24px">Booking Details</h3>
    <table><tbody>
        <tr><td>Service</td><td><?= e($booking['service_name']) ?></td></tr>
        <tr><td>Date</td><td><?= e(date('d M Y', strtotime($booking['booking_date']))) ?></td></tr>
        <tr><td>Time</td><td><?= e(date('h:i A', strtotime($booking['start_time']))) ?> - <?= e(date('h:i A', strtotime($booking['end_time']))) ?></td></tr>
        <tr><td><?= $booking['slug'] === 'swimming' ? 'Number of Persons' : 'Duration' ?></td><td><?= $booking['slug'] === 'swimming' ? e($booking['people_count']) : e($booking['duration_minutes'] . ' minutes') ?></td></tr>
        <tr><td>Payment Method</td><td><?= e($booking['method']) ?></td></tr>
        <tr><td>Transaction ID</td><td><?= e($booking['transaction_id']) ?></td></tr>
        <tr><td>Payment Status</td><td><?= e($booking['payment_status']) ?></td></tr>
        <tr><td><strong>Total Amount</strong></td><td><strong><?= money($booking['total_amount']) ?></strong></td></tr>
    </tbody></table>
    <p style="margin-top:28px">Note: This invoice confirms that your booking request was received. Final booking confirmation depends on manager payment verification.</p>
</section>
<?php endif; require_once 'includes/footer.php'; ?>
