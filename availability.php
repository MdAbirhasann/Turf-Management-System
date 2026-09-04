<?php
require_once 'includes/functions.php';
$serviceId = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT);
$date = $_GET['date'] ?? date('Y-m-d');
$duration = filter_input(INPUT_GET, 'duration', FILTER_VALIDATE_INT) ?: 60;
if (!$serviceId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo '<div class="alert error">Invalid slot request.</div>';
    exit;
}
echo '<div class="slot-grid">';
foreach (getTimeSlots('06:00','23:00',30) as $slot) {
    $start = $slot . ':00';
    $end = addMinutesToTime($slot, $duration);
    $status = slotStatus($serviceId, $date, $start, $end);
    $class = $status === 'Available' ? 'available' : ($status === 'Pending payment' ? 'pending' : ($status === 'Closed' ? 'closed' : 'booked'));
    echo '<div class="slot ' . e($class) . '"><strong>' . e(date('h:i A', strtotime($slot))) . '</strong><br><small>' . e($status) . '</small></div>';
}
echo '</div>';
?>
