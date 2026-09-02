<?php
$pageTitle='Manage Reviews';require_once 'includes_admin_header.php';
if(isset($_GET['delete'])){$pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([(int)$_GET['delete']]);redirect('reviews.php?success=Review deleted');}
if($_SERVER['REQUEST_METHOD']==='POST'){$pdo->prepare('INSERT INTO reviews(customer_name,rating,comment,status) VALUES(?,?,?,?)')->execute([trim($_POST['customer_name']),(int)$_POST['rating'],trim($_POST['comment']),'active']);redirect('reviews.php?success=Review added');}
$items=$pdo->query('SELECT * FROM reviews ORDER BY id DESC')->fetchAll();
?>
<div class="panel"><form method="POST" class="form-grid"><div><label>Name</label><input name="customer_name" required></div><div><label>Rating</label><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select></div><div class="field full"><label>Comment</label><textarea name="comment" required></textarea></div><div class="field full"><button>Add Review</button></div></form></div>
<div class="panel"><table><thead><tr><th>Name</th><th>Rating</th><th>Comment</th><th>Action</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?= e($i['customer_name']) ?></td><td><?= e($i['rating']) ?> ★</td><td><?= e($i['comment']) ?></td><td><a class="btn danger" data-confirm="Delete review?" href="reviews.php?delete=<?= $i['id'] ?>">Delete</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php require_once 'includes_admin_footer.php'; ?>
