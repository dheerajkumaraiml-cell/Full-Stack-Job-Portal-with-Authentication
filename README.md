# SEE YOUR JOBS

A PHP and MySQL job portal for candidates and administrators. Candidate accounts can manage academic profiles, search and save roles, apply, and track application status. Administrators can post and manage jobs, review applications, update statuses, and manage candidates.

## Requirements

- XAMPP with Apache, MySQL, and PHP 8.0 or later
- MySQL 5.7+ or MariaDB 10.4+

## Install with XAMPP

1. Copy the `Job-Portal` folder into `C:\xampp\htdocs`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open phpMyAdmin and import `database/see_your_jobs.sql`. This creates the `see_your_jobs` database and its tables.
4. If your local MySQL credentials differ from the defaults (`root` with an empty password), set `SEE_DB_HOST`, `SEE_DB_NAME`, `SEE_DB_USER`, and `SEE_DB_PASSWORD` in the Apache/PHP environment. Defaults are `127.0.0.1`, `see_your_jobs`, `root`, and an empty password.
5. Create the first admin account from a command prompt after setting `SEE_ADMIN_NAME`, `SEE_ADMIN_EMAIL`, and a strong `SEE_ADMIN_PASSWORD` (at least 14 characters):

   ```powershell
   $env:SEE_ADMIN_NAME = "Portal Administrator"
   $env:SEE_ADMIN_EMAIL = "admin@example.com"
   $env:SEE_ADMIN_PASSWORD = "use-a-unique-password-of-14-or-more"
   C:\xampp\php\php.exe C:\xampp\htdocs\Job-Portal\setup_admin.php
   ```

   The setup utility only runs from the command line and refuses to overwrite an existing account.
6. Visit `http://localhost/Job-Portal/`. Register a candidate account or log in using the admin account you created.

The project intentionally does not ship with a default admin password.

## Candidate workflow

1. Register and complete the profile (addresses, education, and skills).
2. Browse and filter active jobs.
3. Open a role to bookmark it or apply. Applications require a completed profile and cannot be duplicated.
4. Track application status from the candidate dashboard or **My applications**.

## Admin workflow

Use the admin navigation to post/edit/hide jobs, search candidates, review applications, view candidate profiles, and change application status. Deleting a candidate or job cascades to its bookmarks and applications in the database.

## Manual smoke test

1. Register a candidate, sign out, then log in again and confirm the candidate dashboard opens.
2. Try opening an admin URL as that candidate; it should return **403 Forbidden**.
3. Complete the candidate profile and verify the dashboard enables job applications.
4. As admin, post a job. Confirm it appears in the public search, then test search filters, edit, hide/unhide, and delete.
5. As candidate, save the job and apply. Apply a second time and confirm the duplicate is rejected.
6. As admin, find the application, open the candidate profile and application details, then update the status through Pending, Shortlisted, Selected, and Rejected.
7. As candidate, verify the updated status appears in My applications and the dashboard counts.
8. Test candidate search and profile viewing, then confirm the browser asks for confirmation before deleting a candidate.

## Security notes

- Passwords use PHP's `password_hash()` and `password_verify()`.
- Database access uses PDO prepared statements.
- Sessions use HTTP-only, SameSite cookies and regenerate IDs after login.
- Every state-changing form, including logout, is protected by a session CSRF token.
- Admin and candidate routes enforce role checks; output is escaped before rendering.
- Configure HTTPS and non-default database credentials before deployment. Do not expose `setup_admin.php` through an internet-facing deployment; it is CLI-only.
