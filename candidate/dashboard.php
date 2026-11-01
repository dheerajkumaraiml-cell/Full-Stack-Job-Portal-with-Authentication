<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$user = require_role('user');
require_once __DIR__ . '/../config/db.php';
$id = (int)$user['id'];
$stats = ['jobs' => 0, 'applications' => 0, 'shortlisted' => 0, 'selected' => 0];
$stats['jobs'] = (int)$pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'")->fetchColumn();
$statement = $pdo->prepare("SELECT COUNT(*) AS applications, SUM(status = 'Shortlisted') AS shortlisted, SUM(status = 'Selected') AS selected FROM applications WHERE user_id = ?");
$statement->execute([$id]);
$counts = $statement->fetch();
$stats['applications'] = (int)$counts['applications'];
$stats['shortlisted'] = (int)$counts['shortlisted'];
$stats['selected'] = (int)$counts['selected'];
$statement = $pdo->prepare(
    'SELECT j.id, j.title, j.company, j.location, j.salary, a.status, a.applied_at
     FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ?
     ORDER BY a.applied_at DESC LIMIT 5'
);
$statement->execute([$id]);
$applications = $statement->fetchAll();
$pageTitle = 'Your dashboard';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap">
    <div class="page-heading"><div><span class="eyebrow">Candidate dashboard</span><h1>Good to see you, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h1><p>Keep an eye on your applications and find your next opportunity.</p></div><a class="btn btn-primary" href="<?= e(url('candidate/jobs.php')) ?>">Find a job <i class="bi bi-arrow-up-right"></i></a></div>
    <?php if (empty($user['profile_completed'])): ?><div class="profile-callout"><div><strong>Finish your profile to apply for jobs</strong><p class="mb-0">Add your education, contact details, and skills so employers can get to know you.</p></div><a class="btn btn-primary" href="<?= e(url('candidate/profile.php')) ?>">Complete profile <i class="bi bi-arrow-right"></i></a></div><?php endif; ?>
    <div class="row g-3 stats-grid">
        <?php foreach ([['Available jobs', $stats['jobs'], 'bi-briefcase', 'stat-blue'], ['Applications sent', $stats['applications'], 'bi-send', 'stat-violet'], ['Shortlisted', $stats['shortlisted'], 'bi-stars', 'stat-amber'], ['Selected', $stats['selected'], 'bi-check2-circle', 'stat-green']] as [$label, $value, $icon, $color]): ?>
            <div class="col-6 col-xl-3"><div class="stat-card"><span class="stat-icon <?= e($color) ?>"><i class="bi <?= e($icon) ?>"></i></span><div class="stat-value"><?= (int)$value ?></div><div class="stat-label"><?= e($label) ?></div></div></div>
        <?php endforeach; ?>
    </div>
    <section class="content-panel mt-4">
        <div class="section-heading compact"><div><h2>Recent applications</h2><p>Your latest application updates.</p></div><a class="text-link" href="<?= e(url('candidate/my_applications.php')) ?>">View all <i class="bi bi-arrow-right"></i></a></div>
        <?php if (!$applications): ?><div class="empty-state small-empty"><i class="bi bi-send"></i><h3>No applications yet</h3><p>When you apply for a role, its status will show up here.</p><a class="btn btn-outline-primary" href="<?= e(url('candidate/jobs.php')) ?>">Explore jobs</a></div>
        <?php else: ?><div class="table-responsive"><table class="table align-middle portal-table"><thead><tr><th>Role</th><th>Company</th><th>Applied</th><th>Status</th><th></th></tr></thead><tbody>
            <?php foreach ($applications as $application): ?><tr><td class="fw-semibold"><?= e($application['title']) ?></td><td><?= e($application['company']) ?></td><td><?= e(date('M j, Y', strtotime($application['applied_at']))) ?></td><td><span class="status-badge <?= e(status_class($application['status'])) ?>"><?= e($application['status']) ?></span></td><td><a class="table-link" href="<?= e(url('candidate/job_details.php?id=' . (int)$application['id'])) ?>">View role</a></td></tr><?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
