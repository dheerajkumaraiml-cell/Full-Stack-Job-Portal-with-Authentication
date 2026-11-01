<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$statuses = ['Pending', 'Shortlisted', 'Selected', 'Rejected'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = request_string($_POST, 'status');
    if (!$applicationId || !in_array($status, $statuses, true)) {
        http_response_code(400);
        exit('Invalid application status.');
    }
    $statement = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
    $statement->execute([$status, $applicationId]);
    flash('success', 'Application status updated.');
    redirect('admin/applications.php');
}
$query = request_string($_GET, 'q');
$statusFilter = request_string($_GET, 'status');
$sql = 'SELECT a.id, a.status, a.applied_at, u.full_name, u.email, j.title, j.company FROM applications a JOIN users u ON u.id = a.user_id JOIN jobs j ON j.id = a.job_id WHERE 1=1';
$params = [];
if ($query !== '') {
    $sql .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR j.title LIKE ? OR j.company LIKE ?)';
    $wildcard = '%' . $query . '%';
    array_push($params, $wildcard, $wildcard, $wildcard, $wildcard);
}
if (in_array($statusFilter, $statuses, true)) {
    $sql .= ' AND a.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY a.applied_at DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$applications = $statement->fetchAll();
$pageTitle = 'Review applications';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Admin workspace</span><h1>Applications</h1><p>Review candidate profiles and keep application statuses up to date.</p></div><span class="result-count"><?= count($applications) ?> records</span></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <form method="get" class="content-panel filter-inline mb-3"><label class="visually-hidden" for="q">Search applications</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input id="q" name="q" class="form-control" value="<?= e($query) ?>" placeholder="Search candidates or job titles"><select class="form-select status-filter" name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $statusFilter === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Filter</button></div></form>
    <?php if (!$applications): ?><div class="content-panel empty-state"><i class="bi bi-inbox"></i><h3>No matching applications</h3><p>Try adjusting your search or status filter.</p></div>
    <?php else: ?><div class="application-list"><?php foreach ($applications as $application): ?><article class="application-card">
        <div class="applicant-avatar"><?= e(strtoupper(substr($application['full_name'], 0, 1))) ?></div>
        <div class="application-main"><div class="application-person"><?= e($application['full_name']) ?></div><div class="text-secondary small"><?= e($application['email']) ?></div><div class="application-role"><i class="bi bi-briefcase"></i><?= e($application['title']) ?> <span>at <?= e($application['company']) ?></span></div><div class="small text-secondary mt-2">Applied <?= e(date('M j, Y', strtotime($application['applied_at']))) ?></div></div>
        <div class="application-status"><span class="status-badge <?= e(status_class($application['status'])) ?>"><?= e($application['status']) ?></span>
            <form method="post" class="status-update-form"><?= csrf_field() ?><input type="hidden" name="application_id" value="<?= (int)$application['id'] ?>"><label class="visually-hidden" for="status-<?= (int)$application['id'] ?>">Application status</label><select class="form-select form-select-sm" id="status-<?= (int)$application['id'] ?>" name="status"><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $application['status'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><button class="btn btn-primary btn-sm" type="submit">Update</button></form>
        </div>
        <div class="application-actions"><a class="btn btn-outline-primary btn-sm" href="<?= e(url('admin/view_user.php?application_id=' . (int)$application['id'])) ?>"><i class="bi bi-person-vcard"></i> Candidate</a><a class="btn btn-light btn-sm" href="<?= e(url('admin/view_application.php?id=' . (int)$application['id'])) ?>"><i class="bi bi-eye"></i> Details</a></div>
    </article><?php endforeach; ?></div><?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
