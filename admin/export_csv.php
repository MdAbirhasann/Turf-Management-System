<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$where=[];$params=[];
if(!empty($_GET['date'])){$where[]='b.booking_date = ?';$params[]=$_GET['date'];}
if(!empty($_GET['service_id'])){$where[]='b.service_id = ?';$params[]=$_GET['service_id'];}
if(!empty($_GET['status'])){$where[]='b.status = ?';$params[]=$_GET['status'];}
if(!empty($_GET['payment_method'])){$where[]='p.method = ?';$params[]=$_GET['payment_method'];}
if(!empty($_GET['q'])){$where[]='(b.customer_name LIKE ? OR b.phone LIKE ? OR b.invoice_number LIKE ? OR p.transaction_id LIKE ?)';$q='%'.$_GET['q'].'%';array_push($params,$q,$q,$q,$q);} 
$sql="SELECT b.invoice_number,b.customer_name,b.phone,b.email,s.name service,b.booking_date,b.start_time,b.end_time,b.duration_minutes,b.people_count,b.total_amount,p.method,p.transaction_id,b.status,b.created_at FROM bookings b JOIN services s ON s.id=b.service_id LEFT JOIN payments p ON p.id=b.payment_id";
if($where)$sql.=' WHERE '.implode(' AND ',$where);$sql.=' ORDER BY b.id DESC';
$stmt=$pdo->prepare($sql);$stmt->execute($params);
header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="ts_sports_arena_bookings.csv"');$out=fopen('php://output','w');fputcsv($out,['Invoice','Customer','Phone','Email','Service','Date','Start','End','Duration','People','Amount','Payment','Transaction','Status','Created']);while($row=$stmt->fetch()){fputcsv($out,$row);}fclose($out);exit;
?>
