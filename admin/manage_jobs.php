<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    if (!$jobId || !in_array($action, ['toggle', 'delete'], true)) {
        http_response_code(400);
        exit('Invalid job action.');
    }
    if ($action === 'toggle') {
        $statement = $pdo->prepare("UPDATE jobs SET status = IF(status = 'active', 'hidden', 'active') WHERE id = ?");
        $statement->execute([$jobId]);
        flash('success', 'Job visibility updated.');
    } else {
        $statement = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
        $statement->execute([$jobId]);
        flash('success', 'Job and its associated applications and bookmarks were deleted.');
    }
    redirect('admin/manage_jobs.php');
}
$jobs = $pdo->query('SELECT j.id, j.title, j.company, j.location, j.job_type, j.status, j.created_at, COUNT(a.id) AS application_count FROM jobs j LEFT JOIN applications a ON a.job_id = j.id GROUP BY j.id ORDER BY j.created_at DESC')->fetchAll();
$pageTitle = 'Manage jobs';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Your opportunities</span><h1>Manage jobs</h1><p>Edit, hide, or remove your published roles.</p></div><a class="btn btn-primary" href="<?= e(url('admin/post_job.php')) ?>"><i class="bi bi-plus-lg"></i> Post a job</a></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <section class="content-panel"><?php if (!$jobs): ?><div class="empty-state"><i class="bi bi-briefcase"></i><h3>No jobs posted yet</h3><p>Publish your first role to start reaching candidates.</p><a class="btn btn-primary" href="<?= e(url('admin/post_job.php')) ?>">Post a job</a></div>
    <?php else: ?><div class="table-responsive"><table class="table align-middle portal-table"><thead><tr><th>Role</th><th>Location</th><th>Type</th><th>Applications</th><th>Visibility</th><th>Actions</th></tr></thead><tbody><?php foreach ($jobs as $job): ?><tr><td><div class="fw-semibold"><?= e($job['title']) ?></div><div class="small text-secondary"><?= e($job['company']) ?></div></td><td><?= e($job['location']) ?></td><td><?= e($job['job_type']) ?></td><td><?= (int)$job['application_count'] ?></td><td><span class="status-badge <?= $job['status'] === 'active' ? 'status-selected' : 'status-pending' ?>"><?= e(ucfirst($job['status'])) ?></span></td><td><div class="table-actions"><a class="btn btn-light btn-sm" href="<?= e(url('candidate/job_details.php?id=' . (int)$job['id'])) ?>">View</a><a class="btn btn-light btn-sm" href="<?= e(url('admin/edit_job.php?id=' . (int)$job['id'])) ?>">Edit</a><form method="post"><?= csrf_field() ?><input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-light btn-sm" type="submit"><?= $job['status'] === 'active' ? 'Hide' : 'Unhide' ?></button></form><form method="post" data-confirm="Delete this job? Associated applications and bookmarks will also be deleted."><?= csrf_field() ?><input type="hidden" name="job_id" value="<?= (int)$job['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-outline-danger btn-sm" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
