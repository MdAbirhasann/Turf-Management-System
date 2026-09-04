<?php
$pageTitle = 'Bookings';
require_once 'includes_admin_header.php';
$where = [];$params=[];
if(!empty($_GET['date'])){$where[]='b.booking_date = ?';$params[]=$_GET['date'];}
if(!empty($_GET['service_id'])){$where[]='b.service_id = ?';$params[]=$_GET['service_id'];}
if(!empty($_GET['status'])){$where[]='b.status = ?';$params[]=$_GET['status'];}
if(!empty($_GET['payment_method'])){$where[]='p.method = ?';$params[]=$_GET['payment_method'];}
if(!empty($_GET['q'])){$where[]='(b.customer_name LIKE ? OR b.phone LIKE ? OR b.invoice_number LIKE ? OR p.transaction_id LIKE ?)';$q='%'.$_GET['q'].'%';array_push($params,$q,$q,$q,$q);} 
$sql = "SELECT b.*, s.name service_name, s.slug, p.method, p.transaction_id, p.screenshot_path, p.status payment_status FROM bookings b JOIN services s ON s.id=b.service_id LEFT JOIN payments p ON p.id=b.payment_id";
if($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= ' ORDER BY b.booking_date DESC, b.start_time DESC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);$bookings=$stmt->fetchAll();
$services=getServices(false);
?>
<div class="panel">
<form class="filters" method="GET">
    <input type="date" name="date" value="<?= e($_GET['date'] ?? '') ?>">
    <select name="service_id"><option value="">All Services</option><?php foreach($services as $s): ?><option value="<?= $s['id'] ?>" <?= ($_GET['service_id'] ?? '')==$s['id']?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
    <select name="status"><option value="">All Status</option><?php foreach(['Pending','Confirmed','Cancelled','Completed'] as $st): ?><option <?= ($_GET['status'] ?? '')===$st?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select>
    <select name="payment_method"><option value="">All Payments</option><option <?= ($_GET['payment_method'] ?? '')==='bKash'?'selected':'' ?>>bKash</option><option <?= ($_GET['payment_method'] ?? '')==='Nagad'?'selected':'' ?>>Nagad</option></select>
    <input type="search" name="q" placeholder="Name, phone, invoice, transaction" value="<?= e($_GET['q'] ?? '') ?>">
    <button>Filter</button><a class="btn secondary" href="export_csv.php?<?= e(http_build_query($_GET)) ?>">Export CSV</a><button type="button" class="btn secondary" data-print>Print</button>
</form>
<table><thead><tr><th>Invoice</th><th>Customer</th><th>Service</th><th>Slot</th><th>Payment</th><th>Screenshot</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($bookings as $b): ?>
<tr>
    <td><a href="../invoice.php?invoice=<?= urlencode($b['invoice_number']) ?>" target="_blank"><?= e($b['invoice_number']) ?></a></td>
    <td><?= e($b['customer_name']) ?><br><?= e($b['phone']) ?><br><?= e($b['email'] ?: '-') ?></td>
    <td><?= e($b['service_name']) ?><br><small><?= $b['slug']==='swimming' ? e($b['people_count'].' persons') : e($b['duration_minutes'].' minutes') ?></small></td>
    <td><?= e(date('d M Y', strtotime($b['booking_date']))) ?><br><?= e(date('h:i A', strtotime($b['start_time']))) ?> - <?= e(date('h:i A', strtotime($b['end_time']))) ?></td>
    <td><?= e($b['method'] ?? '-') ?><br><?= e($b['transaction_id'] ?? '-') ?><br><small><?= e($b['payment_status'] ?? '-') ?></small></td>
    <td><?php if($b['screenshot_path']): ?><a class="btn secondary" href="../<?= e($b['screenshot_path']) ?>" target="_blank">View</a><?php else: ?>-<?php endif; ?></td>
    <td><?= money($b['total_amount']) ?></td>
    <td><span class="pill <?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
    <td class="actions">
        <a class="btn" href="booking_action.php?id=<?= $b['id'] ?>&action=confirm" data-confirm="Confirm this booking?">Confirm</a>
        <a class="btn secondary" href="booking_action.php?id=<?= $b['id'] ?>&action=complete" data-confirm="Mark as completed?">Complete</a>
        <a class="btn danger" href="booking_action.php?id=<?= $b['id'] ?>&action=cancel" data-confirm="Cancel this booking?">Cancel</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php require_once 'includes_admin_footer.php'; ?>
