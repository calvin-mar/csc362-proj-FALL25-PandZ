
# P&Z Database Management System

## Installation Instructions

### 1. Database Setup

First, run the main schema file:
```bash
mysql -u your_username -p your_database < pandz_schema_mariadb_final.sql
```

Then run the stored procedures:
```bash
mysql -u your_username -p your_database < form_insert_procedures.sql
```

Then run the views:
```bash
mysql -u your_username -p your_database < database_views.sql
```

Finally, add authentication fields:
```bash
mysql -u your_username -p your_database < auth_schema.sql
```

### 2. Configure Database Connection

Edit `config.php` and update these constants:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'pandz_database');
```

### 3. Generate Password Hashes

Run the password generator:
```bash
php setup_passwords.php
```

Or use this PHP snippet to generate a hash:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

### 4. Create Users

Insert users into the database with hashed passwords:

```sql
-- Create a client
INSERT INTO clients (client_username, client_password) 
VALUES ('johndoe', 'PASTE_HASH_HERE');

-- Create a department
INSERT INTO departments (department_name, department_password) 
VALUES ('Planning Department', 'PASTE_HASH_HERE');

-- Create a government worker
INSERT INTO govt_workers (worker_username, worker_password, worker_name, worker_email) 
VALUES ('admin', 'PASTE_HASH_HERE', 'System Administrator', 'admin@example.com');
```

### 5. File Structure

Place all PHP files in your web server directory:
```
/var/www/html/pandz/
├── config.php
├── login.php
├── logout.php
├── client_dashboard.php
├── client_new_form.php
├── client_view_form.php
├── department_dashboard.php
├── govt_worker_dashboard.php
├── govt_worker_reports.php
├── govt_worker_view_form.php
└── setup_passwords.php
```

### 6. Web Server Configuration

Ensure PHP is enabled and has the PDO MySQL extension:
```bash
# For Ubuntu/Debian
sudo apt-get install php-mysql php-pdo

# For CentOS/RHEL
sudo yum install php-mysqlnd
```

### 7. Access the Application

Navigate to: `http://your-server/pandz/login.php`

## Default Login Credentials

If you used the sample data from auth_schema.sql:

**Client:**
- Username: client1
- Password: password123

**Department:**
- Username: Planning
- Password: password123

**Government Worker:**
- Username: admin
- Password: password123

⚠️ **IMPORTANT:** Change these default passwords immediately in production!

## Features

### Client Portal
- Submit new forms
- View submitted forms
- Track payment status
- View department interactions

### Department Portal
- View all forms
- Add interactions/comments to forms
- Track form status

### Government Worker Portal
- View all forms with advanced filtering
- Generate reports
- View comprehensive form details
- Access statistics dashboard

## Security Notes

1. Always use HTTPS in production
2. Change default passwords immediately
3. Use strong passwords (minimum 12 characters)
4. Regularly backup your database
5. Keep PHP and MySQL updated
6. Set appropriate file permissions (644 for PHP files)
7. Disable directory listing in Apache/Nginx

## Troubleshooting

### "Connection failed" error
- Check database credentials in config.php
- Verify MySQL is running
- Confirm database exists

### "Call to undefined function password_hash()"
- Upgrade to PHP 5.5 or higher

### Session errors
- Ensure session.save_path is writable
- Check PHP session configuration

### Stored procedure errors
- Verify procedures were created successfully
- Check procedure delimiter settings

## Support

For issues or questions, please contact your system administrator.

---

**Version:** 1.0
**Last Updated:** November 2024