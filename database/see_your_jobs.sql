CREATE DATABASE IF NOT EXISTS see_your_jobs
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE see_your_jobs;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    profile_completed TINYINT(1) NOT NULL DEFAULT 0,
    present_address TEXT NULL,
    permanent_address TEXT NULL,
    board_10 VARCHAR(150) NULL,
    percent_10 DECIMAL(5,2) NULL,
    year_10 SMALLINT UNSIGNED NULL,
    board_12 VARCHAR(150) NULL,
    percent_12 DECIMAL(5,2) NULL,
    year_12 SMALLINT UNSIGNED NULL,
    degree_name VARCHAR(180) NULL,
    degree_percent DECIMAL(5,2) NULL,
    degree_year SMALLINT UNSIGNED NULL,
    masters_name VARCHAR(180) NULL,
    masters_percent DECIMAL(5,2) NULL,
    masters_year SMALLINT UNSIGNED NULL,
    skills TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    company VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(180) NOT NULL,
    available_cities TEXT NULL,
    qualification VARCHAR(180) NOT NULL,
    skills_required TEXT NULL,
    req_percent_10 DECIMAL(5,2) NULL,
    req_percent_12 DECIMAL(5,2) NULL,
    req_degree_percent DECIMAL(5,2) NULL,
    salary VARCHAR(120) NULL,
    job_type VARCHAR(40) NOT NULL,
    experience VARCHAR(100) NULL,
    status ENUM('active', 'hidden') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jobs_status_created (status, created_at),
    INDEX idx_jobs_location (location)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    job_id BIGINT UNSIGNED NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Shortlisted', 'Selected', 'Rejected') NOT NULL DEFAULT 'Pending',
    UNIQUE KEY uq_application_user_job (user_id, job_id),
    INDEX idx_applications_status (status),
    CONSTRAINT fk_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    job_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bookmark_user_job (user_id, job_id),
    CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookmarks_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
