<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $_GET['action'] ?? '';
$map = ['confirm'=>'Confirmed','cancel'=>'Cancelled','complete'=>'Completed'];
if(!$id || !isset($map[$action])) redirect('bookings.php?error=Invalid action');
$status = $map[$action];
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE bookings SET status=? WHERE id=?');
    $stmt->execute([$status, $id]);
    if($status === 'Confirmed') {
        $pdo->prepare("UPDATE payments p JOIN bookings b ON b.payment_id=p.id SET p.status='verified' WHERE b.id=?")->execute([$id]);
    }
    if($status === 'Cancelled') {
        $pdo->prepare("UPDATE payments p JOIN bookings b ON b.payment_id=p.id SET p.status='rejected' WHERE b.id=?")->execute([$id]);
    }
    $pdo->commit();
    redirect('bookings.php?success=Booking updated to ' . urlencode($status));
} catch(Exception $e) {
    $pdo->rollBack();
    redirect('bookings.php?error=Unable to update booking');
}
?>
