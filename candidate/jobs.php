<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
$query = request_string($_GET, 'q');
$location = request_string($_GET, 'location');
$type = request_string($_GET, 'type');
$qualification = request_string($_GET, 'qualification');
$sql = "SELECT j.* FROM jobs j WHERE j.status = 'active'";
$params = [];
if ($query !== '') {
    $sql .= ' AND (j.title LIKE ? OR j.company LIKE ? OR j.skills_required LIKE ?)';
    $wildcard = '%' . $query . '%';
    array_push($params, $wildcard, $wildcard, $wildcard);
}
if ($location !== '') {
    $sql .= ' AND (j.location LIKE ? OR j.available_cities LIKE ?)';
    $wildcard = '%' . $location . '%';
    array_push($params, $wildcard, $wildcard);
}
if ($type !== '') {
    $sql .= ' AND j.job_type = ?';
    $params[] = $type;
}
if ($qualification !== '') {
    $sql .= ' AND j.qualification LIKE ?';
    $params[] = '%' . $qualification . '%';
}
$sql .= ' ORDER BY j.created_at DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$jobs = $statement->fetchAll();
$pageTitle = 'Find jobs';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap">
    <div class="page-heading"><div><span class="eyebrow">Find your next move</span><h1>Explore open roles</h1><p>Search real opportunities and discover a role that fits your skills.</p></div><span class="result-count"><?= count($jobs) ?> <?= count($jobs) === 1 ? 'role' : 'roles' ?> found</span></div>
    <form method="get" class="content-panel filters-form">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4"><label class="form-label" for="q">Title, company, or skill</label><input class="form-control" id="q" name="q" value="<?= e($query) ?>" placeholder="e.g. Product Designer"></div>
            <div class="col-lg-3"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" value="<?= e($location) ?>" placeholder="City or remote"></div>
            <div class="col-lg-2"><label class="form-label" for="type">Job type</label><select class="form-select" id="type" name="type"><option value="">All types</option><?php foreach (['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'] as $option): ?><option value="<?= e($option) ?>" <?= $type === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
            <div class="col-lg-2"><label class="form-label" for="qualification">Qualification</label><input class="form-control" id="qualification" name="qualification" value="<?= e($qualification) ?>" placeholder="Any"></div>
            <div class="col-lg-1 d-grid"><button class="btn btn-primary" type="submit" aria-label="Apply filters"><i class="bi bi-search"></i></button></div>
        </div>
    </form>
    <?php if (!$jobs): ?><div class="empty-state content-panel"><i class="bi bi-search"></i><h3>No roles match those filters</h3><p>Try a different title, skill, or location.</p><a class="btn btn-outline-primary" href="<?= e(url('candidate/jobs.php')) ?>">Clear filters</a></div>
    <?php else: ?><div class="row g-3 mt-1"><?php foreach ($jobs as $job): ?>
        <div class="col-md-6 col-xl-4"><article class="job-card h-100"><div class="job-card-top"><div class="company-mark"><?= e(strtoupper(substr($job['company'], 0, 1))) ?></div><span class="job-type"><?= e($job['job_type']) ?></span></div><h3><?= e($job['title']) ?></h3><div class="company-name"><?= e($job['company']) ?></div><div class="job-meta"><span><i class="bi bi-geo-alt"></i><?= e($job['location']) ?></span><span><i class="bi bi-mortarboard"></i><?= e($job['qualification']) ?></span></div><p class="job-description-preview"><?= e(substr($job['description'], 0, 135)) ?><?= strlen($job['description']) > 135 ? '…' : '' ?></p><div class="job-card-bottom"><strong><?= e($job['salary'] ?: 'Salary not listed') ?></strong><a class="btn btn-outline-primary btn-sm" href="<?= e(url('candidate/job_details.php?id=' . (int)$job['id'])) ?>">View details</a></div></article></div>
    <?php endforeach; ?></div><?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
