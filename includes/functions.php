<?php
require_once __DIR__ . '/../config/db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount) {
    return number_format((float)$amount, 0) . ' ' . CURRENCY;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function getServices($activeOnly = true) {
    global $pdo;
    $sql = 'SELECT * FROM services';
    if ($activeOnly) $sql .= " WHERE status='active'";
    $sql .= ' ORDER BY id ASC';
    return $pdo->query($sql)->fetchAll();
}

function getService($id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPrices($serviceId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM prices WHERE service_id = ? AND status='active' ORDER BY COALESCE(duration_minutes, 0), price");
    $stmt->execute([$serviceId]);
    return $stmt->fetchAll();
}

function getPriceRow($serviceId, $durationMinutes = null) {
    global $pdo;
    if ($durationMinutes === null || $durationMinutes === '') {
        $stmt = $pdo->prepare("SELECT * FROM prices WHERE service_id = ? AND pricing_type='per_person' AND status='active' LIMIT 1");
        $stmt->execute([$serviceId]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM prices WHERE service_id = ? AND duration_minutes = ? AND status='active' LIMIT 1");
        $stmt->execute([$serviceId, $durationMinutes]);
    }
    return $stmt->fetch();
}

function generateInvoiceNumber() {
    return 'TS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

function addMinutesToTime($time, $minutes) {
    return date('H:i:s', strtotime($time . ' +' . (int)$minutes . ' minutes'));
}

function overlapConditionSql($startAlias = ':start_time', $endAlias = ':end_time') {
    return "(start_time < {$endAlias} AND end_time > {$startAlias})";
}

function hasBookingConflict($serviceId, $date, $startTime, $endTime, $ignoreBookingId = null) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE service_id = :service_id
            AND booking_date = :booking_date
            AND status IN ('Pending','Confirmed')
            AND " . overlapConditionSql();
    $params = [
        ':service_id' => $serviceId,
        ':booking_date' => $date,
        ':start_time' => $startTime,
        ':end_time' => $endTime,
    ];
    if ($ignoreBookingId) {
        $sql .= ' AND id != :ignore_id';
        $params[':ignore_id'] = $ignoreBookingId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function hasBlockedConflict($serviceId, $date, $startTime, $endTime) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_slots
        WHERE service_id = :service_id AND block_date = :block_date
        AND start_time < :end_time AND end_time > :start_time");
    $stmt->execute([
        ':service_id' => $serviceId,
        ':block_date' => $date,
        ':start_time' => $startTime,
        ':end_time' => $endTime,
    ]);
    return (int)$stmt->fetchColumn() > 0;
}

function slotStatus($serviceId, $date, $startTime, $endTime) {
    global $pdo;
    $blocked = $pdo->prepare("SELECT reason FROM blocked_slots WHERE service_id = ? AND block_date = ? AND start_time < ? AND end_time > ? LIMIT 1");
    $blocked->execute([$serviceId, $date, $endTime, $startTime]);
    if ($blocked->fetch()) return 'Closed';

    $stmt = $pdo->prepare("SELECT status FROM bookings WHERE service_id = ? AND booking_date = ? AND status IN ('Pending','Confirmed','Completed') AND start_time < ? AND end_time > ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$serviceId, $date, $endTime, $startTime]);
    $row = $stmt->fetch();
    if (!$row) return 'Available';
    if ($row['status'] === 'Pending') return 'Pending payment';
    if ($row['status'] === 'Completed') return 'Completed';
    return 'Booked';
}

function getTimeSlots($start = '06:00', $end = '23:00', $stepMinutes = 30) {
    $slots = [];
    $current = strtotime($start);
    $endTs = strtotime($end);
    while ($current < $endTs) {
        $slots[] = date('H:i', $current);
        $current = strtotime("+{$stepMinutes} minutes", $current);
    }
    return $slots;
}

function pendingBookingCount() {
    global $pdo;
    return (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
}

function uploadFile($field, $folder = 'assets/uploads/payments/') {
    if (empty($_FILES[$field]['name'])) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) return null;
    $ext = $allowed[$mime];
    $safeName = date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
    $targetDir = __DIR__ . '/../' . $folder;
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetPath = $targetDir . $safeName;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
        return $folder . $safeName;
    }
    return null;
}
?>
