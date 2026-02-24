-- SRMS Database Schema
-- Jharkhand Rai University Student Result Management System

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS srms_jru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE srms_jru;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    course VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(10) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    credits INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Examinations table
CREATE TABLE IF NOT EXISTS examinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    session VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    semester INT NOT NULL,
    course VARCHAR(50) NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_id INT NOT NULL,
    internal_marks DECIMAL(5,2) DEFAULT 0,
    external_marks DECIMAL(5,2) DEFAULT 0,
    total_marks DECIMAL(5,2) GENERATED ALWAYS AS (internal_marks + external_marks) STORED,
    grade VARCHAR(2),
    status ENUM('pass', 'fail', 'absent') DEFAULT 'pass',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES examinations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_result (student_id, subject_id, exam_id)
);

-- Notices table
CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('general', 'schedule', 'result', 'holiday', 'urgent') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    file_path VARCHAR(255),
    publish_date DATE NOT NULL,
    expiry_date DATE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'operator') DEFAULT 'operator',
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO admin_users (username, email, password_hash, full_name, role) 
VALUES ('admin', 'admin@jru.edu.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Insert sample subjects for B.Tech CSE
INSERT IGNORE INTO subjects (subject_code, subject_name, course, semester, credits) VALUES
('CS101', 'Computer Fundamentals', 'B.Tech CSE', 1, 4),
('CS102', 'Programming in C', 'B.Tech CSE', 1, 4),
('MA101', 'Mathematics I', 'B.Tech CSE', 1, 4),
('PH101', 'Physics I', 'B.Tech CSE', 1, 3),
('CS201', 'Data Structures', 'B.Tech CSE', 2, 4),
('CS202', 'Digital Electronics', 'B.Tech CSE', 2, 4),
('MA201', 'Mathematics II', 'B.Tech CSE', 2, 4),
('CS301', 'Database Management Systems', 'B.Tech CSE', 3, 4),
('CS302', 'Computer Networks', 'B.Tech CSE', 3, 4),
('CS303', 'Operating Systems', 'B.Tech CSE', 3, 4);

-- Insert sample students
INSERT IGNORE INTO students (roll_number, name, email, phone, course, semester, year) VALUES
('JRU2021001', 'Rahul Kumar', 'rahul.kumar@jru.edu.in', '9876543210', 'B.Tech CSE', 3, 2021),
('JRU2021002', 'Priya Singh', 'priya.singh@jru.edu.in', '9876543211', 'B.Tech CSE', 3, 2021),
('JRU2021003', 'Amit Sharma', 'amit.sharma@jru.edu.in', '9876543212', 'B.Tech CSE', 3, 2021),
('JRU2021004', 'Neha Patel', 'neha.patel@jru.edu.in', '9876543213', 'B.Tech CSE', 3, 2021),
('JRU2021005', 'Vikram Gupta', 'vikram.gupta@jru.edu.in', '9876543214', 'B.Tech CSE', 3, 2021);

-- Insert sample examination
INSERT IGNORE INTO examinations (exam_name, session, year, semester, course, start_date, end_date, status) 
VALUES ('End Semester Examination', '2023-24', 2023, 3, 'B.Tech CSE', '2023-12-01', '2023-12-15', 'completed');

-- Insert sample notices
INSERT IGNORE INTO notices (title, content, category, priority, publish_date, status) VALUES
('End Semester Examination Schedule', 'The end semester examinations for B.Tech CSE 3rd semester will commence from December 1, 2023. Students are advised to check their examination centers and report 30 minutes before the scheduled time.', 'schedule', 'high', '2023-11-15', 'published'),
('Result Declaration Date', 'The results for the end semester examinations will be declared on January 15, 2024. Students can check their results online using their roll numbers.', 'result', 'medium', '2023-12-20', 'published'),
('Holiday Notice', 'The university will remain closed on account of Republic Day on January 26, 2024. All examinations and classes scheduled for this day are postponed.', 'holiday', 'low', '2024-01-20', 'published');
