import unittest
import mariadb
import subprocess
import configparser
from datetime import date
import sys
import os

class TestDatabaseSchema(unittest.TestCase):
    TEST_DB_NAME = "planning_zoning_test"
    TEST_MAIN_FILE = "database_project_main_testing.sql"
    """Unit tests for the Planning and Zoning database schema using MariaDB"""
    @classmethod
    def getDefaultPassword(cls):
        #return "roundtable"
        return cls.config["default"]["mysqli.default_pw"]
    
    @classmethod
    def runMariaDBTerminalCommandAsDefaultUser(cls, command: list):
        DB_EXEC_COMMAND = ["mariadb", f"-p{cls.getDefaultPassword()}", "-e"]
        return subprocess.run(DB_EXEC_COMMAND + command, check=True)
    
    @classmethod
    def setUpClass(cls):
        """Set up database connection and configuration"""
        # Read configuration
        cls.config = configparser.ConfigParser()
        
        cls.config.read("/home/calvinmar/mysqli.ini")
        
        cls.runMariaDBTerminalCommandAsDefaultUser([f"SOURCE {cls.TEST_MAIN_FILE };"])
       
        cls.connection = mariadb.connect(
           password=cls.getDefaultPassword(),
           host="localhost",
           database=cls.TEST_DB_NAME
        )
       
        cls.cursor = cls.connection.cursor()
       
        return super().setUpClass()
    
    
    @classmethod
    def tearDownClass(cls):
        """Close database connection after all tests"""
        if cls.connection:
            cls.cursor.close()
            cls.connection.close()
            print("MariaDB connection closed")
    
    def setUp(self):
        """Start transaction before each test"""
        self.connection.autocommit = False
    
    def tearDown(self):
        """Rollback changes after each test"""
        self.connection.rollback()
    
    # ==================== SUBPROCESS UTILITY METHODS ====================
    
    def execute_sql_file(self, sql_file):
        """Execute SQL file using subprocess"""
        try:
            cmd = [
                'mysql',
                f'--host={self.db_host}',
                f'--port={self.db_port}',
                f'--user={self.db_user}',
                f'--database={self.db_name}'
            ]
            
            if self.db_password:
                cmd.append(f'--password={self.db_password}')
            
            with open(sql_file, 'r') as f:
                result = subprocess.run(
                    cmd,
                    stdin=f,
                    capture_output=True,
                    text=True,
                    timeout=30
                )
            
            return result.returncode == 0, result.stderr
        except Exception as e:
            return False, str(e)
    
    def execute_sql_command(self, sql_command):
        """Execute single SQL command using subprocess"""
        try:
            cmd = [
                'mysql',
                f'--host={self.db_host}',
                f'--port={self.db_port}',
                f'--user={self.db_user}',
                f'--database={self.db_name}',
                '-e',
                sql_command
            ]
            
            if self.db_password:
                cmd.append(f'--password={self.db_password}')
            
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                timeout=10
            )
            
            return result.returncode == 0, result.stdout, result.stderr
        except Exception as e:
            return False, '', str(e)
    
    def get_table_count(self):
        """Get count of tables using subprocess"""
        tables_query = "SHOW TABLES;"
        self.cursor.execute(tables_query)
        select_query = "SELECT FOUND_ROWS();"
        self.cursor.execute(select_query)
        (num_tables,) = self.cursor.fetchone()
        return num_tables
    
    # ==================== TABLE EXISTENCE TESTS ====================
    
    def test_all_tables_exist(self):
        """Test that all required tables exist in the database"""
        expected_tables = [
            'form_types', 'forms', 'surveyors', 'engineers', 'contractors',
            'architects', 'land_architects', 'attorneys', 'properties',
            'zoning_verification_letter', 'apof_neighbors', 'adjacent_neighbors',
            'adjacent_property_owner_forms', 'type_one_applicants',
            'applicants_link_forms', 'major_subdivision_plat_applications',
            'minor_subdivision_plat_applications', 'technical_forms',
            'LDS_plans', 'structures', 'WSF_verifications', 'project_types',
            'zoning_permit_applications', 'zoning_map_amendment_applications',
            'gdpa_required_findings', 'general_development_plan_applications',
            'variance_applications', 'future_land_use_map_applications',
            'conditional_use_permit_applications', 'hearing_forms',
            'type_one_forms', 'orr_applicants', 'public_records',
            'orr_public_record_names', 'open_record_requests',
            'aar_property_owners', 'administrative_property_owners',
            'aar_appellants', 'administrative_appellants',
            'administrative_appeal_requests', 'sign_permit_applications',
            'departments', 'department_form_interactions', 'clients',
            'incomplete_client_forms', 'client_forms',
            'site_development_plan_applications', 'type_one_execs',
            'type_one_applicant_execs', 'type_one_owners',
            'adjacent_property_owners', 'adjacent_neighbor_owners',
            'signs', 'zva_property_owners', 'zva_applicants',
            'sp_property_owners', 'sp_businesses', 'permits_link_signs',
            'sp_contractors'
        ]
        
        self.cursor.execute("SHOW TABLES")
        actual_tables = [table[0] for table in self.cursor.fetchall()]
        
        for table in expected_tables:
            self.assertIn(table, actual_tables, f"Table {table} does not exist")
    
    def test_table_count(self):
        """Test total number of tables using subprocess"""
        count = self.get_table_count()
        self.assertGreaterEqual(count, 59, "Expected at least 59 tables")
    
    # ==================== FORM TYPES TESTS ====================
    
    def test_insert_form_type(self):
        """Test inserting a form type"""
        self.cursor.execute(
            "INSERT INTO form_types (form_type) VALUES (?)",
            ("Test Form Type",)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_form_type_duplicate_prevention(self):
        """Test that duplicate form types are prevented"""
        self.cursor.execute(
            "INSERT INTO form_types (form_type) VALUES (?)",
            ("Unique Form",)
        )
        
        with self.assertRaises(mariadb.Error):
            self.cursor.execute(
                "INSERT INTO form_types (form_type) VALUES (?)",
                ("Unique Form",)
            )
    
    # ==================== FORMS TESTS ====================
    
    def test_insert_form_with_valid_type(self):
        """Test inserting a form with a valid form type"""
        self.cursor.execute(
            "INSERT INTO form_types (form_type) VALUES (?)",
            ("Valid Type",)
        )
        self.cursor.execute(
            "INSERT INTO forms (form_type) VALUES (?)",
            ("Valid Type",)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_form_cascade_delete_on_form_type(self):
        """Test that forms are deleted when form type is deleted"""
        self.cursor.execute(
            "INSERT INTO form_types (form_type) VALUES (?)",
            ("Delete Type",)
        )
        self.cursor.execute(
            "INSERT INTO forms (form_type) VALUES (?)",
            ("Delete Type",)
        )
        
        self.cursor.execute("DELETE FROM form_types WHERE form_type = ?", ("Delete Type",))
        
        self.cursor.execute("SELECT COUNT(*) FROM forms WHERE form_type = ?", ("Delete Type",))
        count = self.cursor.fetchone()[0]
        self.assertEqual(count, 0)
    
    def test_form_auto_increment(self):
        """Test that form_id auto-increments properly"""
        self.cursor.execute(
            "INSERT INTO form_types (form_type) VALUES (?)",
            ("Auto Inc Test",)
        )
        self.cursor.execute(
            "INSERT INTO forms (form_type) VALUES (?)",
            ("Auto Inc Test",)
        )
        first_id = self.cursor.lastrowid
        
        self.cursor.execute(
            "INSERT INTO forms (form_type) VALUES (?)",
            ("Auto Inc Test",)
        )
        second_id = self.cursor.lastrowid
        
        self.assertEqual(second_id, first_id + 1)
    
    # ==================== SURVEYOR TESTS ====================
    
    def test_insert_surveyor(self):
        """Test inserting a surveyor"""
        self.cursor.execute(
            """INSERT INTO surveyors (surveyor_first_name, surveyor_last_name, 
               surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
               VALUES (?, ?, ?, ?, ?, ?)""",
            ("John", "Doe", "Survey Corp", "john@example.com", "555-1234", "555-5678")
        )
        self.assertEqual(self.cursor.rowcount, 1)
        self.assertIsNotNone(self.cursor.lastrowid)
    
    def test_surveyor_utf8_support(self):
        """Test UTF-8 character support in surveyor fields"""
        self.cursor.execute(
            "INSERT INTO surveyors (surveyor_first_name, surveyor_last_name) VALUES (?, ?)",
            ("José", "Müller")
        )
        
        self.cursor.execute(
            "SELECT surveyor_first_name, surveyor_last_name FROM surveyors WHERE surveyor_id = ?",
            (self.cursor.lastrowid,)
        )
        result = self.cursor.fetchone()
        self.assertEqual(result[0], "José")
        self.assertEqual(result[1], "Müller")
    
    # ==================== PROPERTY TESTS ====================
    
    def test_insert_property(self):
        """Test inserting a property"""
        self.cursor.execute(
            """INSERT INTO properties (PVA_parcel_number, property_street_address,
               property_city, property_state, property_zip_code, property_acreage,
               property_current_zoning)
               VALUES (?, ?, ?, ?, ?, ?, ?)""",
            (123456, "123 Main St", "Danville", "KY", "40422", "5.0", "R-1")
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_property_duplicate_parcel_prevention(self):
        """Test that duplicate PVA parcel numbers are prevented"""
        self.cursor.execute(
            """INSERT INTO properties (PVA_parcel_number, property_street_address)
               VALUES (?, ?)""",
            (999999, "456 Oak St")
        )
        
        with self.assertRaises(mariadb.Error):
            self.cursor.execute(
                """INSERT INTO properties (PVA_parcel_number, property_street_address)
                   VALUES (?, ?)""",
                (999999, "789 Pine St")
            )
    
    # ==================== MAJOR SUBDIVISION APPLICATION TESTS ====================
    
    def test_insert_major_subdivision_application(self):
        """Test inserting a major subdivision plat application"""
        # Insert prerequisites
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Test Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Test Type",))
        form_id = self.cursor.lastrowid
        
        self.cursor.execute(
            "INSERT INTO surveyors (surveyor_first_name, surveyor_last_name) VALUES (?, ?)",
            ("Jane", "Smith")
        )
        surveyor_id = self.cursor.lastrowid
        
        self.cursor.execute(
            "INSERT INTO engineers (engineer_first_name, engineer_last_name) VALUES (?, ?)",
            ("Bob", "Engineer")
        )
        engineer_id = self.cursor.lastrowid
        
        self.cursor.execute(
            "INSERT INTO properties (PVA_parcel_number) VALUES (?)",
            (111111,)
        )
        
        self.cursor.execute(
            """INSERT INTO major_subdivision_plat_applications 
               (form_id, surveyor_id, engineer_id, PVA_parcel_number)
               VALUES (?, ?, ?, ?)""",
            (form_id, surveyor_id, engineer_id, 111111)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_major_subdivision_foreign_key_constraint(self):
        """Test that foreign key constraints work for major subdivision applications"""
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("FK Test",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("FK Test",))
        form_id = self.cursor.lastrowid
        
        with self.assertRaises(mariadb.IntegrityError):
            self.cursor.execute(
                """INSERT INTO major_subdivision_plat_applications 
                   (form_id, surveyor_id) VALUES (?, ?)""",
                (form_id, 99999)  # Non-existent surveyor
            )
    
    # ==================== APPLICANT TESTS ====================
    
    def test_insert_type_one_applicant(self):
        """Test inserting a type one applicant"""
        self.cursor.execute(
            """INSERT INTO type_one_applicants 
               (t1_applicant_first_name, t1_applicant_last_name, t1_applicant_email)
               VALUES (?, ?, ?)""",
            ("Alice", "Johnson", "alice@example.com")
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_applicant_form_link(self):
        """Test linking applicants to forms"""
        self.cursor.execute(
            "INSERT INTO type_one_applicants (t1_applicant_first_name) VALUES (?)",
            ("Test",)
        )
        applicant_id = self.cursor.lastrowid
        
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Link Test",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Link Test",))
        form_id = self.cursor.lastrowid
        
        self.cursor.execute(
            """INSERT INTO applicants_link_forms (t1_applicant_id, form_id)
               VALUES (?, ?)""",
            (applicant_id, form_id)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    # ==================== TECHNICAL FORMS TESTS ====================
    
    def test_insert_technical_form_with_dates(self):
        """Test inserting technical form with various dates"""
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Tech Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Tech Type",))
        form_id = self.cursor.lastrowid
        
        self.cursor.execute(
            """INSERT INTO technical_forms 
               (form_id, technical_app_filing_date, technical_review_date)
               VALUES (?, ?, ?)""",
            (form_id, date(2024, 1, 15), date(2024, 2, 1))
        )
        self.assertEqual(self.cursor.rowcount, 1)
        
        # Verify dates are stored correctly
        self.cursor.execute(
            "SELECT technical_app_filing_date, technical_review_date FROM technical_forms WHERE form_id = ?",
            (form_id,)
        )
        result = self.cursor.fetchone()
        self.assertEqual(result[0], date(2024, 1, 15))
        self.assertEqual(result[1], date(2024, 2, 1))
    
    # ==================== ZONING PERMIT TESTS ====================
    
    def test_insert_zoning_permit_application(self):
        """Test inserting a zoning permit application"""
        # Setup
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Zoning Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Zoning Type",))
        form_id = self.cursor.lastrowid
        
        self.cursor.execute("INSERT INTO properties (PVA_parcel_number) VALUES (?)", (222222,))
        self.cursor.execute("INSERT INTO project_types (project_type) VALUES (?)", ("Test Project",))
        
        self.cursor.execute(
            """INSERT INTO zoning_permit_applications 
               (form_id, PVA_parcel_number, project_type)
               VALUES (?, ?, ?)""",
            (form_id, 222222, "Test Project")
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    # ==================== HEARING FORMS TESTS ====================
    
    def test_insert_hearing_form(self):
        """Test inserting a hearing form"""
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Hearing Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Hearing Type",))
        form_id = self.cursor.lastrowid
        
        self.cursor.execute(
            """INSERT INTO hearing_forms 
               (form_id, hearing_docket_number, hearing_date)
               VALUES (?, ?, ?)""",
            (form_id, "2024-001", date(2024, 3, 15))
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    # ==================== COMPLEX RELATIONSHIP TESTS ====================
    
    def test_adjacent_neighbors_relationship(self):
        """Test the many-to-many relationship between forms and neighbors"""
        # Create form
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Neighbor Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Neighbor Type",))
        form_id = self.cursor.lastrowid
        
        # Create neighbor
        self.cursor.execute(
            """INSERT INTO apof_neighbors (PVA_map_code, apof_neighbor_property_location)
               VALUES (?, ?)""",
            ("MAP123", "456 Adjacent St")
        )
        neighbor_id = self.cursor.lastrowid
        
        # Link them
        self.cursor.execute(
            "INSERT INTO adjacent_neighbors (form_id, neighbor_id) VALUES (?, ?)",
            (form_id, neighbor_id)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    def test_cascade_delete_preserves_data_integrity(self):
        """Test that cascade deletes maintain referential integrity"""
        # Create form
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Cascade Test",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Cascade Test",))
        form_id = self.cursor.lastrowid
        
        # Create related records
        self.cursor.execute(
            "INSERT INTO structures (form_id, structure_type) VALUES (?, ?)",
            (form_id, "Building")
        )
        
        # Delete form
        self.cursor.execute("DELETE FROM forms WHERE form_id = ?", (form_id,))
        
        # Verify structures were cascade deleted
        self.cursor.execute("SELECT COUNT(*) FROM structures WHERE form_id = ?", (form_id,))
        count = self.cursor.fetchone()[0]
        self.assertEqual(count, 0)
    
    # ==================== DATA VALIDATION TESTS ====================
    
    def test_preloaded_form_types(self):
        """Test that form types are properly preloaded"""
        expected_types = [
            "Administrative Appeal Request",
            "Adjacent Property Owners Form",
            "Conditional Use Permit Application",
            "Zoning Permit Application"
        ]
        
        self.cursor.execute("SELECT form_type FROM form_types")
        existing_types = [row[0] for row in self.cursor.fetchall()]
        
        for form_type in expected_types:
            self.assertIn(form_type, existing_types)
    
    def test_preloaded_project_types(self):
        """Test that project types are properly preloaded"""
        expected_projects = [
            "Multi-Family Use",
            "Commercial Use",
            "Industrial Use"
        ]
        
        self.cursor.execute("SELECT project_type FROM project_types")
        existing_projects = [row[0] for row in self.cursor.fetchall()]
        
        for project in expected_projects:
            self.assertIn(project, existing_projects)
    
    # ==================== SIGN PERMIT TESTS ====================
    
    def test_insert_sign_and_permit_relationship(self):
        """Test inserting signs and linking to permits"""
        # Create sign owner
        self.cursor.execute(
            "INSERT INTO sp_property_owners (sp_owner_first_name) VALUES (?)",
            ("Sign Owner",)
        )
        owner_id = self.cursor.lastrowid
        
        # Create sign
        self.cursor.execute(
            """INSERT INTO signs (sp_owner_id, sign_type, sign_square_footage)
               VALUES (?, ?, ?)""",
            (owner_id, "Billboard", 50.5)
        )
        sign_id = self.cursor.lastrowid
        
        # Create form
        self.cursor.execute("INSERT INTO form_types (form_type) VALUES (?)", ("Sign Type",))
        self.cursor.execute("INSERT INTO forms (form_type) VALUES (?)", ("Sign Type",))
        form_id = self.cursor.lastrowid
        
        # Link sign to permit
        self.cursor.execute(
            "INSERT INTO permits_link_signs (form_id, sign_id) VALUES (?, ?)",
            (form_id, sign_id)
        )
        self.assertEqual(self.cursor.rowcount, 1)
    
    # ==================== DECIMAL FIELD TESTS ====================
    
    def test_decimal_fields_precision(self):
        """Test DECIMAL field precision in apof_neighbors"""
        self.cursor.execute(
            """INSERT INTO apof_neighbors (PVA_map_code, apof_neighbor_property_street, 
               apof_neighbor_property_deed_book) VALUES (?, ?, ?)""",
            ("MAP456", 123.45, 9876.54)
        )
        
        self.cursor.execute(
            """SELECT apof_neighbor_property_street, apof_neighbor_property_deed_book 
               FROM apof_neighbors WHERE neighbor_id = ?""",
            (self.cursor.lastrowid,)
        )
        result = self.cursor.fetchone()
        self.assertEqual(float(result[0]), 123.45)
        self.assertEqual(float(result[1]), 9876.54)
    
    # ==================== ENGINE AND CHARSET TESTS ====================
    
    def test_table_engine_innodb(self):
        """Test that tables use InnoDB engine"""
        self.cursor.execute(
            """SELECT ENGINE FROM information_schema.tables 
               WHERE table_schema = DATABASE() AND table_name = 'forms'"""
        )
        result = self.cursor.fetchone()
        self.assertEqual(result[0], 'InnoDB')
    
    def test_table_charset_utf8mb4(self):
        """Test that tables use utf8mb4 charset"""
        self.cursor.execute(
            """SELECT TABLE_COLLATION FROM information_schema.tables 
               WHERE table_schema = DATABASE() AND table_name = 'forms'"""
        )
        result = self.cursor.fetchone()
        self.assertTrue(result[0].startswith('utf8mb4'))


if __name__ == '__main__':
    # Run tests with verbose output
    print("=" * 70)
    print("MariaDB Database Unit Tests")
    print("=" * 70)
    unittest.main(verbosity=2)