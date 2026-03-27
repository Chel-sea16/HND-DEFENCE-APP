-- Cherry's Todo App Database Setup
-- This file creates the database structure for the Todo application

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS cherry_todo 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE cherry_todo;

-- Create projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_name (name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    project_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    
    -- Indexes for better performance
    INDEX idx_project_id (project_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_created_at (created_at),
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table (for future authentication features)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    profile_picture VARCHAR(255),
    bio TEXT,
    preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_email (email),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create task_tags table for many-to-many relationship between tasks and tags
CREATE TABLE IF NOT EXISTS task_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_task_id (task_id),
    INDEX idx_tag_name (tag_name),
    
    -- Unique constraint to prevent duplicate tags for the same task
    UNIQUE KEY unique_task_tag (task_id, tag_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for better performance
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for demonstration

-- Sample projects
INSERT INTO projects (name, description, color) VALUES
('Website Redesign', 'Complete overhaul of company website with modern design', '#667eea'),
('Mobile App Development', 'Native iOS and Android app development', '#22c55e'),
('Marketing Campaign', 'Q2 marketing initiatives and social media strategy', '#f59e0b'),
('Database Migration', 'Migrate legacy database to modern infrastructure', '#ef4444'),
('User Research', 'Conduct user research and usability testing', '#8b5cf6');

-- Sample tasks
INSERT INTO tasks (title, description, due_date, priority, status, project_id) VALUES
('Complete project documentation', 'Write comprehensive documentation for the new features including API endpoints and user guides', '2024-03-20', 'high', 'in_progress', 1),
('Review pull requests', 'Review and merge pending pull requests from the development team', '2024-03-18', 'medium', 'completed', 1),
('Update dependencies', 'Update all npm packages to latest stable versions and test compatibility', '2024-03-22', 'low', 'pending', 1),
('Design mobile mockups', 'Create UI/UX mockups for mobile application screens', '2024-03-25', 'high', 'pending', 2),
('Setup CI/CD pipeline', 'Configure automated testing and deployment pipeline', '2024-03-28', 'medium', 'pending', 2),
('Social media content', 'Create content for Q2 marketing campaign across all platforms', '2024-03-19', 'medium', 'in_progress', 3),
('Performance testing', 'Conduct load testing on current website infrastructure', '2024-03-24', 'high', 'pending', 1),
('User interviews', 'Schedule and conduct user interviews for research phase', '2024-03-21', 'medium', 'pending', 5),
('Database backup strategy', 'Implement automated backup strategy for production database', '2024-03-23', 'high', 'pending', 4),
('Analytics setup', 'Setup Google Analytics and custom tracking for marketing campaigns', '2024-03-26', 'medium', 'pending', 3);

-- Sample tags for tasks
INSERT INTO task_tags (task_id, tag_name) VALUES
(1, 'documentation'),
(1, 'urgent'),
(2, 'code-review'),
(3, 'maintenance'),
(4, 'design'),
(4, 'mobile'),
(5, 'devops'),
(6, 'marketing'),
(7, 'performance'),
(8, 'research'),
(9, 'database'),
(10, 'analytics');

-- Create views for common queries

-- View for tasks with project information
CREATE OR REPLACE VIEW task_details AS
SELECT 
    t.id,
    t.title,
    t.description,
    t.due_date,
    t.priority,
    t.status,
    t.project_id,
    p.name as project_name,
    p.color as project_color,
    t.created_at,
    t.updated_at,
    CASE 
        WHEN t.due_date < CURDATE() AND t.status != 'completed' THEN 'overdue'
        WHEN t.due_date = CURDATE() AND t.status != 'completed' THEN 'due_today'
        WHEN t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND t.status != 'completed' THEN 'due_soon'
        ELSE 'on_track'
    END as urgency_status
FROM tasks t
LEFT JOIN projects p ON t.project_id = p.id;

-- View for project statistics
CREATE OR REPLACE VIEW project_stats AS
SELECT 
    p.id,
    p.name,
    p.description,
    p.color,
    p.created_at,
    COUNT(t.id) as total_tasks,
    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
    SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
    SUM(CASE WHEN t.priority = 'high' THEN 1 ELSE 0 END) as high_priority_tasks,
    ROUND(
        CASE 
            WHEN COUNT(t.id) > 0 
            THEN (SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) / COUNT(t.id)) * 100 
            ELSE 0 
        END, 1
    ) as completion_percentage,
    MAX(t.due_date) as next_due_date
FROM projects p
LEFT JOIN tasks t ON p.id = t.project_id
GROUP BY p.id, p.name, p.description, p.color, p.created_at;

-- Create stored procedures for common operations

-- Procedure to get dashboard statistics
DELIMITER //
CREATE PROCEDURE GetDashboardStats()
BEGIN
    SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
        SUM(CASE WHEN priority = 'high' AND status != 'completed' THEN 1 ELSE 0 END) as high_priority_tasks,
        SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue_tasks
    FROM tasks;
END //
DELIMITER ;

-- Procedure to get user's upcoming tasks
DELIMITER //
CREATE PROCEDURE GetUpcomingTasks(IN days_ahead INT)
BEGIN
    SELECT 
        td.*,
        GROUP_CONCAT(tt.tag_name) as tags
    FROM task_details td
    LEFT JOIN task_tags tt ON td.id = tt.task_id
    WHERE td.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL days_ahead DAY)
    AND td.status != 'completed'
    GROUP BY td.id
    ORDER BY td.due_date ASC, td.priority DESC;
END //
DELIMITER ;

-- Create triggers for data integrity

-- Trigger to update project updated_at when tasks are modified
DELIMITER //
CREATE TRIGGER update_project_timestamp_on_task_change
AFTER INSERT ON tasks
FOR EACH ROW
BEGIN
    UPDATE projects 
    SET updated_at = CURRENT_TIMESTAMP 
    WHERE id = NEW.project_id;
END //
DELIMITER ;

DELIMITER //
CREATE TRIGGER update_project_timestamp_on_task_update
AFTER UPDATE ON tasks
FOR EACH ROW
BEGIN
    IF NEW.project_id != OLD.project_id THEN
        UPDATE projects 
        SET updated_at = CURRENT_TIMESTAMP 
        WHERE id IN (NEW.project_id, OLD.project_id);
    ELSEIF NEW.project_id IS NOT NULL THEN
        UPDATE projects 
        SET updated_at = CURRENT_TIMESTAMP 
        WHERE id = NEW.project_id;
    END IF;
END //
DELIMITER ;

-- Trigger to clean up orphaned tags
DELIMITER //
CREATE TRIGGER cleanup_task_tags_on_delete
AFTER DELETE ON tasks
FOR EACH ROW
BEGIN
    DELETE FROM task_tags WHERE task_id = OLD.id;
END //
DELIMITER ;

-- Create full-text search index for task titles and descriptions
ALTER TABLE tasks ADD FULLTEXT(title, description);

-- Function to calculate task completion percentage for a project
DELIMITER //
CREATE FUNCTION ProjectCompletionPercentage(project_id_param INT) 
RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE total_count INT DEFAULT 0;
    DECLARE completed_count INT DEFAULT 0;
    DECLARE percentage DECIMAL(5,2) DEFAULT 0;
    
    SELECT COUNT(*) INTO total_count 
    FROM tasks 
    WHERE project_id = project_id_param;
    
    IF total_count > 0 THEN
        SELECT COUNT(*) INTO completed_count 
        FROM tasks 
        WHERE project_id = project_id_param AND status = 'completed';
        
        SET percentage = (completed_count / total_count) * 100;
    END IF;
    
    RETURN percentage;
END //
DELIMITER ;

-- Create event to clean up old notifications (optional)
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS cleanup_old_notifications
-- ON SCHEDULE EVERY 1 WEEK
-- DO
-- DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Grant permissions (adjust as needed for your setup)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON cherry_todo.* TO 'cherry_user'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;

-- Display setup completion message
SELECT 'Cherry Todo database setup completed successfully!' as message;
