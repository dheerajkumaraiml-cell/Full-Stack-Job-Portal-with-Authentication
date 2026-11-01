<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$values, $error] = validate_job_input($_POST);
    if (!$error) {
        $statement = $pdo->prepare(
            "INSERT INTO jobs (title, company, description, location, available_cities, qualification, skills_required,
             req_percent_10, req_percent_12, req_degree_percent, salary, job_type, experience)
             VALUES (:title, :company, :description, :location, :available_cities, :qualification, :skills_required,
             :req_percent_10, :req_percent_12, :req_degree_percent, :salary, :job_type, :experience)"
        );
        $statement->execute($values);
        flash('success', 'Your job posting is live.');
        redirect('admin/manage_jobs.php');
    }
}
$pageTitle = 'Post a job';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Grow your team</span><h1>Post a job</h1><p>Share the details candidates need to make their next move.</p></div></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="content-panel form-panel">
        <?= csrf_field() ?><div class="row g-3"><?php job_form($_POST); ?></div>
        <div class="form-actions"><a class="btn btn-outline-secondary" href="<?= e(url('admin/manage_jobs.php')) ?>">Cancel</a><button type="submit" class="btn btn-primary">Publish job <i class="bi bi-arrow-up-right"></i></button></div>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
