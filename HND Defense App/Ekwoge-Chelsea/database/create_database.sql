-- TaskFlow Database Creation Script
-- Run this in phpMyAdmin to create the database structure

-- Create Database
CREATE DATABASE IF NOT EXISTS taskflow_db;
USE taskflow_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    phone VARCHAR(30) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    preferences JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tasks Table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    priority ENUM('Low','Medium','High') DEFAULT 'Medium',
    status ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
    project_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Projects Table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Sample Data (Optional - for testing)
INSERT INTO users (first_name, last_name, email, password) VALUES 
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample Projects
INSERT INTO projects (user_id, project_name, description, color) VALUES 
(1, 'Website Redesign', 'Complete overhaul of company website', '#667eea'),
(1, 'Mobile App Development', 'Create iOS and Android apps', '#22c55e'),
(2, 'Marketing Campaign', 'Q2 marketing campaign planning', '#f59e0b');

-- Sample Tasks
INSERT INTO tasks (user_id, title, description, due_date, priority, status, project_id) VALUES 
(1, 'Complete project documentation', 'Write comprehensive documentation for the new feature', '2025-03-25', 'High', 'Pending', 1),
(1, 'Review pull requests', 'Check and approve pending PRs', '2025-03-20', 'Medium', 'Completed', 1),
(2, 'Team meeting preparation', 'Prepare slides for weekly team meeting', '2025-03-22', 'Medium', 'In Progress', 3);
