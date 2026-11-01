<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'candidate/dashboard.php');
}
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = request_string($_POST, 'email');
    $password = $_POST['password'] ?? '';
    if (!is_string($password)) {
        $password = '';
    }
    $statement = $pdo->prepare('SELECT id, full_name, email, role, password, profile_completed FROM users WHERE email = ?');
    $statement->execute([$email]);
    $account = $statement->fetch();
    if (!$account || !password_verify($password, $account['password'])) {
        $error = 'Email or password is incorrect.';
    } else {
        if (password_needs_rehash($account['password'], PASSWORD_DEFAULT)) {
            $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $account['id']]);
        }
        session_regenerate_id(true);
        unset($account['password']);
        $_SESSION['user'] = $account;
        redirect($account['role'] === 'admin' ? 'admin/dashboard.php' : 'candidate/dashboard.php');
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-page"><div class="auth-card">
    <span class="eyebrow">Welcome back</span><h1>Log in to your account</h1><p class="text-secondary">Your next opportunity could be one click away.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12"><label for="email" class="form-label">Email address</label><input id="email" class="form-control" type="email" name="email" autocomplete="email" value="<?= e($email) ?>" required></div>
        <div class="col-12"><label for="password" class="form-label">Password</label><input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required></div>
        <div class="col-12"><button class="btn btn-primary w-100" type="submit">Log in <i class="bi bi-arrow-right"></i></button></div>
    </form>
    <p class="auth-switch">New to SEE YOUR JOBS? <a href="<?= e(url('auth/register.php')) ?>">Create your profile</a></p>
</div></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
