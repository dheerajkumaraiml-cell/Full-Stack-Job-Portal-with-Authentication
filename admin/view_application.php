<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$applicationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$applicationId) {
    http_response_code(404);
    exit('Application not found.');
}
$statement = $pdo->prepare(
    'SELECT a.id, a.status, a.applied_at, u.id AS user_id, u.full_name, u.email, u.phone, j.id AS job_id,
     j.title, j.company, j.location, j.job_type, j.salary FROM applications a
     JOIN users u ON u.id=a.user_id JOIN jobs j ON j.id=a.job_id WHERE a.id = ?'
);
$statement->execute([$applicationId]);
$application = $statement->fetch();
if (!$application) {
    http_response_code(404);
    exit('Application not found.');
}
$pageTitle = 'Application details';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page admin-layout"><?php require __DIR__ . '/../includes/admin_nav.php'; ?><a class="back-link" href="<?= e(url('admin/applications.php')) ?>"><i class="bi bi-arrow-left"></i> Back to applications</a>
    <div class="page-heading"><div><span class="eyebrow">Application #<?= (int)$application['id'] ?></span><h1><?= e($application['full_name']) ?></h1><p>Submitted <?= e(date('M j, Y', strtotime($application['applied_at']))) ?></p></div><span class="status-badge <?= e(status_class($application['status'])) ?>"><?= e($application['status']) ?></span></div>
    <section class="content-panel detail-section"><h2>Candidate</h2><dl class="job-requirements"><div><dt>Name</dt><dd><?= e($application['full_name']) ?></dd></div><div><dt>Email</dt><dd><?= e($application['email']) ?></dd></div><div><dt>Phone</dt><dd><?= e($application['phone'] ?: 'Not provided') ?></dd></div></dl><a class="btn btn-outline-primary" href="<?= e(url('admin/view_user.php?application_id=' . (int)$application['id'])) ?>">View full candidate profile</a></section>
    <section class="content-panel detail-section mt-3"><h2>Applied role</h2><dl class="job-requirements"><div><dt>Job title</dt><dd><?= e($application['title']) ?></dd></div><div><dt>Company</dt><dd><?= e($application['company']) ?></dd></div><div><dt>Location</dt><dd><?= e($application['location']) ?></dd></div><div><dt>Type</dt><dd><?= e($application['job_type']) ?></dd></div><div><dt>Salary</dt><dd><?= e($application['salary'] ?: 'Not listed') ?></dd></div></dl><a class="btn btn-outline-primary" href="<?= e(url('admin/edit_job.php?id=' . (int)$application['job_id'])) ?>">Manage this job</a></section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
