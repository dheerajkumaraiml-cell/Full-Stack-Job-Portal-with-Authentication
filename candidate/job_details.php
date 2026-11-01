<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$user = current_user();
$isCandidate = $user && $user['role'] === 'user';
$isAdmin = $user && $user['role'] === 'admin';
require_once __DIR__ . '/../config/db.php';
$jobId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$jobId) {
    http_response_code(404);
    exit('Job not found.');
}
$statement = $pdo->prepare(
    "SELECT j.*, a.status AS application_status,
        EXISTS(SELECT 1 FROM bookmarks b WHERE b.user_id = ? AND b.job_id = j.id) AS is_bookmarked
     FROM jobs j LEFT JOIN applications a ON a.job_id = j.id AND a.user_id = ?
     WHERE j.id = ? AND (j.status = 'active' OR a.id IS NOT NULL OR ? = 1)"
);
$statement->execute([(int)($user['id'] ?? 0), (int)($user['id'] ?? 0), $jobId, $isAdmin ? 1 : 0]);
$job = $statement->fetch();
if (!$job) {
    http_response_code(404);
    exit('This job is no longer available.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    if (!$isCandidate) {
        flash('error', 'Please sign in as a candidate to apply or save jobs.');
        redirect('auth/login.php');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'bookmark') {
        if ($job['is_bookmarked']) {
            $statement = $pdo->prepare('DELETE FROM bookmarks WHERE user_id = ? AND job_id = ?');
            $statement->execute([(int)$user['id'], $jobId]);
            flash('success', 'Job removed from your saved list.');
        } elseif ($job['status'] === 'active') {
            $statement = $pdo->prepare('INSERT INTO bookmarks (user_id, job_id) VALUES (?, ?)');
            $statement->execute([(int)$user['id'], $jobId]);
            flash('success', 'Job saved for later.');
        }
    } elseif ($action === 'apply') {
        if ($job['status'] !== 'active') {
            flash('error', 'This job is no longer accepting applications.');
        } elseif (empty($user['profile_completed'])) {
            flash('error', 'Complete your profile before applying.');
            redirect('candidate/profile.php');
        } elseif ($job['application_status']) {
            flash('error', 'You have already applied for this job.');
        } else {
            $statement = $pdo->prepare('INSERT INTO applications (user_id, job_id) VALUES (?, ?)');
            try {
                $statement->execute([(int)$user['id'], $jobId]);
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }
                flash('error', 'Your application could not be submitted because this role is no longer available or you already applied.');
                redirect('candidate/job_details.php?id=' . $jobId);
            }
            flash('success', 'Your application has been submitted.');
            redirect('candidate/my_applications.php');
        }
    } else {
        http_response_code(400);
        exit('Invalid action.');
    }
    redirect('candidate/job_details.php?id=' . $jobId);
}
$pageTitle = $job['title'];
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page">
    <a class="back-link" href="<?= e(url('candidate/jobs.php')) ?>"><i class="bi bi-arrow-left"></i> Back to jobs</a>
    <article class="content-panel detail-panel">
        <div class="job-detail-top"><div class="company-mark large-mark"><?= e(strtoupper(substr($job['company'], 0, 1))) ?></div><div><span class="job-type"><?= e($job['job_type']) ?></span><h1><?= e($job['title']) ?></h1><p class="mb-0"><?= e($job['company']) ?></p></div></div>
        <div class="detail-meta"><span><i class="bi bi-geo-alt"></i><?= e($job['location']) ?></span><span><i class="bi bi-cash-stack"></i><?= e($job['salary'] ?: 'Salary not listed') ?></span><span><i class="bi bi-clock"></i><?= e($job['experience'] ?: 'Experience not specified') ?></span><span><i class="bi bi-calendar3"></i>Posted <?= e(date('M j, Y', strtotime($job['created_at']))) ?></span></div>
        <div class="detail-actions">
            <?php if ($job['application_status']): ?><span class="status-badge <?= e(status_class($job['application_status'])) ?>">Applied · <?= e($job['application_status']) ?></span>
            <?php elseif ($isCandidate && $job['status'] === 'active'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="apply"><button class="btn btn-primary" type="submit">Apply now <i class="bi bi-arrow-up-right"></i></button></form><?php endif; ?>
            <?php if ($isCandidate && $job['status'] === 'active'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="bookmark"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-bookmark<?= $job['is_bookmarked'] ? '-fill' : '' ?>"></i> <?= $job['is_bookmarked'] ? 'Saved' : 'Save job' ?></button></form><?php endif; ?>
            <?php if (!$user && $job['status'] === 'active'): ?><a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Log in to apply <i class="bi bi-arrow-up-right"></i></a><?php endif; ?>
        </div>
        <?php if ($isCandidate && empty($user['profile_completed']) && !$job['application_status']): ?><div class="profile-callout slim-callout"><span>Complete your profile before applying.</span><a class="text-link" href="<?= e(url('candidate/profile.php')) ?>">Update profile <i class="bi bi-arrow-right"></i></a></div><?php endif; ?>
        <hr><section class="detail-section"><h2>About the role</h2><div class="preserve-lines"><?= nl2br(e($job['description'])) ?></div></section>
        <section class="detail-section"><h2>What you'll need</h2><dl class="job-requirements">
            <div><dt>Qualification</dt><dd><?= e($job['qualification']) ?></dd></div>
            <div><dt>Skills</dt><dd><?= e($job['skills_required'] ?: 'Not specified') ?></dd></div>
            <div><dt>10th requirement</dt><dd><?= e($job['req_percent_10'] ? $job['req_percent_10'] . '%' : 'Not specified') ?></dd></div>
            <div><dt>12th requirement</dt><dd><?= e($job['req_percent_12'] ? $job['req_percent_12'] . '%' : 'Not specified') ?></dd></div>
            <div><dt>Degree requirement</dt><dd><?= e($job['req_degree_percent'] ? $job['req_degree_percent'] . '%' : 'Not specified') ?></dd></div>
            <div><dt>Other cities</dt><dd><?= e($job['available_cities'] ?: 'Not specified') ?></dd></div>
        </dl></section>
    </article>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
