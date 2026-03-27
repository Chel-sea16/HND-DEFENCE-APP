# Database Setup Guide

This directory contains the database setup files for Cherry's Todo application.

## Files

- `create_database.sql` - Canonical database schema for the PHP stack
- `setup.sql` - Legacy schema (older prototype)
- `README.md` - This file

## Quick Setup with XAMPP

### 1. Start XAMPP Services
- Open XAMPP Control Panel
- Start Apache and MySQL services

### 2. Access phpMyAdmin
- Open your web browser
- Go to `http://localhost/phpmyadmin`

### 3. Import Database
- Click on the "Import" tab
- Choose the `create_database.sql` file from this directory
- Click "Go" to execute the SQL script

### 4. Verify Installation
- You should see a new database named `taskflow_db`
- The database should contain the following tables:
  - `projects`
  - `tasks`
  - `users`
  - `task_tags`
  - `notifications`

## Manual Setup

If you prefer to run the SQL commands manually:

```sql
-- Create the database
CREATE DATABASE taskflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE taskflow_db;

-- Run the create_database.sql contents
-- (Copy and paste the contents of create_database.sql in phpMyAdmin SQL tab)
```

## Database Schema

### Tables

#### `projects`
Stores project information
- `id` - Primary key
- `name` - Project name (required)
- `description` - Project description
- `color` - Project color for UI
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `tasks`
Stores task information
- `id` - Primary key
- `title` - Task title (required)
- `description` - Task description
- `due_date` - Due date
- `priority` - Priority level (low, medium, high)
- `status` - Task status (pending, in_progress, completed)
- `project_id` - Foreign key to projects table
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `users`
Stores user information (for future authentication)
- `id` - Primary key
- `name` - User name
- `email` - User email (unique)
- `password` - User password (hashed)
- `profile_picture` - Profile picture URL
- `bio` - User biography
- `preferences` - User preferences (JSON)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `task_tags`
Stores tags for tasks (many-to-many relationship)
- `id` - Primary key
- `task_id` - Foreign key to tasks
- `tag_name` - Tag name
- `created_at` - Creation timestamp

#### `notifications`
Stores user notifications
- `id` - Primary key
- `user_id` - Foreign key to users
- `title` - Notification title
- `message` - Notification message
- `type` - Notification type (info, success, warning, error)
- `is_read` - Read status
- `created_at` - Creation timestamp

### Views

#### `task_details`
Enhanced task view with project information and urgency status

#### `project_stats`
Project statistics including task counts and completion percentages

### Stored Procedures

#### `GetDashboardStats()`
Returns overall dashboard statistics

#### `GetUpcomingTasks(days_ahead)`
Returns tasks due within the specified number of days

### Functions

#### `ProjectCompletionPercentage(project_id)`
Calculates completion percentage for a specific project

## Sample Data

The setup script includes sample data for demonstration:
- 5 sample projects
- 10 sample tasks
- Sample tags for tasks

## Configuration

### PHP Connection
Update the database connection settings in `php/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'taskflow_db');
```

### Security Considerations
- Change default MySQL password
- Create dedicated database user with limited privileges
- Use prepared statements (already implemented in PHP files)
- Validate and sanitize all inputs

## Backup and Maintenance

### Backup Database
```bash
mysqldump -u root -p taskflow_db > backup.sql
```

### Restore Database
```bash
mysql -u root -p taskflow_db < backup.sql
```

### Maintenance Queries
```sql
-- Clean up old notifications (older than 30 days)
DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Update task statistics
ANALYZE TABLE tasks, projects;

-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'taskflow_db'
ORDER BY (data_length + index_length) DESC;
```

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Verify MySQL service is running
   - Check database credentials in `php/db.php`
   - Ensure database name is correct

2. **Permission Denied**
   - Check MySQL user permissions
   - Grant necessary privileges to the database user

3. **Character Encoding Issues**
   - Ensure database uses utf8mb4 charset
   - Check connection charset in PHP

### Error Codes

- **1045**: Access denied for user
- **1049**: Unknown database
- **2002**: Cannot connect to MySQL server

## Performance Optimization

### Indexes
The schema includes optimized indexes for:
- Foreign keys
- Frequently queried columns
- Date ranges
- Status and priority fields

### Query Optimization
- Use views for complex queries
- Implement pagination for large datasets
- Consider caching for frequently accessed data

## Future Enhancements

### Planned Features
- User authentication and authorization
- Task dependencies
- File attachments
- Comments and collaboration
- Time tracking
- Advanced reporting

### Schema Extensions
- Add `user_id` to tasks for personal task assignment
- Implement soft deletes with `deleted_at` columns
- Add task templates
- Implement project sharing and collaboration

## Support

For database-related issues:
1. Check XAMPP error logs
2. Verify MySQL service status
3. Test connection with simple PHP script
4. Review SQL syntax in setup script

## Version History

- **v1.0** - Initial setup with basic CRUD operations
- **v1.1** - Added tags, notifications, and advanced features
- **v1.2** - Performance optimizations and indexes

