-- Mental Health Pre-Assessment System Database Schema

CREATE DATABASE IF NOT EXISTS mental_health_db;
USE mental_health_db;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    roll_number VARCHAR(50) UNIQUE,
    user_type ENUM('student', 'counselor', 'admin') NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    contact_number VARCHAR(15),
    department VARCHAR(100),
    semester INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assessment Questions Table
CREATE TABLE assessment_questions (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    question_text TEXT NOT NULL,
    question_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Responses Table
CREATE TABLE student_responses (
    response_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    response_value INT NOT NULL,
    assessment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES assessment_questions(question_id) ON DELETE CASCADE,
    KEY idx_user_assessment (user_id, assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assessment Scores Table
CREATE TABLE assessment_scores (
    score_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    depression_score INT,
    anxiety_score INT,
    stress_score INT,
    overall_score INT,
    risk_level ENUM('Low', 'Moderate', 'High', 'Critical') NOT NULL,
    assessment_date TIMESTAMP,
    report_generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    counselor_notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    KEY idx_user_date (user_id, assessment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Counselor Notes Table
CREATE TABLE counselor_notes (
    note_id INT PRIMARY KEY AUTO_INCREMENT,
    counselor_id INT NOT NULL,
    student_id INT NOT NULL,
    note_text TEXT NOT NULL,
    recommendation VARCHAR(500),
    follow_up_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (counselor_id) REFERENCES users(user_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Session Log Table
CREATE TABLE session_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    login_time TIMESTAMP,
    logout_time TIMESTAMP NULL,
    activity TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Assessment Questions
INSERT INTO assessment_questions (category, question_text, question_number) VALUES
-- Depression Category
('Depression', 'I feel sad or empty most of the time', 1),
('Depression', 'I lose interest in activities I usually enjoy', 2),
('Depression', 'I have difficulty concentrating on tasks', 3),
('Depression', 'I feel worthless or guilty about things', 4),
('Depression', 'I have thoughts of hurting myself', 5),
-- Anxiety Category
('Anxiety', 'I feel nervous or anxious most days', 6),
('Anxiety', 'I worry about things I cannot control', 7),
('Anxiety', 'I experience sudden panic attacks', 8),
('Anxiety', 'I avoid situations that make me anxious', 9),
('Anxiety', 'My anxiety interferes with my daily activities', 10),
-- Stress Category
('Stress', 'I feel overwhelmed by my academic workload', 11),
('Stress', 'I have difficulty managing my time', 12),
('Stress', 'I experience physical symptoms of stress (headaches, muscle tension)', 13),
('Stress', 'I feel irritable or easily frustrated', 14),
('Stress', 'I have trouble sleeping due to stress', 15);

-- Create Admin / Head Counselor account (default password: admin123 - hashed)
INSERT INTO users (email, password, full_name, user_type) VALUES
('admin@mentalhealthportal.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'Head Counselor (Admin)', 'admin');

-- Counselor Availability Table
CREATE TABLE IF NOT EXISTS counselor_availability (
    availability_id INT PRIMARY KEY AUTO_INCREMENT,
    counselor_id INT NOT NULL,
    day_of_week TINYINT NOT NULL COMMENT '0=Sunday,1=Monday,...,6=Saturday',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_counselor_day (counselor_id, day_of_week),
    CONSTRAINT fk_avail_counselor FOREIGN KEY (counselor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

