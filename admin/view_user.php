<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$applicationId = filter_input(INPUT_GET, 'application_id', FILTER_VALIDATE_INT);
$candidateId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$applicationId && !$candidateId) {
    http_response_code(404);
    exit('Candidate not found.');
}
$candidate = false;
if ($applicationId) {
    $statement = $pdo->prepare("SELECT u.* FROM users u JOIN applications a ON a.user_id = u.id WHERE a.id = ? AND u.role = 'user'");
    $statement->execute([$applicationId]);
    $candidate = $statement->fetch();
}
if (!$candidate && $candidateId) {
    $statement = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
    $statement->execute([$candidateId]);
    $candidate = $statement->fetch();
}
if (!$candidate) {
    http_response_code(404);
    exit('Candidate not found.');
}
$pageTitle = 'Candidate profile';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page admin-layout">
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <a class="back-link" href="<?= e(url('admin/manage_users.php')) ?>"><i class="bi bi-arrow-left"></i> Back to candidates</a>
    <div class="profile-hero"><div class="applicant-avatar profile-avatar"><?= e(strtoupper(substr($candidate['full_name'], 0, 1))) ?></div><div><span class="eyebrow">Candidate profile</span><h1><?= e($candidate['full_name']) ?></h1><p><?= e($candidate['email']) ?><?= $candidate['phone'] ? ' · ' . e($candidate['phone']) : '' ?></p></div></div>
    <section class="content-panel detail-section"><h2>Personal information</h2><dl class="job-requirements"><div><dt>Present address</dt><dd><?= nl2br(e($candidate['present_address'] ?: 'Not provided')) ?></dd></div><div><dt>Permanent address</dt><dd><?= nl2br(e($candidate['permanent_address'] ?: 'Not provided')) ?></dd></div><div><dt>Skills</dt><dd><?= nl2br(e($candidate['skills'] ?: 'Not provided')) ?></dd></div><div><dt>Joined</dt><dd><?= e(date('M j, Y', strtotime($candidate['created_at']))) ?></dd></div></dl></section>
    <section class="content-panel detail-section mt-3"><h2>Academic background</h2><div class="education-summary"><?php foreach ([['10th standard', $candidate['board_10'], $candidate['percent_10'], $candidate['year_10']], ['12th standard', $candidate['board_12'], $candidate['percent_12'], $candidate['year_12']], ['Graduation', $candidate['degree_name'], $candidate['degree_percent'], $candidate['degree_year']], ["Master's degree", $candidate['masters_name'], $candidate['masters_percent'], $candidate['masters_year']]] as [$label, $name, $percent, $year]): ?><div class="education-summary-row"><div><span class="small text-secondary"><?= e($label) ?></span><strong><?= e($name ?: 'Not provided') ?></strong></div><div><?= e($percent !== null ? $percent . '%' : '—') ?></div><div><?= e($year ?: '—') ?></div></div><?php endforeach; ?></div></section>
    <section class="content-panel detail-section mt-3"><h2>Applications</h2><?php $statement = $pdo->prepare('SELECT a.id, a.status, a.applied_at, j.title, j.company FROM applications a JOIN jobs j ON j.id = a.job_id WHERE a.user_id = ? ORDER BY a.applied_at DESC'); $statement->execute([(int)$candidate['id']]); $apps = $statement->fetchAll(); ?>
        <?php if (!$apps): ?><p class="text-secondary mb-0">No applications submitted.</p><?php else: ?><div class="table-responsive"><table class="table align-middle portal-table mb-0"><thead><tr><th>Role</th><th>Company</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($apps as $app): ?><tr><td><?= e($app['title']) ?></td><td><?= e($app['company']) ?></td><td><?= e(date('M j, Y', strtotime($app['applied_at']))) ?></td><td><span class="status-badge <?= e(status_class($app['status'])) ?>"><?= e($app['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
