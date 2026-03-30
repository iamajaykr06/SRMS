-- Sample Data for SRMS Testing
-- Jharkhand Rai University Student Result Management System

USE srms_jru;

-- Insert sample university data
INSERT INTO universities (name, address, established_text, logo_path) VALUES 
('Jharkhand Rai University', 'Ranchi, Jharkhand, India', 'Established under Jharkhand State Legislature', '/assets/jrulogo.jpg');

-- Insert sample students
INSERT INTO students (roll_number, name, email, phone, course, semester, batch_year) VALUES 
('JRU2021001', 'Raj Kumar Singh', 'raj.kumar@jru.edu.in', '9876543210', 'BCA', 3, 2021),
('JRU2021002', 'Priya Sharma', 'priya.sharma@jru.edu.in', '9876543211', 'BCA', 3, 2021),
('JRU2021003', 'Amit Kumar', 'amit.kumar@jru.edu.in', '9876543212', 'BCA', 3, 2021),
('JRU2022001', 'Neha Verma', 'neha.verma@jru.edu.in', '9876543213', 'B.Tech CSE', 5, 2022),
('JRU2022002', 'Vikas Gupta', 'vikas.gupta@jru.edu.in', '9876543214', 'B.Tech CSE', 5, 2022);

-- Insert sample subjects
INSERT INTO subjects (subject_code, subject_name, credits, course, semester) VALUES 
('BCA301', 'Data Structures', 4, 'BCA', 3),
('BCA302', 'Database Management Systems', 4, 'BCA', 3),
('BCA303', 'Web Development', 3, 'BCA', 3),
('BCA304', 'Computer Networks', 3, 'BCA', 3),
('BCA305', 'Software Engineering', 3, 'BCA', 3),
('CSE501', 'Algorithm Design', 4, 'B.Tech CSE', 5),
('CSE502', 'Machine Learning', 4, 'B.Tech CSE', 5),
('CSE503', 'Cloud Computing', 3, 'B.Tech CSE', 5),
('CSE504', 'Cyber Security', 3, 'B.Tech CSE', 5),
('CSE505', 'Artificial Intelligence', 4, 'B.Tech CSE', 5);

-- Insert sample examinations
INSERT INTO examinations (exam_name, session, year, course, semester, start_date, end_date, status) VALUES 
('End Semester Examination', 'Dec 2024', 2024, 'BCA', 3, '2024-12-15', '2024-12-30', 'completed'),
('End Semester Examination', 'May 2024', 2024, 'BCA', 3, '2024-05-10', '2024-05-25', 'completed'),
('End Semester Examination', 'Dec 2024', 2024, 'B.Tech CSE', 5, '2024-12-15', '2024-12-30', 'completed'),
('End Semester Examination', 'May 2024', 2024, 'B.Tech CSE', 5, '2024-05-10', '2024-05-25', 'completed');

-- Insert sample results for BCA students - Dec 2024
INSERT INTO results (student_id, subject_id, exam_id, internal_marks, external_marks) VALUES 
-- Raj Kumar Singh (JRU2021001)
(1, 1, 1, 25, 65),  -- Data Structures: 90
(1, 2, 1, 28, 62),  -- DBMS: 90
(1, 3, 1, 22, 58),  -- Web Development: 80
(1, 4, 1, 20, 55),  -- Computer Networks: 75
(1, 5, 1, 24, 61),  -- Software Engineering: 85

-- Priya Sharma (JRU2021002)
(2, 1, 1, 23, 67),  -- Data Structures: 90
(2, 2, 1, 26, 64),  -- DBMS: 90
(2, 3, 1, 25, 60),  -- Web Development: 85
(2, 4, 1, 21, 59),  -- Computer Networks: 80
(2, 5, 1, 23, 62),  -- Software Engineering: 85

-- Amit Kumar (JRU2021003)
(3, 1, 1, 20, 55),  -- Data Structures: 75
(3, 2, 1, 24, 61),  -- DBMS: 85
(3, 3, 1, 23, 57),  -- Web Development: 80
(3, 4, 1, 18, 52),  -- Computer Networks: 70
(3, 5, 1, 22, 58),  -- Software Engineering: 80

-- Insert sample results for B.Tech students - Dec 2024
INSERT INTO results (student_id, subject_id, exam_id, internal_marks, external_marks) VALUES 
-- Nehra Verma (JRU2022001)
(4, 6, 3, 26, 64),  -- Algorithm Design: 90
(4, 7, 3, 25, 65),  -- Machine Learning: 90
(4, 8, 3, 22, 58),  -- Cloud Computing: 80
(4, 9, 3, 24, 61),  -- Cyber Security: 85
(4, 10, 3, 27, 63), -- Artificial Intelligence: 90

-- Vikas Gupta (JRU2022002)
(5, 6, 3, 24, 61),  -- Algorithm Design: 85
(5, 7, 3, 23, 62),  -- Machine Learning: 85
(5, 8, 3, 21, 54),  -- Cloud Computing: 75
(5, 9, 3, 23, 57),  -- Cyber Security: 80
(5, 10, 3, 25, 60); -- Artificial Intelligence: 85
