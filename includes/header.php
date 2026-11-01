<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'SEE YOUR JOBS';
$user = current_user();
$notice = take_flash();
$adminPage = $user && $user['role'] === 'admin' && str_contains(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10172a">
    <title><?= e($pageTitle) ?> | SEE YOUR JOBS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('css/style.css')) ?>" rel="stylesheet">
</head>
<body<?= $adminPage ? ' class="admin-body"' : '' ?>>
<nav class="navbar navbar-expand-lg site-nav">
    <div class="container">
        <a class="navbar-brand brand" href="<?= e(url('index.php')) ?>"><span class="brand-mark"><i class="bi bi-compass-fill"></i></span>SEE YOUR <span>JOBS</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('candidate/jobs.php')) ?>">Find jobs</a></li>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/dashboard.php')) ?>">Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('admin/applications.php')) ?>">Applications</a></li>
                <?php elseif ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('candidate/dashboard.php')) ?>">My dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('candidate/my_applications.php')) ?>">Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('candidate/saved_jobs.php')) ?>">Saved jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('candidate/profile.php')) ?>">My profile</a></li>
                <?php endif; ?>
                <?php if ($user): ?>
                    <li class="nav-item"><span class="nav-user"><?= e($user['full_name']) ?></span></li>
                    <li class="nav-item"><form action="<?= e(url('auth/logout.php')) ?>" method="post" class="d-inline"><?= csrf_field() ?><button class="btn btn-outline-primary btn-sm" type="submit">Log out</button></form></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('auth/login.php')) ?>">Log in</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm" href="<?= e(url('auth/register.php')) ?>">Create profile <i class="bi bi-arrow-up-right"></i></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php if ($notice): ?>
    <div class="container notice-wrap"><div class="alert alert-<?= e($notice['type'] === 'error' ? 'danger' : $notice['type']) ?> alert-dismissible fade show" role="alert"><?= e($notice['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div></div>
<?php endif; ?>
