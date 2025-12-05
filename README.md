# planning-zoning-database
Password: "What does a leprechaun have to follow to find his pot of gold?"

# CSC362 Database Web Application Project – PandZ System

This project is a PHP-based web application backed by a MariaDB database created for the Danville Planning and Zoning office. It provides tools for managing form-related data using stored procedures, views, and backend validation. The system also includes automated tests, an ER diagram, and SQL scripts for full database setup. 

The system is built using:
- PHP
- MariaDB 
- Custom SQL procedures, triggers, and views
- PHPUnit & Python unit tests for SQL

# Features
- Full database schema with sample inserts  
- Stored procedures for inserting and updating form data  
- Duplicate detection via SQL script  
- Dynamic PHP pages for displaying forms and creating new forms 
- Testing suite for PHP & Python tests for SQL   
- ER diagram for database visualization  
- Organized field spec form templates
- Database views for viewing forms and reports

# File Structure
CSC362-PROJ-FALL25-PANDZ/
│
├── field_spec_forms/ # Field specification forms
├── html/ # PHP pages for web application
├── php_testing/ # PHPUnit tests
├── uploads/ # User-uploaded files
├── vendor/ # Composer dependencies
│
├── auth_schema.sql # Authentication schema
├── database_project_main.sql # Full production DB schema
├── database_project_main_testing.sql # Schema used for testing
├── database_sample_insert.sql # Populate DB with sample data
├── database_views.sql # Views for formatted database access
├── database_views_first.sql # Initial view definitions
├── form_insert_procedures.sql # Procedures for inserting data
├── form_update_procedures.sql # Procedures for updating records
├── find_duplicates.sql # Script for detecting duplicate entries
│
├── pandz_schema_mariadb_final.sql # Final combined schema
├── PandZ_ER.jpg # ER diagram of the database
│
├── test_database_project_main_test.py # Python tests
├── test_zoning_permit_display.php # PHP test for displaying zoning permit data
│
├── composer.json
├── composer.lock
├── TeamContract.txt
└── README.md

# Installation
This database and web application were developed using VS Code and a Google virtual machine.

VS Code Extensions
- Dev Containers
- GitHub Codespaces 
- Remote Development # For remoting into the VM where the files are stored. 

You will need to clone the repository using git clone to get all of the files and the working file structure. Once you have all of those files you can then set up the database through MariaDB.

After accessing MariaDB you will then need to SOURCE the database_project_main.sql file to set up the entire database. 

To access the database through the PHP Application you will need to create a symbolic link for your system or virtual machine you are using to host the website. 

# Important steps for database interaction permissions

1. Log in as root
sudo mariadb

2. Grant your own user full privileges (WITH GRANT OPTION)

Replace the username and password with your own.

GRANT ALL PRIVILEGES ON *.* TO 'username'@'localhost'
IDENTIFIED BY 'password' WITH GRANT OPTION;

Then exit:

exit;

3. Log in as your user
mariadb -u username -p

4. Create the dedicated PHP database account
CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'rainbows';

5. Grant minimal CRUD permissions
GRANT INSERT ON *.* TO 'webuser'@'localhost';
GRANT SELECT  ON *.* TO 'webuser'@'localhost';
GRANT UPDATE ON *.* TO 'webuser'@'localhost';
GRANT DELETE ON *.* TO 'webuser'@'localhost';

6. Create a mysqli configuration file

Create ~/mysql.ini at the top level of your system or VM:

mysqli.default_host = 'localhost'
mysqli.default_user = 'webuser'
mysqli.default_pw = 'rainbows'

# Final Thoughts

The PandZ System was designed to support the operational needs of the Danville Planning and Zoning Office.
The office manages a wide range of forms and previously lacked an online system capable of storing, validating, and retrieving important information efficiently.

# This database and web application provide:
- A location for storing form and permit data
- Automated validation through stored procedures
- Data entry through stored procedures that interact with the web application
- Organized views for easier reporting and reviewing of forms
- Duplicate detection to ensure no duplicate entries
- A maintainable architecture for future growth
- Client side for submitting new forms
- Department side for adding department interactions to forms
- Government worker side for adding form corrections and adding new departments 
- User portal for logging in as a client, department, or government worker