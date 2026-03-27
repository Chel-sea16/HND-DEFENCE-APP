-- FocusTrack Database Schema
-- Generated from project analysis
-- Import this file into phpMyAdmin to create the database structure

-- Create Database
CREATE DATABASE IF NOT EXISTS focustrack
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE focustrack;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    profile_picture VARCHAR(255),
    phone VARCHAR(30) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    preferences JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_project_name (project_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks Table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    project_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_project_id (project_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
-- Default admin user
INSERT INTO users (id, first_name, last_name, email, password) VALUES 
(1, 'Admin', 'User', 'admin@focustrack.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE id=id;

-- Sample projects
INSERT INTO projects (user_id, project_name, description, color) VALUES 
(1, 'Website Redesign', 'Complete overhaul of company website with modern design', '#667eea'),
(1, 'Mobile App Development', 'Native iOS and Android app development', '#22c55e'),
(1, 'Marketing Campaign', 'Q2 marketing initiatives and social media strategy', '#f59e0b')
ON DUPLICATE KEY UPDATE id=id;

-- Sample tasks
INSERT INTO tasks (user_id, title, description, due_date, priority, status, project_id) VALUES 
(1, 'Complete project documentation', 'Write comprehensive documentation for the new features', '2024-03-20', 'high', 'in_progress', 1),
(1, 'Review pull requests', 'Review and merge pending pull requests', '2024-03-18', 'medium', 'completed', 1),
(1, 'Update dependencies', 'Update all npm packages to latest stable versions', '2024-03-22', 'low', 'pending', 1),
(1, 'Design mobile mockups', 'Create UI/UX mockups for mobile application', '2024-03-25', 'high', 'pending', 2),
(1, 'Setup CI/CD pipeline', 'Configure automated testing and deployment', '2024-03-28', 'medium', 'pending', 2),
(1, 'Social media content', 'Create content for Q2 marketing campaign', '2024-03-19', 'medium', 'in_progress', 3)
ON DUPLICATE KEY UPDATE id=id;