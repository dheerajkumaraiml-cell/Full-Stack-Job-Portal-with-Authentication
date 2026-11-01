<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$baseDirectory = preg_replace('~/(admin|candidate|auth)$~', '', $scriptDirectory) ?: '';
if (!defined('APP_BASE')) {
    define('APP_BASE', rtrim($baseDirectory, '/'));
}

function url(string $path = ''): string
{
    return APP_BASE . '/' . ltrim($path, '/');
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function request_string(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) || is_numeric($value) ? trim((string)$value) : '';
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('Your session expired. Please go back, refresh the page, and try again.');
    }
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method not allowed.');
    }
    verify_csrf();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please sign in to continue.');
        redirect('auth/login.php');
    }
    return $user;
}

function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
    return $user;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function valid_percentage(mixed $value): bool
{
    return $value === '' || ($value !== null && is_numeric($value) && (float)$value >= 0 && (float)$value <= 100);
}

function valid_year(mixed $value): bool
{
    return $value === '' || ($value !== null && ctype_digit((string)$value) && (int)$value >= 1950 && (int)$value <= (int)date('Y') + 8);
}

function status_class(string $status): string
{
    return match ($status) {
        'Shortlisted' => 'status-shortlisted',
        'Selected' => 'status-selected',
        'Rejected' => 'status-rejected',
        default => 'status-pending',
    };
}

function validate_job_input(array $input): array
{
    $fields = ['title', 'company', 'description', 'location', 'available_cities', 'qualification', 'skills_required', 'req_percent_10', 'req_percent_12', 'req_degree_percent', 'salary', 'job_type', 'experience'];
    $values = [];
    foreach ($fields as $field) {
        $values[$field] = request_string($input, $field);
    }
    foreach (['title', 'company', 'description', 'location', 'qualification', 'job_type'] as $required) {
        if ($values[$required] === '') {
            return [$values, ucfirst(str_replace('_', ' ', $required)) . ' is required.'];
        }
    }
    foreach (['title' => 180, 'company' => 180, 'location' => 180, 'qualification' => 180, 'salary' => 120, 'job_type' => 40, 'experience' => 100] as $field => $limit) {
        if (strlen($values[$field]) > $limit) {
            return [$values, ucfirst(str_replace('_', ' ', $field)) . ' is too long.'];
        }
    }
    if (!in_array($values['job_type'], ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'], true)) {
        return [$values, 'Choose a valid job type.'];
    }
    if (strlen($values['description']) > 60000 || strlen($values['available_cities']) > 2000 || strlen($values['skills_required']) > 20000) {
        return [$values, 'The job description, cities, or skills exceed the allowed length.'];
    }
    foreach (['req_percent_10', 'req_percent_12', 'req_degree_percent'] as $percentage) {
        if (!valid_percentage($values[$percentage])) {
            return [$values, 'Percentage requirements must be between 0 and 100.'];
        }
        $values[$percentage] = $values[$percentage] === '' ? null : $values[$percentage];
    }
    return [$values, null];
}

function job_form(array $job = []): void
{
    $fields = [
        'title' => ['Job title', 'text', true], 'company' => ['Company name', 'text', true],
        'location' => ['Primary location', 'text', true], 'available_cities' => ['Other available cities', 'text', false],
        'qualification' => ['Required qualification', 'text', true], 'skills_required' => ['Required skills', 'text', false],
        'req_percent_10' => ['Minimum 10th percentage', 'number', false], 'req_percent_12' => ['Minimum 12th percentage', 'number', false],
        'req_degree_percent' => ['Minimum degree percentage', 'number', false], 'salary' => ['Salary range', 'text', false],
        'job_type' => ['Job type', 'select', true], 'experience' => ['Experience', 'text', false],
    ];
    foreach ($fields as $name => [$label, $type, $required]) {
        $value = e($job[$name] ?? '');
        echo '<div class="col-md-6"><label class="form-label" for="' . e($name) . '">' . e($label) . ($required ? ' <span class="text-danger">*</span>' : '') . '</label>';
        if ($type === 'select') {
            echo '<select class="form-select" id="' . e($name) . '" name="' . e($name) . '" required><option value="">Choose job type</option>';
            foreach (['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'] as $option) {
                echo '<option value="' . e($option) . '"' . ($value === $option ? ' selected' : '') . '>' . e($option) . '</option>';
            }
            echo '</select>';
        } else {
            $step = $type === 'number' ? ' step="0.01" min="0" max="100"' : '';
            echo '<input class="form-control" id="' . e($name) . '" name="' . e($name) . '" type="' . e($type) . '" value="' . $value . '"' . $step . ($required ? ' required' : '') . '>';
        }
        echo '</div>';
    }
    echo '<div class="col-12"><label class="form-label" for="description">Job description <span class="text-danger">*</span></label><textarea class="form-control" id="description" name="description" rows="7" required>' . e($job['description'] ?? '') . '</textarea></div>';
}
