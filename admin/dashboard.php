<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$stats = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'jobs' => (int)$pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn(),
    'applications' => (int)$pdo->query('SELECT COUNT(*) FROM applications')->fetchColumn(),
    'pending' => (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Pending'")->fetchColumn(),
    'selected' => (int)$pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Selected'")->fetchColumn(),
];
$recent = $pdo->query('SELECT a.id, a.status, a.applied_at, u.full_name, j.title, j.company FROM applications a JOIN users u ON u.id = a.user_id JOIN jobs j ON j.id = a.job_id ORDER BY a.applied_at DESC LIMIT 6')->fetchAll();
$pageTitle = 'Admin dashboard';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Admin workspace</span><h1>Overview</h1><p>Here’s what’s happening across SEE YOUR JOBS.</p></div><a class="btn btn-primary" href="<?= e(url('admin/post_job.php')) ?>"><i class="bi bi-plus-lg"></i> Post a job</a></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <div class="row g-3 stats-grid admin-stats">
        <?php foreach ([['Total candidates', $stats['users'], 'bi-people', 'stat-blue'], ['Total jobs', $stats['jobs'], 'bi-briefcase', 'stat-violet'], ['Applications', $stats['applications'], 'bi-file-earmark-text', 'stat-amber'], ['Pending review', $stats['pending'], 'bi-hourglass-split', 'stat-blue'], ['Selected candidates', $stats['selected'], 'bi-check2-circle', 'stat-green']] as [$label, $value, $icon, $color]): ?><div class="col-6 col-xl"><div class="stat-card"><span class="stat-icon <?= e($color) ?>"><i class="bi <?= e($icon) ?>"></i></span><div class="stat-value"><?= (int)$value ?></div><div class="stat-label"><?= e($label) ?></div></div></div><?php endforeach; ?>
    </div>
    <section class="content-panel mt-4"><div class="section-heading compact"><div><h2>Latest applications</h2><p>Recent activity from candidates.</p></div><a class="text-link" href="<?= e(url('admin/applications.php')) ?>">Review all <i class="bi bi-arrow-right"></i></a></div>
        <?php if (!$recent): ?><div class="empty-state small-empty"><i class="bi bi-inbox"></i><h3>No applications yet</h3><p>Applications will appear here as candidates apply.</p></div>
        <?php else: ?><div class="table-responsive"><table class="table align-middle portal-table"><thead><tr><th>Applicant</th><th>Job</th><th>Company</th><th>Applied</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($recent as $application): ?><tr><td class="fw-semibold"><?= e($application['full_name']) ?></td><td><?= e($application['title']) ?></td><td><?= e($application['company']) ?></td><td><?= e(date('M j, Y', strtotime($application['applied_at']))) ?></td><td><span class="status-badge <?= e(status_class($application['status'])) ?>"><?= e($application['status']) ?></span></td><td><a class="table-link" href="<?= e(url('admin/view_application.php?id=' . (int)$application['id'])) ?>">Review</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
