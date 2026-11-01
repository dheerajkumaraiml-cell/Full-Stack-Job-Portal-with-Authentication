<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$user = require_role('user');
require_once __DIR__ . '/../config/db.php';
$statement = $pdo->prepare('SELECT a.id, a.status, a.applied_at, j.id AS job_id, j.title, j.company, j.location, j.salary FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ? ORDER BY a.applied_at DESC');
$statement->execute([(int)$user['id']]);
$applications = $statement->fetchAll();
$pageTitle = 'My applications';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap">
    <div class="page-heading"><div><span class="eyebrow">Your progress</span><h1>My applications</h1><p>Track every opportunity you've applied for.</p></div><a class="btn btn-primary" href="<?= e(url('candidate/jobs.php')) ?>">Find more jobs <i class="bi bi-arrow-up-right"></i></a></div>
    <section class="content-panel">
        <?php if (!$applications): ?><div class="empty-state"><i class="bi bi-send"></i><h3>Your next opportunity is out there</h3><p>Once you apply, you can follow your application status here.</p><a class="btn btn-primary" href="<?= e(url('candidate/jobs.php')) ?>">Explore jobs</a></div>
        <?php else: ?><div class="table-responsive"><table class="table align-middle portal-table"><thead><tr><th>Position</th><th>Company</th><th>Location</th><th>Applied on</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($applications as $application): ?><tr><td class="fw-semibold"><?= e($application['title']) ?></td><td><?= e($application['company']) ?></td><td><?= e($application['location']) ?></td><td><?= e(date('M j, Y', strtotime($application['applied_at']))) ?></td><td><span class="status-badge <?= e(status_class($application['status'])) ?>"><?= e($application['status']) ?></span></td><td><a class="table-link" href="<?= e(url('candidate/job_details.php?id=' . (int)$application['job_id'])) ?>">View role</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
