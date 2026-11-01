<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$jobId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$jobId) {
    http_response_code(404);
    exit('Job not found.');
}
$statement = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
$statement->execute([$jobId]);
$job = $statement->fetch();
if (!$job) {
    http_response_code(404);
    exit('Job not found.');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$values, $error] = validate_job_input($_POST);
    if (!$error) {
        $values['id'] = $jobId;
        $statement = $pdo->prepare(
            'UPDATE jobs SET title=:title, company=:company, description=:description, location=:location,
             available_cities=:available_cities, qualification=:qualification, skills_required=:skills_required,
             req_percent_10=:req_percent_10, req_percent_12=:req_percent_12, req_degree_percent=:req_degree_percent,
             salary=:salary, job_type=:job_type, experience=:experience WHERE id=:id'
        );
        $statement->execute($values);
        flash('success', 'Job details updated.');
        redirect('admin/manage_jobs.php');
    }
    $job = array_merge($job, $values);
}
$pageTitle = 'Edit job';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page admin-layout">
    <div class="page-heading"><div><span class="eyebrow">Keep your listing current</span><h1>Edit job</h1><p>Update the role details shown to candidates.</p></div></div>
    <?php require __DIR__ . '/../includes/admin_nav.php'; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="content-panel form-panel"><?= csrf_field() ?><div class="row g-3"><?php job_form($job); ?></div><div class="form-actions"><a class="btn btn-outline-secondary" href="<?= e(url('admin/manage_jobs.php')) ?>">Cancel</a><button type="submit" class="btn btn-primary">Save changes <i class="bi bi-check2"></i></button></div></form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
