<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Find work that moves you forward';
$term = request_string($_GET, 'q');
$jobs = [];
$statement = $pdo->prepare(
    "SELECT id, title, company, location, salary, job_type, qualification, created_at
     FROM jobs WHERE status = 'active'
     AND (:term = '' OR title LIKE :title OR company LIKE :company OR skills_required LIKE :skills OR location LIKE :location)
     ORDER BY created_at DESC LIMIT 6"
);
$wildcard = '%' . $term . '%';
$statement->execute([
    'term' => $term,
    'title' => $wildcard,
    'company' => $wildcard,
    'skills' => $wildcard,
    'location' => $wildcard,
]);
$jobs = $statement->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero-section">
        <div class="container hero-content">
            <span class="eyebrow"><i class="bi bi-stars"></i> Your next chapter starts here</span>
            <h1>Find work that<br><span>moves you forward.</span></h1>
            <p>Discover opportunities from teams building what comes next.</p>
            <form class="search-panel" action="<?= e(url('candidate/jobs.php')) ?>" method="get">
                <label class="search-field">
                    <i class="bi bi-search"></i>
                    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Job title, company, or skill">
                </label>
                <label class="search-field location-field">
                    <i class="bi bi-geo-alt"></i>
                    <input type="search" name="location" value="<?= e(request_string($_GET, 'location')) ?>" placeholder="City or remote">
                </label>
                <button class="btn btn-primary btn-lg" type="submit">Search jobs <i class="bi bi-arrow-up-right"></i></button>
            </form>
            <div class="hero-note"><i class="bi bi-check-circle-fill"></i> Real opportunities. A profile that tells your story.</div>
        </div>
        <div class="hero-decoration" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="hero-sticker"><i class="bi bi-briefcase-fill"></i><span>Make your<br>next move</span></div></div>
    </section>

    <section class="container section-space">
        <div class="section-heading">
            <div><span class="eyebrow">Curated for you</span><h2>Fresh opportunities</h2><p>New roles from employers looking for their next great teammate.</p></div>
            <a class="text-link" href="<?= e(url('candidate/jobs.php')) ?>">Explore all jobs <i class="bi bi-arrow-right"></i></a>
        </div>
        <?php if ($jobs): ?>
            <div class="row g-3">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="job-card h-100">
                            <div class="job-card-top"><div class="company-mark"><?= e(strtoupper(substr($job['company'], 0, 1))) ?></div><span class="job-type"><?= e($job['job_type']) ?></span></div>
                            <h3><?= e($job['title']) ?></h3><div class="company-name"><?= e($job['company']) ?></div>
                            <div class="job-meta"><span><i class="bi bi-geo-alt"></i><?= e($job['location']) ?></span><span><i class="bi bi-mortarboard"></i><?= e($job['qualification']) ?></span></div>
                            <div class="job-card-bottom"><strong><?= e($job['salary'] ?: 'Salary not listed') ?></strong><a class="icon-link" href="<?= e(url('candidate/job_details.php?id=' . (int)$job['id'])) ?>" aria-label="View job"><i class="bi bi-arrow-up-right"></i></a></div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-search"></i><h3>No open jobs just yet</h3><p>Check back soon for new opportunities.</p><a class="btn btn-outline-primary" href="<?= e(url('candidate/jobs.php')) ?>">Browse job search</a></div>
        <?php endif; ?>
    </section>

    <section class="container section-space">
        <div class="value-banner">
            <div><span class="eyebrow">Built around your next step</span><h2>More than a job search.<br>A better way forward.</h2><p>Create your profile, save interesting roles, and track every application in one place.</p></div>
            <a href="<?= e(url('auth/register.php')) ?>" class="btn btn-light btn-lg">Create your free profile <i class="bi bi-arrow-up-right"></i></a>
            <div class="banner-shape" aria-hidden="true"></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
