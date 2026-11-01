<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$user = require_role('user');
require_once __DIR__ . '/../config/db.php';
$statement = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$statement->execute([(int)$user['id']]);
$profile = $statement->fetch();
$error = '';
$fields = ['phone', 'present_address', 'permanent_address', 'board_10', 'percent_10', 'year_10', 'board_12', 'percent_12', 'year_12', 'degree_name', 'degree_percent', 'degree_year', 'masters_name', 'masters_percent', 'masters_year', 'skills'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values = [];
    foreach ($fields as $field) {
        $values[$field] = request_string($_POST, $field);
    }
    if ($values['present_address'] === '' || $values['permanent_address'] === '' || $values['board_10'] === '' || $values['board_12'] === '' || $values['degree_name'] === '' || $values['skills'] === '') {
        $error = 'Complete your addresses, 10th and 12th boards, degree, and skills.';
    } elseif (strlen($values['phone']) > 30 || strlen($values['present_address']) > 2000 || strlen($values['permanent_address']) > 2000) {
        $error = 'Please check the length of your phone number and address fields.';
    } else {
        foreach (['board_10' => 150, 'board_12' => 150, 'degree_name' => 180, 'masters_name' => 180, 'skills' => 20000] as $field => $limit) {
            if (strlen($values[$field]) > $limit) {
                $error = ucfirst(str_replace('_', ' ', $field)) . ' is too long.';
            }
        }
        foreach (['percent_10', 'percent_12', 'degree_percent', 'masters_percent'] as $percentage) {
            if (!valid_percentage($values[$percentage])) {
                $error = 'Percentages must be between 0 and 100.';
            } elseif ($values[$percentage] === '') {
                $values[$percentage] = null;
            }
        }
        foreach (['year_10', 'year_12', 'degree_year', 'masters_year'] as $year) {
            if (!valid_year($values[$year])) {
                $error = 'Enter a valid passing year.';
            } elseif ($values[$year] === '') {
                $values[$year] = null;
            }
        }
        if ($error === '') {
            $update = $pdo->prepare(
                'UPDATE users SET phone = :phone, present_address = :present_address, permanent_address = :permanent_address,
                 board_10 = :board_10, percent_10 = :percent_10, year_10 = :year_10, board_12 = :board_12, percent_12 = :percent_12,
                 year_12 = :year_12, degree_name = :degree_name, degree_percent = :degree_percent, degree_year = :degree_year,
                 masters_name = :masters_name, masters_percent = :masters_percent, masters_year = :masters_year, skills = :skills,
                 profile_completed = 1 WHERE id = :id'
            );
            $update->execute($values + ['id' => (int)$user['id']]);
            $_SESSION['user']['profile_completed'] = 1;
            flash('success', 'Your profile has been saved.');
            redirect('candidate/profile.php');
        }
    }
    $profile = array_merge($profile, $values);
}
$pageTitle = 'My profile';
require __DIR__ . '/../includes/header.php';
?>
<main class="container page-wrap narrow-page">
    <div class="page-heading"><div><span class="eyebrow">Your professional story</span><h1>My profile</h1><p>Share your background so employers can find the right fit.</p></div><span class="profile-state <?= !empty($profile['profile_completed']) ? 'complete' : '' ?>"><i class="bi <?= !empty($profile['profile_completed']) ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i> <?= !empty($profile['profile_completed']) ? 'Profile complete' : 'Profile in progress' ?></span></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="content-panel form-panel">
        <?= csrf_field() ?>
        <h2>Personal information</h2><p class="form-intro">The contact details employers need to reach you.</p>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" value="<?= e($profile['full_name']) ?>" disabled></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?= e($profile['email']) ?>" disabled></div>
            <div class="col-12"><label for="phone" class="form-label">Phone</label><input id="phone" name="phone" class="form-control" maxlength="30" value="<?= e($profile['phone']) ?>"></div>
            <div class="col-md-6"><label for="present_address" class="form-label">Present address <span class="text-danger">*</span></label><textarea id="present_address" name="present_address" class="form-control" rows="3" required><?= e($profile['present_address']) ?></textarea></div>
            <div class="col-md-6"><label for="permanent_address" class="form-label">Permanent address <span class="text-danger">*</span></label><textarea id="permanent_address" name="permanent_address" class="form-control" rows="3" required><?= e($profile['permanent_address']) ?></textarea></div>
        </div>
        <hr><h2>Education</h2><p class="form-intro">Add your academic qualifications. Master's degree is optional.</p>
        <div class="education-block"><h3>10th standard</h3><div class="row g-3"><div class="col-md-6"><label class="form-label" for="board_10">Board name *</label><input id="board_10" name="board_10" class="form-control" value="<?= e($profile['board_10']) ?>" required></div><div class="col-md-3"><label class="form-label" for="percent_10">Percentage</label><input id="percent_10" name="percent_10" type="number" step="0.01" min="0" max="100" class="form-control" value="<?= e($profile['percent_10']) ?>"></div><div class="col-md-3"><label class="form-label" for="year_10">Passing year</label><input id="year_10" name="year_10" type="number" min="1950" max="<?= (int)date('Y') + 8 ?>" class="form-control" value="<?= e($profile['year_10']) ?>"></div></div></div>
        <div class="education-block"><h3>12th standard</h3><div class="row g-3"><div class="col-md-6"><label class="form-label" for="board_12">Board name *</label><input id="board_12" name="board_12" class="form-control" value="<?= e($profile['board_12']) ?>" required></div><div class="col-md-3"><label class="form-label" for="percent_12">Percentage</label><input id="percent_12" name="percent_12" type="number" step="0.01" min="0" max="100" class="form-control" value="<?= e($profile['percent_12']) ?>"></div><div class="col-md-3"><label class="form-label" for="year_12">Passing year</label><input id="year_12" name="year_12" type="number" min="1950" max="<?= (int)date('Y') + 8 ?>" class="form-control" value="<?= e($profile['year_12']) ?>"></div></div></div>
        <div class="education-block"><h3>Graduation</h3><div class="row g-3"><div class="col-md-6"><label class="form-label" for="degree_name">Degree name *</label><input id="degree_name" name="degree_name" class="form-control" value="<?= e($profile['degree_name']) ?>" required></div><div class="col-md-3"><label class="form-label" for="degree_percent">Percentage</label><input id="degree_percent" name="degree_percent" type="number" step="0.01" min="0" max="100" class="form-control" value="<?= e($profile['degree_percent']) ?>"></div><div class="col-md-3"><label class="form-label" for="degree_year">Passing year</label><input id="degree_year" name="degree_year" type="number" min="1950" max="<?= (int)date('Y') + 8 ?>" class="form-control" value="<?= e($profile['degree_year']) ?>"></div></div></div>
        <div class="education-block"><h3>Postgraduation <span class="optional-label">Optional</span></h3><div class="row g-3"><div class="col-md-6"><label class="form-label" for="masters_name">Master's degree</label><input id="masters_name" name="masters_name" class="form-control" value="<?= e($profile['masters_name']) ?>"></div><div class="col-md-3"><label class="form-label" for="masters_percent">Percentage</label><input id="masters_percent" name="masters_percent" type="number" step="0.01" min="0" max="100" class="form-control" value="<?= e($profile['masters_percent']) ?>"></div><div class="col-md-3"><label class="form-label" for="masters_year">Passing year</label><input id="masters_year" name="masters_year" type="number" min="1950" max="<?= (int)date('Y') + 8 ?>" class="form-control" value="<?= e($profile['masters_year']) ?>"></div></div></div>
        <hr><div class="mb-4"><label for="skills" class="form-label">Skills <span class="text-danger">*</span></label><textarea id="skills" name="skills" class="form-control" rows="3" placeholder="For example: Project management, Excel, Python" required><?= e($profile['skills']) ?></textarea><div class="form-text">Separate your skills with commas.</div></div>
        <button class="btn btn-primary" type="submit">Save profile <i class="bi bi-check2"></i></button>
    </form>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
