<?php
$pageTitle='Manage Pricing';require_once 'includes_admin_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    foreach($_POST['price'] as $id=>$value){$pdo->prepare('UPDATE prices SET price=? WHERE id=?')->execute([(float)$value,(int)$id]);}
    redirect('pricing.php?success=Pricing updated');
}
$prices=$pdo->query('SELECT p.*,s.name service_name FROM prices p JOIN services s ON s.id=p.service_id ORDER BY s.id,p.id')->fetchAll();
?>
<div class="panel"><form method="POST"><table><thead><tr><th>Service</th><th>Package</th><th>Type</th><th>Price</th></tr></thead><tbody><?php foreach($prices as $p): ?><tr><td><?= e($p['service_name']) ?></td><td><?= e($p['label']) ?></td><td><?= e($p['pricing_type']) ?></td><td><input type="number" min="0" name="price[<?= $p['id'] ?>]" value="<?= e($p['price']) ?>"></td></tr><?php endforeach; ?></tbody></table><br><button>Save Pricing</button></form></div>
<?php require_once 'includes_admin_footer.php'; ?>
