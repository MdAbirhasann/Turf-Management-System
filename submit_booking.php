<?php
require_once 'includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('booking.php');

$serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
$date = $_POST['booking_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$customerName = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? '';
$transactionId = trim($_POST['transaction_id'] ?? '');
$note = trim($_POST['note'] ?? '');
$terms = isset($_POST['terms']);
$service = $serviceId ? getService($serviceId) : null;

$errors = [];
if (!$service) $errors[] = 'Please select a valid service.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date('Y-m-d')) $errors[] = 'Please select a valid booking date.';
if (!preg_match('/^\d{2}:\d{2}$/', $startTime)) $errors[] = 'Please select a valid time slot.';
if ($customerName === '' || strlen($customerName) < 2) $errors[] = 'Please enter your name.';
if ($phone === '' || strlen($phone) < 8) $errors[] = 'Please enter a valid phone number.';
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (!in_array($paymentMethod, ['bKash','Nagad'], true)) $errors[] = 'Please select a payment method.';
if ($transactionId === '') $errors[] = 'Please enter transaction ID.';
if (!$terms) $errors[] = 'You must agree to the terms and conditions.';

$duration = null;
$people = null;
$total = 0;
if ($service && $service['slug'] === 'swimming') {
    $people = max(1, (int)($_POST['people_count'] ?? 1));
    $duration = 60;
    $price = getPriceRow($serviceId, null);
    if (!$price) $errors[] = 'Swimming price is not configured.';
    else $total = $price['price'] * $people;
} else {
    $duration = filter_input(INPUT_POST, 'duration_minutes', FILTER_VALIDATE_INT);
    $price = getPriceRow($serviceId, $duration);
    if (!$duration || !$price) $errors[] = 'Please select a valid duration.';
    else $total = $price['price'];
}
$startTimeSql = $startTime . ':00';
$endTime = addMinutesToTime($startTime, $duration ?: 60);
if (!$errors && hasBlockedConflict($serviceId, $date, $startTimeSql, $endTime)) $errors[] = 'This slot is closed/unavailable.';
if (!$errors && hasBookingConflict($serviceId, $date, $startTimeSql, $endTime)) $errors[] = 'This slot is already booked or pending payment. Please choose another slot.';

if ($errors) {
    $msg = urlencode(implode(' ', $errors));
    redirect('booking.php?error=' . $msg);
}

try {
    $pdo->beginTransaction();
    $screenshot = uploadFile('payment_screenshot', 'assets/uploads/payments/');
    $paymentStmt = $pdo->prepare('INSERT INTO payments (method, transaction_id, screenshot_path, status) VALUES (?, ?, ?, ?)');
    $paymentStmt->execute([$paymentMethod, $transactionId, $screenshot, 'pending']);
    $paymentId = $pdo->lastInsertId();
    $invoiceNumber = generateInvoiceNumber();
    $bookingStmt = $pdo->prepare('INSERT INTO bookings (invoice_number, customer_name, phone, email, service_id, booking_date, start_time, end_time, duration_minutes, people_count, total_amount, note, payment_id, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $bookingStmt->execute([$invoiceNumber, $customerName, $phone, $email ?: null, $serviceId, $date, $startTimeSql, $endTime, $duration, $people, $total, $note ?: null, $paymentId, 'Pending', 'customer']);
    $bookingId = $pdo->lastInsertId();
    $invoiceStmt = $pdo->prepare('INSERT INTO invoices (booking_id, invoice_number) VALUES (?, ?)');
    $invoiceStmt->execute([$bookingId, $invoiceNumber]);
    $pdo->commit();
    redirect('invoice.php?invoice=' . urlencode($invoiceNumber) . '&new=1');
} catch (Exception $e) {
    $pdo->rollBack();
    redirect('booking.php?error=' . urlencode('Booking submission failed. Please try again.'));
}
?>
