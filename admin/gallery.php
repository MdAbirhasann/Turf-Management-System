<?php
$pageTitle='Manage Gallery';require_once 'includes_admin_header.php';
if(isset($_GET['delete'])){$pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([(int)$_GET['delete']]);redirect('gallery.php?success=Image deleted');}
if($_SERVER['REQUEST_METHOD']==='POST'){$title=trim($_POST['title']);$path=uploadFile('image','assets/uploads/gallery/');if($path){$pdo->prepare('INSERT INTO gallery(title,image_path,status) VALUES(?,?,?)')->execute([$title,$path,'active']);redirect('gallery.php?success=Image uploaded');}else echo '<div class="notice error">Please upload a valid image.</div>';}
$items=$pdo->query('SELECT * FROM gallery ORDER BY id DESC')->fetchAll();
?>
<div class="panel"><form method="POST" enctype="multipart/form-data" class="filters"><input name="title" placeholder="Image title"><input type="file" name="image" accept="image/*" required><button>Upload</button></form><table><thead><tr><th>Image</th><th>Title</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><img class="thumb" src="../<?= e($i['image_path']) ?>"></td><td><?= e($i['title']) ?></td><td><?= e($i['status']) ?></td><td><a class="btn danger" data-confirm="Delete image?" href="gallery.php?delete=<?= $i['id'] ?>">Delete</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php require_once 'includes_admin_footer.php'; ?>
