<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$user = require_role('user');
require_once __DIR__ . '/../config/db.php';
$statement = $pdo->prepare("SELECT j.* FROM bookmarks b JOIN jobs j ON j.id = b.job_id WHERE b.user_id = ? AND j.status = 'active' ORDER BY b.created_at DESC");
$statement->execute([(int)$user['id']]);
$jobs = $statement->fetchAll();
$pageTitle = 'Saved jobs';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap">
    <div class="page-heading"><div><span class="eyebrow">Keep your options open</span><h1>Saved jobs</h1><p>Roles you've bookmarked to come back to.</p></div><a class="btn btn-outline-primary" href="<?= e(url('candidate/jobs.php')) ?>">Browse jobs</a></div>
    <?php if (!$jobs): ?><div class="content-panel empty-state"><i class="bi bi-bookmark"></i><h3>No saved jobs yet</h3><p>Bookmark roles that catch your eye and they'll be waiting here.</p><a class="btn btn-primary" href="<?= e(url('candidate/jobs.php')) ?>">Find jobs</a></div>
    <?php else: ?><div class="row g-3"><?php foreach ($jobs as $job): ?><div class="col-md-6 col-xl-4"><article class="job-card h-100"><div class="job-card-top"><div class="company-mark"><?= e(strtoupper(substr($job['company'], 0, 1))) ?></div><span class="job-type"><?= e($job['job_type']) ?></span></div><h3><?= e($job['title']) ?></h3><div class="company-name"><?= e($job['company']) ?></div><div class="job-meta"><span><i class="bi bi-geo-alt"></i><?= e($job['location']) ?></span><span><i class="bi bi-mortarboard"></i><?= e($job['qualification']) ?></span></div><div class="job-card-bottom"><strong><?= e($job['salary'] ?: 'Salary not listed') ?></strong><a class="btn btn-outline-primary btn-sm" href="<?= e(url('candidate/job_details.php?id=' . (int)$job['id'])) ?>">View details</a></div></article></div><?php endforeach; ?></div><?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
