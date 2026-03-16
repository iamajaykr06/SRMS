-- SRMS Optimized Database Schema for Production
-- Jharkhand Rai University Student Result Management System

-- Create database with optimized settings
CREATE DATABASE IF NOT EXISTS srms_jru 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE srms_jru;

-- Students table with optimized indexes
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    course VARCHAR(50) NOT NULL,
    semester TINYINT NOT NULL,
    batch_year YEAR NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_roll_number (roll_number),
    INDEX idx_course_semester (course, semester),
    INDEX idx_batch_year (batch_year),
    INDEX idx_name (name),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects table with proper indexing
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(100) NOT NULL,
    credits TINYINT NOT NULL DEFAULT 1,
    course VARCHAR(50) NOT NULL,
    semester TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_subject_code (subject_code),
    INDEX idx_course_semester (course, semester),
    INDEX idx_subject_name (subject_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Examinations table
CREATE TABLE IF NOT EXISTS examinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    session VARCHAR(20) NOT NULL,
    year YEAR NOT NULL,
    course VARCHAR(50) NOT NULL,
    semester TINYINT NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Composite index for common queries
    INDEX idx_course_semester_year (course, semester, year),
    INDEX idx_session (session),
    INDEX idx_status (status),
    INDEX idx_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Results table with optimized structure
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_id INT NOT NULL,
    internal_marks DECIMAL(5,2) DEFAULT 0,
    external_marks DECIMAL(5,2) DEFAULT 0,
    total_marks DECIMAL(5,2) GENERATED ALWAYS AS (internal_marks + external_marks) STORED,
    grade VARCHAR(2) GENERATED ALWAYS AS (
        CASE 
            WHEN (internal_marks + external_marks) >= 90 THEN 'O'
            WHEN (internal_marks + external_marks) >= 80 THEN 'A+'
            WHEN (internal_marks + external_marks) >= 70 THEN 'A'
            WHEN (internal_marks + external_marks) >= 60 THEN 'B+'
            WHEN (internal_marks + external_marks) >= 50 THEN 'B'
            WHEN (internal_marks + external_marks) >= 40 THEN 'C'
            ELSE 'F'
        END
    ) STORED,
    status ENUM('pass', 'fail') GENERATED ALWAYS AS (
        CASE 
            WHEN (internal_marks + external_marks) >= 40 THEN 'pass'
            ELSE 'fail'
        END
    ) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES examinations(id) ON DELETE CASCADE,
    
    -- Unique constraint to prevent duplicate entries
    UNIQUE KEY unique_student_subject_exam (student_id, subject_id, exam_id),
    
    -- Performance indexes
    INDEX idx_student_exam (student_id, exam_id),
    INDEX idx_exam_subject (exam_id, subject_id),
    INDEX idx_total_marks (total_marks),
    INDEX idx_grade (grade),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API rate limiting table
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_ip_endpoint (ip_address, endpoint),
    INDEX idx_window_start (window_start),
    
    -- Clean old entries automatically
    INDEX idx_last_request (last_request)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit log table for security
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stored procedure for optimized result retrieval
DELIMITER //
CREATE PROCEDURE sp_get_student_results(
    IN p_roll_number VARCHAR(20),
    IN p_session VARCHAR(20),
    IN p_semester TINYINT
)
BEGIN
    SELECT 
        s.roll_number,
        s.name,
        s.course,
        s.semester,
        e.exam_name,
        e.session,
        e.year as exam_year,
        r.subject_id,
        sub.subject_code,
        sub.subject_name,
        sub.credits,
        r.internal_marks,
        r.external_marks,
        r.total_marks,
        r.grade,
        r.status
    FROM students s
    JOIN examinations e ON s.semester = e.semester AND s.course = e.course
    JOIN results r ON s.id = r.student_id AND e.id = r.exam_id
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE s.roll_number = p_roll_number
    AND (p_session IS NULL OR e.session = p_session)
    AND (p_semester IS NULL OR s.semester = p_semester)
    ORDER BY e.year DESC, sub.subject_code;
END //
DELIMITER ;

-- Trigger for audit logging
DELIMITER //
CREATE TRIGGER tr_results_audit_insert
AFTER INSERT ON results
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (
        user_id, action, table_name, record_id, new_data, ip_address
    ) VALUES (
        COALESCE(@current_user_id, 'system'),
        'INSERT',
        'results',
        NEW.id,
        JSON_OBJECT(
            'student_id', NEW.student_id,
            'subject_id', NEW.subject_id,
            'exam_id', NEW.exam_id,
            'internal_marks', NEW.internal_marks,
            'external_marks', NEW.external_marks
        ),
        COALESCE(@current_ip, '127.0.0.1')
    );
END //
DELIMITER ;

-- View for student summary statistics
CREATE VIEW v_student_summary AS
SELECT 
    s.id,
    s.roll_number,
    s.name,
    s.course,
    s.semester,
    COUNT(r.id) as total_subjects,
    SUM(r.total_marks) as total_marks,
    SUM(sub.credits) as total_credits,
    ROUND(AVG(r.total_marks), 2) as average_marks,
    SUM(CASE WHEN r.status = 'pass' THEN 1 ELSE 0 END) as passed_subjects,
    CASE 
        WHEN SUM(CASE WHEN r.status = 'pass' THEN 1 ELSE 0 END) = COUNT(r.id) THEN 'PASS'
        ELSE 'FAIL'
    END as overall_status
FROM students s
LEFT JOIN results r ON s.id = r.student_id
LEFT JOIN subjects sub ON r.subject_id = sub.id
GROUP BY s.id, s.roll_number, s.name, s.course, s.semester;

-- Clean old rate limiting entries (older than 24 hours)
CREATE EVENT IF NOT EXISTS cleanup_rate_limits
ON SCHEDULE EVERY 1 HOUR
DO
    DELETE FROM api_rate_limits 
    WHERE window_start < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;
