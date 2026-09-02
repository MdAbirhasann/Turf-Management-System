<?php
$pageTitle = 'Manual Booking';
require_once 'includes_admin_header.php';
$services = getServices();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $serviceId=(int)$_POST['service_id'];$service=getService($serviceId);$date=$_POST['booking_date'];$start=$_POST['start_time'];$name=trim($_POST['customer_name']);$phone=trim($_POST['phone']);$email=trim($_POST['email']);$paymentMethod=$_POST['payment_method'];$transactionId=trim($_POST['transaction_id'] ?: 'MANUAL-'.time());
    $duration=null;$people=null;$total=0;$errors=[];
    if(!$service)$errors[]='Invalid service.';
    if($service && $service['slug']==='swimming'){$people=max(1,(int)$_POST['people_count']);$duration=60;$price=getPriceRow($serviceId,null);$total=$price?$price['price']*$people:0;}else{$duration=(int)$_POST['duration_minutes'];$price=getPriceRow($serviceId,$duration);$total=$price?$price['price']:0;}
    $startSql=$start.':00';$end=addMinutesToTime($start,$duration);
    if(hasBookingConflict($serviceId,$date,$startSql,$end) || hasBlockedConflict($serviceId,$date,$startSql,$end))$errors[]='Slot conflict found.';
    if(!$errors){$pdo->beginTransaction();$pdo->prepare('INSERT INTO payments(method,transaction_id,status) VALUES(?,?,?)')->execute([$paymentMethod,$transactionId,'verified']);$paymentId=$pdo->lastInsertId();$invoice=generateInvoiceNumber();$pdo->prepare('INSERT INTO bookings(invoice_number,customer_name,phone,email,service_id,booking_date,start_time,end_time,duration_minutes,people_count,total_amount,payment_id,status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$invoice,$name,$phone,$email?:null,$serviceId,$date,$startSql,$end,$duration,$people,$total,$paymentId,'Confirmed','manager']);$bookingId=$pdo->lastInsertId();$pdo->prepare('INSERT INTO invoices(booking_id,invoice_number) VALUES(?,?)')->execute([$bookingId,$invoice]);$pdo->commit();redirect('bookings.php?success=Manual booking added');}else{echo '<div class="notice error">'.e(implode(' ',$errors)).'</div>';}
}
?>
<div class="panel"><form method="POST" class="form-grid">
<div><label>Service</label><select name="service_id" required><?php foreach($services as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Date</label><input type="date" name="booking_date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required></div>
<div><label>Start Time</label><select name="start_time"><?php foreach(getTimeSlots('06:00','23:00',30) as $t): ?><option value="<?= e($t) ?>"><?= e(date('h:i A',strtotime($t))) ?></option><?php endforeach; ?></select></div>
<div><label>Duration for Football/Badminton</label><select name="duration_minutes"><option value="60">1 Hour</option><option value="90">1.5 Hours</option><option value="120">2 Hours</option></select></div>
<div><label>People for Swimming</label><input type="number" name="people_count" value="1" min="1"></div>
<div><label>Name</label><input name="customer_name" required></div>
<div><label>Phone</label><input name="phone" required></div>
<div><label>Email</label><input type="email" name="email"></div>
<div><label>Payment Method</label><select name="payment_method"><option>bKash</option><option>Nagad</option></select></div>
<div><label>Transaction ID</label><input name="transaction_id" placeholder="Optional for manual booking"></div>
<div class="field full"><button>Add Confirmed Booking</button></div>
</form></div>
<?php require_once 'includes_admin_footer.php'; ?>
