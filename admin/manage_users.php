<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $candidateId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if (!$candidateId) {
        http_response_code(400);
        exit('Invalid candidate.');
    }
    $statement = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $statement->execute([$candidateId]);
    flash('success', $statement->rowCount() ? 'Candidate and related applications were deleted.' : 'Candidate was not found.');
    redirect('admin/manage_users.php');
}
$query = request_string($_GET, 'q');
$statement = $pdo->prepare(
    "SELECT u.id, u.full_name, u.email, u.phone, u.profile_completed, u.created_at, COUNT(a.id) AS applications
     FROM users u LEFT JOIN applications a ON a.user_id = u.id WHERE u.role = 'user'
     AND (? = '' OR u.full_name LIKE ? OR u.email LIKE ?) GROUP BY u.id ORDER BY u.created_at DESC"
);
$wildcard = '%' . $query . '%';
$statement->execute([$query, $wildcard, $wildcard]);
$users = $statement->fetchAll();
$pageTitle = 'Manage candidates';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Community</span><h1>Manage candidates</h1><p>Find candidate accounts and review their profiles.</p></div><span class="result-count"><?= count($users) ?> candidates</span></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <form method="get" class="content-panel filter-inline mb-3"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" name="q" aria-label="Search candidates" value="<?= e($query) ?>" placeholder="Search by name or email"><button class="btn btn-primary" type="submit">Search</button></div></form>
    <section class="content-panel"><?php if (!$users): ?><div class="empty-state"><i class="bi bi-people"></i><h3>No candidates found</h3><p>Try a different search term.</p></div>
    <?php else: ?><div class="table-responsive"><table class="table align-middle portal-table"><thead><tr><th>Candidate</th><th>Phone</th><th>Profile</th><th>Applications</th><th>Joined</th><th>Actions</th></tr></thead><tbody><?php foreach ($users as $candidate): ?><tr><td><div class="fw-semibold"><?= e($candidate['full_name']) ?></div><div class="small text-secondary"><?= e($candidate['email']) ?></div></td><td><?= e($candidate['phone'] ?: '—') ?></td><td><span class="status-badge <?= $candidate['profile_completed'] ? 'status-selected' : 'status-pending' ?>"><?= $candidate['profile_completed'] ? 'Complete' : 'In progress' ?></span></td><td><?= (int)$candidate['applications'] ?></td><td><?= e(date('M j, Y', strtotime($candidate['created_at']))) ?></td><td><div class="table-actions"><a class="btn btn-light btn-sm" href="<?= e(url('admin/view_user.php?id=' . (int)$candidate['id'])) ?>">View profile</a><form method="post" data-confirm="Delete this candidate and all their applications and bookmarks?"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int)$candidate['id'] ?>"><button class="btn btn-outline-danger btn-sm" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
