<?php
require_once __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        redirect('dashboard.php');
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Manager Login | TS Sports Arena</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body class="login-page">
<form class="login-card" method="POST">
    <img src="../assets/images/logo.jpg" alt="TS Sports Arena">
    <h1>Manager Panel</h1>
    <?php if($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
    <div class="field"><label>Email</label><input type="email" name="email" value="manager@tssportsarena.com" required></div>
    <div class="field"><label>Password</label><input type="password" name="password" placeholder="admin123" required></div>
    <button type="submit" style="width:100%">Login</button>
</form>
</body></html>
