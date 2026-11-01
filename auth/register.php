<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'candidate/dashboard.php');
}
$error = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = request_string($_POST, 'full_name');
    $email = request_string($_POST, 'email');
    $phone = request_string($_POST, 'phone');
    $password = $_POST['password'] ?? '';
    if (!is_string($password)) {
        $password = '';
    }
    if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 10 || strlen($password) > 4096) {
        $error = 'Enter a name and valid email, and choose a password with at least 10 characters.';
    } elseif (strlen($phone) > 30) {
        $error = 'Phone number must be 30 characters or fewer.';
    } else {
        $statement = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $statement->execute([$email]);
        if ($statement->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $statement = $pdo->prepare('INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)');
            try {
                $statement->execute([$name, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }
                $error = 'An account with that email already exists.';
            }
            if ($error === '') {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'full_name' => $name, 'email' => $email, 'role' => 'user', 'profile_completed' => 0];
                flash('success', 'Your account is ready. Complete your profile to start applying.');
                redirect('candidate/profile.php');
            }
        }
    }
}
$pageTitle = 'Create your profile';
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-page"><div class="auth-card">
    <span class="eyebrow">A better next step</span><h1>Create your profile</h1><p class="text-secondary">Get started in less than a minute. You can finish your profile later.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12"><label for="full_name" class="form-label">Full name</label><input id="full_name" class="form-control" name="full_name" autocomplete="name" maxlength="120" value="<?= e($name) ?>" required></div>
        <div class="col-12"><label for="email" class="form-label">Email address</label><input id="email" class="form-control" type="email" name="email" autocomplete="email" maxlength="190" value="<?= e($email) ?>" required></div>
        <div class="col-12"><label for="phone" class="form-label">Phone <span class="text-secondary">(optional)</span></label><input id="phone" class="form-control" name="phone" autocomplete="tel" maxlength="30"></div>
        <div class="col-12"><label for="password" class="form-label">Password</label><input id="password" class="form-control" type="password" name="password" autocomplete="new-password" minlength="10" required><div class="form-text">Use at least 10 characters.</div></div>
        <div class="col-12"><button class="btn btn-primary w-100" type="submit">Create account <i class="bi bi-arrow-right"></i></button></div>
    </form>
    <p class="auth-switch">Already have an account? <a href="<?= e(url('auth/login.php')) ?>">Log in</a></p>
</div></main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
