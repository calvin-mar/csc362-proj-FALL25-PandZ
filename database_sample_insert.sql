SET FOREIGN_KEY_CHECKS = 0;

-- Sample Inserts for addresses
INSERT INTO addresses (address_street, address_city, state_code, address_zip_code) VALUES
('123 Main St', 'Lexington', 'KY', '40507'),
('456 Oak Ave', 'Frankfort', 'KY', '40601'),
('789 Pine Ln', 'Louisville', 'KY', '40203'),
('101 Maple Dr', 'Cincinnati', 'OH', '45202'),
('202 Elm St', 'Indianapolis', 'IN', '46204'),
('303 Cedar Rd', 'Lexington', 'KY', '40507'),
('404 Birch Ct', 'Lexington', 'KY', '40507'),
('505 Spruce Way', 'Lexington', 'KY', '40507'),
('606 Willow Pk', 'Lexington', 'KY', '40507'),
('707 Poplar Blvd', 'Lexington', 'KY', '40507'),
('808 Aspen Cv', 'Lexington', 'KY', '40507'),
('909 Holly Trl', 'Lexington', 'KY', '40507'),
('111 Dogwood Pl', 'Lexington', 'KY', '40507'),
('222 Magnolia Rd', 'Lexington', 'KY', '40507'),
('333 Sycamore St', 'Lexington', 'KY', '40507'),
('444 Redwood Ave', 'Lexington', 'KY', '40507'),
('555 Juniper Dr', 'Lexington', 'KY', '40507'),
('666 Cypress Ln', 'Lexington', 'KY', '40507'),
('777 Pecan Ct', 'Lexington', 'KY', '40507'),
('888 Walnut Way', 'Lexington', 'KY', '40507'),
('999 Chestnut Pk', 'Lexington', 'KY', '40507'),
('121 Peach Blvd', 'Lexington', 'KY', '40507'),
('131 Plum St', 'Lexington', 'KY', '40507'),
('141 Cherry Ln', 'Lexington', 'KY', '40507'),
('151 Apple Way', 'Lexington', 'KY', '40507'),
('161 Orange Cres', 'Lexington', 'KY', '40507'),
('171 Grape Ave', 'Lexington', 'KY', '40507'),
('181 Lemon St', 'Lexington', 'KY', '40507'),
('191 Lime Blvd', 'Lexington', 'KY', '40507'),
('201 Pear Dr', 'Lexington', 'KY', '40507'),
('211 Fig Rd', 'Lexington', 'KY', '40507'),
('234 Market St', 'Lexington', 'KY', '40507'),
('567 Broadway', 'Lexington', 'KY', '40507'),
('890 High St', 'Lexington', 'KY', '40507'),
('112 Low Rd', 'Lexington', 'KY', '40507'),
('345 West Ave', 'Lexington', 'KY', '40507'),
('678 East Blvd', 'Lexington', 'KY', '40507'),
('901 North Way', 'Lexington', 'KY', '40507'),
('123 South Ln', 'Lexington', 'KY', '40507'),
('456 Central St', 'Lexington', 'KY', '40507'),
('789 Summit Dr', 'Lexington', 'KY', '40507'),
('101 Valley Rd', 'Lexington', 'KY', '40507'),
('202 Ridge Ave', 'Lexington', 'KY', '40507'),
('303 Lakefront', 'Lexington', 'KY', '40507'),
('404 Riverwalk', 'Lexington', 'KY', '40507');

-- Sample Inserts for correction_forms (minimum 6, more for linking)
INSERT INTO correction_forms () VALUES
(), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), (), ();


-- Sample Inserts for clients
INSERT INTO clients (client_username, client_password, client_type) VALUES
('john_doe', 'pass123', 'individual'),
('jane_smith', 'securepwd', 'individual'),
('abc_corp', 'corp_pass', 'business'),
('xyz_dev', 'dev_pass', 'business'),
('city_planning', 'plan_pass', 'government'),
('county_clerk', 'clerk_pass', 'government'),
('builder_group', 'build_pass', 'business'),
('designer_firm', 'design_pass', 'business'),
('legal_eagles', 'law_pass', 'individual'),
('survey_co', 'survey_pass', 'business');

-- Sample Inserts for surveyors
INSERT INTO surveyors (surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell) VALUES
('Surveyor', 'One', 'GeoSurveys', 'surveyor1@geosurveys.com', '555-1001', '555-1002'),
('Surveyor', 'Two', 'LandMarks', 'surveyor2@landmarks.com', '555-1003', '555-1004'),
('Surveyor', 'Three', 'Precision Maps', 'surveyor3@precision.com', '555-1005', '555-1006'),
('Surveyor', 'Four', 'Apex Surveying', 'surveyor4@apex.com', '555-1007', '555-1008'),
('Surveyor', 'Five', 'Horizon Surveys', 'surveyor5@horizon.com', '555-1009', '555-1010'),
('Surveyor', 'Six', 'Global Mappers', 'surveyor6@global.com', '555-1011', '555-1012');

-- Sample Inserts for engineers
INSERT INTO engineers (engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell) VALUES
('Engineer', 'One', 'Structura Eng', 'eng1@structura.com', '555-2001', '555-2002'),
('Engineer', 'Two', 'City Builders', 'eng2@citybuilders.com', '555-2003', '555-2004'),
('Engineer', 'Three', 'Innovate Designs', 'eng3@innovate.com', '555-2005', '555-2006'),
('Engineer', 'Four', 'Bridge Works', 'eng4@bridge.com', '555-2007', '555-2008'),
('Engineer', 'Five', 'Power Solutions', 'eng5@power.com', '555-2009', '555-2010'),
('Engineer', 'Six', 'Eco Engineering', 'eng6@ecoeng.com', '555-2011', '555-2012');

-- Sample Inserts for contractors
INSERT INTO contractors (contractor_first_name, contractor_last_name, contractor_law_firm, contractor_email, contractor_phone, contractor_cell) VALUES
('Contractor', 'One', 'BuildRight', 'cont1@buildright.com', '555-3001', '555-3002'),
('Contractor', 'Two', 'Prime Builders', 'cont2@primebuilders.com', '555-3003', '555-3004'),
('Contractor', 'Three', 'Quality Builds', 'cont3@quality.com', '555-3005', '555-3006'),
('Contractor', 'Four', 'Elite Contractors', 'cont4@elite.com', '555-3007', '555-3008'),
('Contractor', 'Five', 'Green Builds', 'cont5@green.com', '555-3009', '555-3010'),
('Contractor', 'Six', 'Urban Renovations', 'cont6@urban.com', '555-3011', '555-3012');

-- Sample Inserts for architects
INSERT INTO architects (architect_first_name, architect_last_name, architect_law_firm, architect_email, architect_phone, architect_cell) VALUES
('Architect', 'One', 'Design House', 'arch1@designhouse.com', '555-4001', '555-4002'),
('Architect', 'Two', 'Modern Spaces', 'arch2@modernspaces.com', '555-4003', '555-4004'),
('Architect', 'Three', 'Urban Vision', 'arch3@urbanvision.com', '555-4005', '555-4006'),
('Architect', 'Four', 'Apex Architecture', 'arch4@apexarch.com', '555-4007', '555-4008'),
('Architect', 'Five', 'Eco Designs', 'arch5@ecodesigns.com', '555-4009', '555-4010'),
('Architect', 'Six', 'Heritage Homes', 'arch6@heritage.com', '555-4011', '555-4012');

-- Sample Inserts for land_architects
INSERT INTO land_architects (land_architect_first_name, land_architect_last_name, land_architect_law_firm, land_architect_email, land_architect_phone, land_architect_cell) VALUES
('LandArch', 'One', 'GreenScape', 'la1@greenscape.com', '555-5001', '555-5002'),
('LandArch', 'Two', 'Natural Designs', 'la2@naturaldesigns.com', '555-5003', '555-5004'),
('LandArch', 'Three', 'Park Planners', 'la3@parkplanners.com', '555-5005', '555-5006'),
('LandArch', 'Four', 'Urban Greens', 'la4@urbangreens.com', '555-5007', '555-5008'),
('LandArch', 'Five', 'Wild Landscapes', 'la5@wildlandscapes.com', '555-5009', '555-5010'),
('LandArch', 'Six', 'Garden Creators', 'la6@garden.com', '555-5011', '555-5012');

-- Sample Inserts for attorneys
INSERT INTO attorneys (attorney_first_name, attorney_last_name, attorney_law_firm, attorney_email, attorney_phone, attorney_cell) VALUES
('Attorney', 'One', 'Legal Associates', 'att1@legal.com', '555-6001', '555-6002'),
('Attorney', 'Two', 'Justice Firm', 'att2@justice.com', '555-6003', '555-6004'),
('Attorney', 'Three', 'Law and Order', 'att3@lawo.com', '555-6005', '555-6006'),
('Attorney', 'Four', 'Barrister Group', 'att4@barrister.com', '555-6007', '555-6008'),
('Attorney', 'Five', 'Counselors At Law', 'att5@counsel.com', '555-6009', '555-6010'),
('Attorney', 'Six', 'The Legal Team', 'att6@legalteam.com', '555-6011', '555-6012');

-- Sample Inserts for properties
INSERT INTO properties (PVA_parcel_number, address_id, property_acreage, property_current_zoning) VALUES
(1001, 1, '1.5', 'R-1'),
(1002, 2, '0.75', 'R-2'),
(1003, 3, '5.0', 'C-1'),
(1004, 4, '2.0', 'R-1A'),
(1005, 5, '10.0', 'I-2'),
(1006, 6, '0.5', 'R-1'),
(1007, 7, '1.2', 'R-1'),
(1008, 8, '0.8', 'C-1'),
(1009, 9, '3.0', 'R-2'),
(1010, 10, '0.25', 'B-1'),
(1011, 11, '4.0', 'A-1'),
(1012, 12, '0.6', 'R-1'),
(1013, 13, '2.5', 'R-1'),
(1014, 14, '0.9', 'C-2'),
(1015, 15, '7.0', 'I-1'),
(1016, 16, '1.1', 'R-1'),
(1017, 17, '0.3', 'R-2'),
(1018, 18, '6.0', 'C-1');


-- Sample Inserts for zva_property_owners
INSERT INTO zva_property_owners (zva_owner_first_name, zva_owner_last_name, address_id) VALUES
('ZVAOwner', 'One', 1),
('ZVAOwner', 'Two', 2),
('ZVAOwner', 'Three', 3),
('ZVAOwner', 'Four', 4),
('ZVAOwner', 'Five', 5),
('ZVAOwner', 'Six', 6);

-- Sample Inserts for zva_applicants
INSERT INTO zva_applicants (zva_applicant_first_name, zva_applicant_last_name, address_id, zva_applicant_phone_number, zva_applicant_fax_number) VALUES
('ZVAApplicant', 'One', 7, '555-7001', '555-7002'),
('ZVAApplicant', 'Two', 8, '555-7003', '555-7004'),
('ZVAApplicant', 'Three', 9, '555-7005', '555-7006'),
('ZVAApplicant', 'Four', 10, '555-7007', '555-7008'),
('ZVAApplicant', 'Five', 11, '555-7009', '555-7010'),
('ZVAApplicant', 'Six', 12, '555-7011', '555-7012');

-- Sample Inserts for apof_neighbors
INSERT INTO apof_neighbors (PVA_map_code, apof_neighbor_property_location, apof_neighbor_property_deed_book, apof_property_street_pg_number) VALUES
('MAP100-A', 'North Side', 'DB1234', 'PG56'),
('MAP100-B', 'South Side', 'DB1235', 'PG57'),
('MAP101-A', 'East Side', 'DB1236', 'PG58'),
('MAP101-B', 'West Side', 'DB1237', 'PG59'),
('MAP102-A', 'North Side', 'DB1238', 'PG60'),
('MAP102-B', 'South Side', 'DB1239', 'PG61');

-- Sample Inserts for type_one_applicants
INSERT INTO type_one_applicants (t1_applicant_first_name, t1_applicant_last_name, address_id, t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email) VALUES
('T1Applicant', 'One', 19, '555-8001', '555-8002', 't1app1@email.com'),
('T1Applicant', 'Two', 20, '555-8003', '555-8004', 't1app2@email.com'),
('T1Applicant', 'Three', 21, '555-8005', '555-8006', 't1app3@email.com'),
('T1Applicant', 'Four', 22, '555-8007', '555-8008', 't1app4@email.com'),
('T1Applicant', 'Five', 23, '555-8009', '555-8010', 't1app5@email.com'),
('T1Applicant', 'Six', 24, '555-8011', '555-8012', 't1app6@email.com');

-- Sample Inserts for sp_property_owners
INSERT INTO sp_property_owners (sp_owner_first_name, sp_owner_last_name, address_id) VALUES
('SPOwner', 'One', 1),
('SPOwner', 'Two', 2),
('SPOwner', 'Three', 3),
('SPOwner', 'Four', 4),
('SPOwner', 'Five', 5),
('SPOwner', 'Six', 6);

-- Sample Inserts for sp_contractors
INSERT INTO sp_contractors (sp_contractor_first_name, sp_contractor_last_name, sp_contractor_phone_number) VALUES
('SPContractor', 'A', '555-9001'),
('SPContractor', 'B', '555-9002'),
('SPContractor', 'C', '555-9003'),
('SPContractor', 'D', '555-9004'),
('SPContractor', 'E', '555-9005'),
('SPContractor', 'F', '555-9006');

-- Sample Inserts for sp_businesses
INSERT INTO sp_businesses (sp_business_name, address_id) VALUES
('BizCo 1', 25),
('BizCo 2', 26),
('BizCo 3', 27),
('BizCo 4', 28),
('BizCo 5', 29),
('BizCo 6', 30);

-- Sample Inserts for aar_property_owners
INSERT INTO aar_property_owners (aar_property_owner_first_name, aar_property_owner_last_name) VALUES
('AAROwner', 'One'),
('AAROwner', 'Two'),
('AAROwner', 'Three'),
('AAROwner', 'Four'),
('AAROwner', 'Five'),
('AAROwner', 'Six');

-- Sample Inserts for aar_appellants
INSERT INTO aar_appellants (aar_first_name, aar_last_name) VALUES
('AARAppellant', 'Alpha'),
('AARAppellant', 'Beta'),
('AARAppellant', 'Gamma'),
('AARAppellant', 'Delta'),
('AARAppellant', 'Epsilon'),
('AARAppellant', 'Zeta');

-- Sample Inserts for orr_applicants
INSERT INTO orr_applicants (orr_applicant_first_name, orr_applicant_last_name, orr_applicant_telephone, address_id) VALUES
('ORRApplicant', 'Xavier', '555-1101', 31),
('ORRApplicant', 'Yara', '555-1102', 32),
('ORRApplicant', 'Zack', '555-1103', 33),
('ORRApplicant', 'Wendy', '555-1104', 34),
('ORRApplicant', 'Victor', '555-1105', 35),
('ORRApplicant', 'Uma', '555-1106', 36);

-- Sample Inserts for public_records
INSERT INTO public_records (public_record_description) VALUES
('Deed for 123 Main St'),
('Zoning Ordinance Chapter 3'),
('Minutes from 2023 Planning Meeting'),
('Building Permit Application #12345'),
('Environmental Impact Study for Riverfront Development'),
('Historic District Guidelines');

-- Sample Inserts for type_one_execs
INSERT INTO type_one_execs (t1e_exec_first_name, t1e_exec_last_name) VALUES
('Exec', 'Prime'),
('Exec', 'Second'),
('Exec', 'Third'),
('Exec', 'Fourth'),
('Exec', 'Fifth'),
('Exec', 'Sixth');

-- Sample Inserts for type_one_owners
INSERT INTO type_one_owners (t1o_owner_first_name, t1o_owner_last_name) VALUES
('T1Owner', 'A'),
('T1Owner', 'B'),
('T1Owner', 'C'),
('T1Owner', 'D'),
('T1Owner', 'E'),
('T1Owner', 'F');

-- Sample Inserts for adjacent_property_owners
INSERT INTO adjacent_property_owners (address_id) VALUES
(37), (38), (39), (40), (41), (42);

-- Sample Inserts for signs
INSERT INTO signs (sp_owner_id, sign_type, sign_square_footage, lettering_height) VALUES
(1, 'Freestanding', 50.00, '5 ft'),
(2, 'Wall Mounted', 30.50, '3 ft'),
(3, 'Monument', 75.25, '6 ft'),
(4, 'Projecting', 20.00, '2 ft'),
(5, 'Window', 15.75, '1.5 ft'),
(6, 'Banner', 40.00, '4 ft');


-- Insert forms (ensure at least 6 of each type)
-- Administrative Appeal Request (form_type_id = 1)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Administrative Appeal Request', '2023-01-01 10:00:00', TRUE, 1),
('Administrative Appeal Request', '2023-01-02 11:00:00', TRUE, 2),
('Administrative Appeal Request', '2023-01-03 12:00:00', FALSE, 3),
('Administrative Appeal Request', '2023-01-04 13:00:00', TRUE, 4),
('Administrative Appeal Request', '2023-01-05 14:00:00', FALSE, 5),
('Administrative Appeal Request', '2023-01-06 15:00:00', TRUE, 6);

-- Adjacent Property Owners Form (form_type_id = 2)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Adjacent Property Owners Form', '2023-02-01 10:00:00', TRUE, 7),
('Adjacent Property Owners Form', '2023-02-02 11:00:00', TRUE, 8),
('Adjacent Property Owners Form', '2023-02-03 12:00:00', FALSE, 9),
('Adjacent Property Owners Form', '2023-02-04 13:00:00', TRUE, 10),
('Adjacent Property Owners Form', '2023-02-05 14:00:00', FALSE, 11),
('Adjacent Property Owners Form', '2023-02-06 15:00:00', TRUE, 12);

-- Conditional Use Permit Application (form_type_id = 3)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Conditional Use Permit Application', '2023-03-01 10:00:00', TRUE, 13),
('Conditional Use Permit Application', '2023-03-02 11:00:00', TRUE, 14),
('Conditional Use Permit Application', '2023-03-03 12:00:00', FALSE, 15),
('Conditional Use Permit Application', '2023-03-04 13:00:00', TRUE, 16),
('Conditional Use Permit Application', '2023-03-05 14:00:00', FALSE, 17),
('Conditional Use Permit Application', '2023-03-06 15:00:00', TRUE, 18);

-- Development Plan Application (General) (form_type_id = 4)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Development Plan Application (General)', '2023-04-01 10:00:00', TRUE, 19),
('Development Plan Application (General)', '2023-04-02 11:00:00', TRUE, 20),
('Development Plan Application (General)', '2023-04-03 12:00:00', FALSE, 21),
('Development Plan Application (General)', '2023-04-04 13:00:00', TRUE, 22),
('Development Plan Application (General)', '2023-04-05 14:00:00', FALSE, 23),
('Development Plan Application (General)', '2023-04-06 15:00:00', TRUE, 24);

-- Development Plan Application (Site) (form_type_id = 5)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Development Plan Application (Site)', '2023-05-01 10:00:00', TRUE, 25),
('Development Plan Application (Site)', '2023-05-02 11:00:00', TRUE, 26),
('Development Plan Application (Site)', '2023-05-03 12:00:00', FALSE, 27),
('Development Plan Application (Site)', '2023-05-04 13:00:00', TRUE, 28),
('Development Plan Application (Site)', '2023-05-05 14:00:00', FALSE, 29),
('Development Plan Application (Site)', '2023-05-06 15:00:00', TRUE, 30);

-- Future Land Use Map (FLUM) Application (form_type_id = 6)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Future Land Use Map (FLUM) Application', '2023-06-01 10:00:00', TRUE, 31),
('Future Land Use Map (FLUM) Application', '2023-06-02 11:00:00', TRUE, 32),
('Future Land Use Map (FLUM) Application', '2023-06-03 12:00:00', FALSE, 33),
('Future Land Use Map (FLUM) Application', '2023-06-04 13:00:00', TRUE, 34),
('Future Land Use Map (FLUM) Application', '2023-06-05 14:00:00', FALSE, 35),
('Future Land Use Map (FLUM) Application', '2023-06-06 15:00:00', TRUE, 36);

-- Open Records Request (form_type_id = 7)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Open Records Request', '2023-07-01 10:00:00', TRUE, 37),
('Open Records Request', '2023-07-02 11:00:00', TRUE, 38),
('Open Records Request', '2023-07-03 12:00:00', FALSE, 39),
('Open Records Request', '2023-07-04 13:00:00', TRUE, 40),
('Open Records Request', '2023-07-05 14:00:00', FALSE, 41),
('Open Records Request', '2023-07-06 15:00:00', TRUE, 42);

-- Sign Permit Application (form_type_id = 8)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Sign Permit Application', '2023-08-01 10:00:00', TRUE, 43),
('Sign Permit Application', '2023-08-02 11:00:00', TRUE, 44),
('Sign Permit Application', '2023-08-03 12:00:00', FALSE, 45),
('Sign Permit Application', '2023-08-04 13:00:00', TRUE, 46),
('Sign Permit Application', '2023-08-05 14:00:00', FALSE, 47),
('Sign Permit Application', '2023-08-06 15:00:00', TRUE, 48);

-- Major Subdivision Plat Application (form_type_id = 9)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Major Subdivision Plat Application', '2023-09-01 10:00:00', TRUE, 49),
('Major Subdivision Plat Application', '2023-09-02 11:00:00', TRUE, 50),
('Major Subdivision Plat Application', '2023-09-03 12:00:00', FALSE, 51),
('Major Subdivision Plat Application', '2023-09-04 13:00:00', TRUE, 52),
('Major Subdivision Plat Application', '2023-09-05 14:00:00', FALSE, 53),
('Major Subdivision Plat Application', '2023-09-06 15:00:00', TRUE, 54);

-- Minor Subdivision Plat Application (form_type_id = 10)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Minor Subdivision Plat Application', '2023-10-01 10:00:00', TRUE, 55),
('Minor Subdivision Plat Application', '2023-10-02 11:00:00', TRUE, 56),
('Minor Subdivision Plat Application', '2023-10-03 12:00:00', FALSE, 57),
('Minor Subdivision Plat Application', '2023-10-04 13:00:00', TRUE, 58),
('Minor Subdivision Plat Application', '2023-10-05 14:00:00', FALSE, 59),
('Minor Subdivision Plat Application', '2023-10-06 15:00:00', TRUE, 60);

-- Variance Application (form_type_id = 11)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Variance Application', '2023-11-01 10:00:00', TRUE, 61),
('Variance Application', '2023-11-02 11:00:00', TRUE, 62),
('Variance Application', '2023-11-03 12:00:00', FALSE, 63),
('Variance Application', '2023-11-04 13:00:00', TRUE, 64),
('Variance Application', '2023-11-05 14:00:00', FALSE, 65),
('Variance Application', '2023-11-06 15:00:00', TRUE, 66);

-- Zoning Map Amendment Application (form_type_id = 12)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Zoning Map Amendment Application', '2023-12-01 10:00:00', TRUE, 67),
('Zoning Map Amendment Application', '2023-12-02 11:00:00', TRUE, 68),
('Zoning Map Amendment Application', '2023-12-03 12:00:00', FALSE, 69),
('Zoning Map Amendment Application', '2023-12-04 13:00:00', TRUE, 70),
('Zoning Map Amendment Application', '2023-12-05 14:00:00', FALSE, 71),
('Zoning Map Amendment Application', '2023-12-06 15:00:00', TRUE, 72);

-- Zoning Permit Application (form_type_id = 13)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Zoning Permit Application', '2024-01-01 10:00:00', TRUE, 73),
('Zoning Permit Application', '2024-01-02 11:00:00', TRUE, 74),
('Zoning Permit Application', '2024-01-03 12:00:00', FALSE, 75),
('Zoning Permit Application', '2024-01-04 13:00:00', TRUE, 76),
('Zoning Permit Application', '2024-01-05 14:00:00', FALSE, 77),
('Zoning Permit Application', '2024-01-06 15:00:00', TRUE, 78);

-- Zoning Verification Application (form_type_id = 14)
INSERT INTO forms (form_type, form_datetime_submitted, form_paid_bool, correction_form_id) VALUES
('Zoning Verification Application', '2024-02-01 10:00:00', TRUE, 79),
('Zoning Verification Application', '2024-02-02 11:00:00', TRUE, 80),
('Zoning Verification Application', '2024-02-03 12:00:00', FALSE, 81),
('Zoning Verification Application', '2024-02-04 13:00:00', TRUE, 82),
('Zoning Verification Application', '2024-02-05 14:00:00', FALSE, 83),
('Zoning Verification Application', '2024-02-06 15:00:00', TRUE, 84);

-- Links between tables and forms

-- zoning_verification_letter (form_id 79-84)
INSERT INTO zoning_verification_letter (form_id, zva_owner_id, zva_applicant_id, zva_letter_content, zva_zoning_address_id, zva_property_address_id) VALUES
(79, 1, 1, 'Content for ZVL form 79', 1, 1),
(80, 2, 2, 'Content for ZVL form 80', 2, 2),
(81, 3, 3, 'Content for ZVL form 81', 3, 3),
(82, 4, 4, 'Content for ZVL form 82', 4, 4),
(83, 5, 5, 'Content for ZVL form 83', 5, 5),
(84, 6, 6, 'Content for ZVL form 84', 6, 6);

-- adjacent_property_owner_forms (form_id 7-12)
-- These forms exist, but the table itself only links to `forms` via FK.
-- The actual neighbor data is in `adjacent_neighbors` and `adjacent_neighbor_owners`.

-- adjacent_neighbors (form_id 7-12)
INSERT INTO adjacent_neighbors (form_id, neighbor_id) VALUES
(7, 1), (7, 2),
(8, 3), (8, 4),
(9, 5), (9, 6),
(10, 1), (10, 3),
(11, 2), (11, 4),
(12, 5), (12, 1);

-- applicants_link_forms (linking type_one_applicants to forms 85-90)
INSERT INTO applicants_link_forms (t1_applicant_id, form_id) VALUES
(1, (SELECT form_id FROM forms WHERE form_type = 'Zoning Map Amendment Application' ORDER BY form_id DESC LIMIT 1 OFFSET 5)),
(2, (SELECT form_id FROM forms WHERE form_type = 'Variance Application' ORDER BY form_id DESC LIMIT 1 OFFSET 4)),
(3, (SELECT form_id FROM forms WHERE form_type = 'Major Subdivision Plat Application' ORDER BY form_id DESC LIMIT 1 OFFSET 3)),
(4, (SELECT form_id FROM forms WHERE form_type = 'Major Subdivision Plat Application' ORDER BY form_id DESC LIMIT 1 OFFSET 2)),
(5, (SELECT form_id FROM forms WHERE form_type = 'Future Land Use Map (FLUM) Application' ORDER BY form_id DESC LIMIT 1 OFFSET 1)),
(6, (SELECT form_id FROM forms WHERE form_type = 'Future Land Use Map (FLUM) Application' ORDER BY form_id DESC LIMIT 1 OFFSET 0));


-- major_subdivision_plat_applications (form_id 49-54)
INSERT INTO major_subdivision_plat_applications (form_id, surveyor_id, engineer_id, PVA_parcel_number, mspa_topographic_survey, mspa_proposed_plot_layout, mspa_plat_restrictions, mspa_property_owner_convenants, mspa_association_covenants, mspa_master_deed, mspa_construction_plans, mspa_traffic_impact_study, mspa_geologic_study, mspa_drainage_plan, mspa_pavement_design, mspa_SWPPP_EPSC_plan, mspa_construction_bond_est) VALUES
(49, 1, 1, 1001, 'mspa_topo_49.pdf', 'mspa_plot_49.pdf', 'plat_restrict_49.txt', 'owner_covenants_49.txt', 'assoc_covenants_49.txt', 'master_deed_49.pdf', 'const_plans_49.pdf', 'traffic_49.pdf', 'geologic_49.pdf', 'drainage_49.pdf', 'pavement_49.pdf', 'SWPPP_49.pdf', 'bond_49.pdf'),
(50, 2, 2, 1002, 'mspa_topo_50.pdf', 'mspa_plot_50.pdf', 'plat_restrict_50.txt', 'owner_covenants_50.txt', 'assoc_covenants_50.txt', 'master_deed_50.pdf', 'const_plans_50.pdf', 'traffic_50.pdf', 'geologic_50.pdf', 'drainage_50.pdf', 'pavement_50.pdf', 'SWPPP_50.pdf', 'bond_50.pdf'),
(51, 3, 3, 1003, 'mspa_topo_51.pdf', 'mspa_plot_51.pdf', 'plat_restrict_51.txt', 'owner_covenants_51.txt', 'assoc_covenants_51.txt', 'master_deed_51.pdf', 'const_plans_51.pdf', 'traffic_51.pdf', 'geologic_51.pdf', 'drainage_51.pdf', 'pavement_51.pdf', 'SWPPP_51.pdf', 'bond_51.pdf'),
(52, 4, 4, 1004, 'mspa_topo_52.pdf', 'mspa_plot_52.pdf', 'plat_restrict_52.txt', 'owner_covenants_52.txt', 'assoc_covenants_52.txt', 'master_deed_52.pdf', 'const_plans_52.pdf', 'traffic_52.pdf', 'geologic_52.pdf', 'drainage_52.pdf', 'pavement_52.pdf', 'SWPPP_52.pdf', 'bond_52.pdf'),
(53, 5, 5, 1005, 'mspa_topo_53.pdf', 'mspa_plot_53.pdf', 'plat_restrict_53.txt', 'owner_covenants_53.txt', 'assoc_covenants_53.txt', 'master_deed_53.pdf', 'const_plans_53.pdf', 'traffic_53.pdf', 'geologic_53.pdf', 'drainage_53.pdf', 'pavement_53.pdf', 'SWPPP_53.pdf', 'bond_53.pdf'),
(54, 6, 6, 1006, 'mspa_topo_54.pdf', 'mspa_plot_54.pdf', 'plat_restrict_54.txt', 'owner_covenants_54.txt', 'assoc_covenants_54.txt', 'master_deed_54.pdf', 'const_plans_54.pdf', 'traffic_54.pdf', 'geologic_54.pdf', 'drainage_54.pdf', 'pavement_54.pdf', 'SWPPP_54.pdf', 'bond_54.pdf');

-- minor_subdivision_plat_applications (form_id 55-60)
INSERT INTO minor_subdivision_plat_applications (form_id, surveyor_id, engineer_id, PVA_parcel_number, minspa_topographic_survey, minspa_proposed_plot_layout, minspa_plat_restrictions, minspa_property_owner_covenants, minspa_association_covenants, minspa_master_deed) VALUES
(55, 1, 1, 1007, 'minspa_topo_55.pdf', 'minspa_plot_55.pdf', 'minspa_restrict_55.txt', 'minspa_owner_cov_55.txt', 'minspa_assoc_cov_55.txt', 'minspa_master_55.pdf'),
(56, 2, 2, 1008, 'minspa_topo_56.pdf', 'minspa_plot_56.pdf', 'minspa_restrict_56.txt', 'minspa_owner_cov_56.txt', 'minspa_assoc_cov_56.txt', 'minspa_master_56.pdf'),
(57, 3, 3, 1009, 'minspa_topo_57.pdf', 'minspa_plot_57.pdf', 'minspa_restrict_57.txt', 'minspa_owner_cov_57.txt', 'minspa_assoc_cov_57.txt', 'minspa_master_57.pdf'),
(58, 4, 4, 1010, 'minspa_topo_58.pdf', 'minspa_plot_58.pdf', 'minspa_restrict_58.txt', 'minspa_owner_cov_58.txt', 'minspa_assoc_cov_58.txt', 'minspa_master_58.pdf'),
(59, 5, 5, 1011, 'minspa_topo_59.pdf', 'minspa_plot_59.pdf', 'minspa_restrict_59.txt', 'minspa_owner_cov_59.txt', 'minspa_assoc_cov_59.txt', 'minspa_master_59.pdf'),
(60, 6, 6, 1012, 'minspa_topo_60.pdf', 'minspa_plot_60.pdf', 'minspa_restrict_60.txt', 'minspa_owner_cov_60.txt', 'minspa_assoc_cov_60.txt', 'minspa_master_60.pdf');

-- technical_forms (no direct form_type, can link to any form)
INSERT INTO technical_forms (form_id, technical_app_filing_date, technical_review_date, technical_prelim_approval_date, technical_final_approval_date) VALUES
(49, '2023-09-01', '2023-09-15', '2023-10-01', '2023-10-15'),
(50, '2023-09-02', '2023-09-16', '2023-10-02', '2023-10-16'),
(51, '2023-09-03', '2023-09-17', '2023-10-03', '2023-10-17'),
(52, '2023-09-04', '2023-09-18', '2023-10-04', '2023-10-18'),
(53, '2023-09-05', '2023-09-19', '2023-10-05', '2023-10-19'),
(54, '2023-09-06', '2023-09-20', '2023-10-06', '2023-10-20');

-- LDS_plans (linking to technical_forms)
INSERT INTO LDS_plans (form_id, LDS_plan_file) VALUES
(49, 'LDS_plan_49_A.pdf'), (49, 'LDS_plan_49_B.pdf'),
(50, 'LDS_plan_50_A.pdf'),
(51, 'LDS_plan_51_A.pdf'),
(52, 'LDS_plan_52_A.pdf'),
(53, 'LDS_plan_53_A.pdf'),
(54, 'LDS_plan_54_A.pdf');

-- structures (linking to zoning_permit_applications (form_id 73-78))
INSERT INTO structures (form_id, structure_type, structure_square_feet, structure_project_value, structure_notes) VALUES
(73, 'Residential House', 2500.00, '500000', 'New construction'),
(74, 'Commercial Building', 10000.00, '2000000', 'Renovation'),
(75, 'Garage', 500.00, '50000', 'Attached garage'),
(76, 'Multi-Family Duplex', 3000.00, '600000', 'Two units'),
(77, 'Shed', 100.00, '10000', 'Storage shed'),
(78, 'Office Building', 15000.00, '3000000', 'New office space');

-- WSF_verifications (linking to zoning_permit_applications (form_id 73-78))
INSERT INTO WSF_verifications (form_id, WSF_verification_file) VALUES
(73, 'WSF_cert_73.pdf'),
(74, 'WSF_cert_74.pdf'),
(75, 'WSF_cert_75.pdf'),
(76, 'WSF_cert_76.pdf'),
(77, 'WSF_cert_77.pdf'),
(78, 'WSF_cert_78.pdf');

-- zoning_permit_applications (form_id 73-78)
INSERT INTO zoning_permit_applications (form_id, surveyor_id, architect_id, land_architect_id, contractor_id, PVA_parcel_number, project_type, zpa_project_plans, zpa_preliminary_site_evaluation) VALUES
(73, 1, 1, 1, 1, 1013, 'Residential Use', 'zpa_plans_73.pdf', 'zpa_eval_73.pdf'),
(74, 2, 2, 2, 2, 1014, 'Commercial Use', 'zpa_plans_74.pdf', 'zpa_eval_74.pdf'),
(75, 3, 3, 3, 3, 1015, 'Industrial Use', 'zpa_plans_75.pdf', 'zpa_eval_75.pdf'),
(76, 4, 4, 4, 4, 1016, 'Multi-Family Use', 'zpa_plans_76.pdf', 'zpa_eval_76.pdf'),
(77, 5, 5, 5, 5, 1017, 'Parking/ Display', 'zpa_plans_77.pdf', 'zpa_eval_77.pdf'),
(78, 6, 6, 6, 6, 1018, 'Use Change', 'zpa_plans_78.pdf', 'zpa_eval_78.pdf');

-- zoning_map_amendment_applications (form_id 67-72)
INSERT INTO zoning_map_amendment_applications (form_id, zoning_map_amendment_request, zmaa_proposed_conditions, PVA_parcel_number) VALUES
(67, 'Change from R-1 to R-2', 'Conditions for R-2 development', 1001),
(68, 'Change from C-1 to C-2', 'Conditions for C-2 development', 1002),
(69, 'Change from I-1 to I-2', 'Conditions for I-2 development', 1003),
(70, 'Change from R-2 to C-1', 'Conditions for C-1 development', 1004),
(71, 'Change from A-1 to R-1', 'Conditions for R-1 development', 1005),
(72, 'Change from R-1A to B-1', 'Conditions for B-1 development', 1006);

-- general_development_plan_applications (form_id 19-24)
INSERT INTO general_development_plan_applications (form_id, address_id, gdpa_applicant_phone, gdpa_plan_amendment_request, gdpa_proposed_conditions, required_findings_type, gdpa_concept_plan, gdpa_traffic_study, gdpa_geologic_analysis) VALUES
(19, 1, '555-0101', 'Amendment A', 'Conditions A', 'significant_change', 'concept_A.pdf', 'traffic_A.pdf', 'geo_A.pdf'),
(20, 2, '555-0102', 'Amendment B', 'Conditions B', 'physical_development', 'concept_B.pdf', 'traffic_B.pdf', 'geo_B.pdf'),
(21, 3, '555-0103', 'Amendment C', 'Conditions C', 'petition_movement', 'concept_C.pdf', 'traffic_C.pdf', 'geo_C.pdf'),
(22, 4, '555-0104', 'Amendment D', 'Conditions D', 'significant_change', 'concept_D.pdf', 'traffic_D.pdf', 'geo_D.pdf'),
(23, 5, '555-0105', 'Amendment E', 'Conditions E', 'physical_development', 'concept_E.pdf', 'traffic_E.pdf', 'geo_E.pdf'),
(24, 6, '555-0106', 'Amendment F', 'Conditions F', 'petition_movement', 'concept_F.pdf', 'traffic_F.pdf', 'geo_F.pdf');

-- variance_applications (form_id 61-66)
INSERT INTO variance_applications (form_id, va_variance_request, va_proposed_conditions, PVA_parcel_number) VALUES
(61, 'Setback reduction', 'Maintain green space', 1007),
(62, 'Height variance', 'Limit to 3 stories', 1008),
(63, 'Parking reduction', 'Provide public transport access', 1009),
(64, 'Lot coverage increase', 'Use permeable surfaces', 1010),
(65, 'Sign size increase', 'No digital displays', 1011),
(66, 'Fence height', 'Decorative fencing', 1012);

-- future_land_use_map_applications (form_id 31-36)
INSERT INTO future_land_use_map_applications (form_id, future_land_use_map_amendment_prop, PVA_parcel_number) VALUES
(31, 'Change from Residential to Mixed-Use', 1013),
(32, 'Change from Commercial to High Density Residential', 1014),
(33, 'Change from Industrial to Green Space', 1015),
(34, 'Change from Agriculture to Rural Residential', 1016),
(35, 'Change from Public to Commercial', 1017),
(36, 'Change from Open Space to Recreation', 1018);

-- conditional_use_permit_applications (form_id 13-18)
INSERT INTO conditional_use_permit_applications (form_id, cupa_permit_request, cupa_proposed_conditions, PVA_parcel_number) VALUES
(13, 'Home Occupation Permit', 'No external signage', 1001),
(14, 'Event Venue Permit', 'Noise restrictions after 10 PM', 1002),
(15, 'Daycare Facility Permit', 'Outdoor play area fencing', 1003),
(16, 'Small Brewery Permit', 'Limited hours of operation', 1004),
(17, 'Community Garden Permit', 'Water access and tool storage', 1005),
(18, 'Bed and Breakfast Permit', 'No more than 5 guest rooms', 1006);

-- hearing_forms (linking to various forms)
INSERT INTO hearing_forms (form_id, hearing_docket_number, hearing_date_application_filed, hearing_date, hearing_preapp_meeting_date, attorney_id) VALUES
(13, 'CUP-2023-001', '2023-03-01', '2023-03-20', '2023-03-10', 1),
(14, 'CUP-2023-002', '2023-03-02', '2023-03-21', '2023-03-11', 2),
(61, 'VAR-2023-001', '2023-11-01', '2023-11-20', '2023-11-10', 3),
(67, 'ZMA-2023-001', '2023-12-01', '2023-12-20', '2023-12-10', 4),
(19, 'GDP-2023-001', '2023-04-01', '2023-04-20', '2023-04-10', 5),
(73, 'ZPA-2024-001', '2024-01-01', '2024-01-20', '2024-01-10', 6);

-- orr_public_record_names (linking open_record_requests (form_id 37-42) to public_records)
INSERT INTO orr_public_record_names (form_id, public_record_id) VALUES
(37, 1), (37, 2),
(38, 3), (38, 4),
(39, 5), (39, 6),
(40, 1), (40, 3),
(41, 2), (41, 5),
(42, 4), (42, 6);

-- open_record_requests (form_id 37-42)
INSERT INTO open_record_requests (form_id, orr_commercial_purpose, orr_request_for_copies, orr_received_on_datetime, orr_receivable_datetime, orr_denied_reasons, orr_applicant_id) VALUES
(37, 'No', 'Digital', '2023-07-01', '2023-07-05', NULL, 1),
(38, 'Yes', 'Physical', '2023-07-02', '2023-07-06', NULL, 2),
(39, 'No', 'Digital', '2023-07-03', '2023-07-07', 'Personal information', 3),
(40, 'Yes', 'Physical', '2023-07-04', '2023-07-08', NULL, 4),
(41, 'No', 'Digital', '2023-07-05', '2023-07-09', NULL, 5),
(42, 'Yes', 'Physical', '2023-07-06', '2023-07-10', 'Sensitive data', 6);

-- administrative_property_owners (linking administrative_appeal_requests (form_id 1-6) to aar_property_owners)
INSERT INTO administrative_property_owners (form_id, aar_property_owner_id) VALUES
(1, 1), (1, 2),
(2, 3), (2, 4),
(3, 5), (3, 6),
(4, 1), (4, 3),
(5, 2), (5, 4),
(6, 5), (6, 1);

-- administrative_appellants (linking administrative_appeal_requests (form_id 1-6) to aar_appellants)
INSERT INTO administrative_appellants (form_id, aar_appellant_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6);

-- administrative_appeal_requests (form_id 1-6)
INSERT INTO administrative_appeal_requests (form_id, aar_hearing_date, aar_submit_date, address_id, aar_official_decision, aar_relevant_provisions) VALUES
(1, '2023-01-20', '2023-01-01', 1, 'Upheld', 'Zoning Ordinance 3.1'),
(2, '2023-01-21', '2023-01-02', 2, 'Overturned', 'Zoning Ordinance 3.2'),
(3, '2023-01-22', '2023-01-03', 3, 'Modified', 'Zoning Ordinance 3.3'),
(4, '2023-01-23', '2023-01-04', 4, 'Upheld', 'Zoning Ordinance 3.1'),
(5, '2023-01-24', '2023-01-05', 5, 'Overturned', 'Zoning Ordinance 3.2'),
(6, '2023-01-25', '2023-01-06', 6, 'Modified', 'Zoning Ordinance 3.3');

-- sign_permit_applications (form_id 43-48)
INSERT INTO sign_permit_applications (form_id, owner_id, sp_contractor_id, business_id, sp_date, sp_permit_number, sp_building_coverage_percent, sp_permit_fee) VALUES
(43, 1, 1, 1, '2023-08-01', 'SP-2023-001', '5%', '100.00'),
(44, 2, 2, 2, '2023-08-02', 'SP-2023-002', '7%', '120.00'),
(45, 3, 3, 3, '2023-08-03', 'SP-2023-003', '6%', '110.00'),
(46, 4, 4, 4, '2023-08-04', 'SP-2023-004', '8%', '130.00'),
(47, 5, 5, 5, '2023-08-05', 'SP-2023-005', '4%', '90.00'),
(48, 6, 6, 6, '2023-08-06', 'SP-2023-006', '9%', '140.00');

-- departments (linking to clients)
INSERT INTO departments (client_id, department_name) VALUES
(5, 'Planning Department'),
(6, 'Clerk Office'),
(1, 'Public Works'),
(7, 'Housing Authority'),
(8, 'Economic Development');

-- department_form_interactions (linking clients to forms)
INSERT INTO department_form_interactions (client_id, form_id, department_form_interaction_description) VALUES
(5, 73, 'Reviewed for zoning compliance'),
(6, 37, 'Processed public record request'),
(5, 19, 'Initial review of development plan'),
(6, 1, 'Filed administrative appeal'),
(5, 43, 'Approved sign permit'),
(6, 7, 'Received adjacent property owner form');

-- govt_workers (linking to clients)
INSERT INTO govt_workers (client_id, govt_worker_role) VALUES
(5, 'Zoning Administrator'),
(6, 'Records Clerk'),
(7, 'Building Inspector'),
(8, 'Planning Commissioner'),
(9, 'City Engineer'),
(10, 'Survey Reviewer');

-- incomplete_client_forms (linking clients to forms)
INSERT INTO incomplete_client_forms (form_id, client_id) VALUES
(1, 1), (2, 2), (3, 1), (4, 3), (5, 2), (6, 4);

-- client_forms (linking clients to forms)
INSERT INTO client_forms (form_id, client_id) VALUES
(73, 1), (74, 2), (75, 3), (76, 4), (77, 1), (78, 2);

-- site_development_plan_applications (form_id 25-30)
INSERT INTO site_development_plan_applications (form_id, surveyor_id, land_architect_id, engineer_id, architect_id, site_plan_request) VALUES
(25, 1, 1, 1, 1, 'Residential Site Plan'),
(26, 2, 2, 2, 2, 'Commercial Site Plan'),
(27, 3, 3, 3, 3, 'Industrial Site Plan'),
(28, 4, 4, 4, 4, 'Mixed-Use Site Plan'),
(29, 5, 5, 5, 5, 'Retail Center Site Plan'),
(30, 6, 6, 6, 6, 'Office Park Site Plan');

-- type_one_applicant_execs (linking type_one_execs to type_one_applicants)
INSERT INTO type_one_applicant_execs (t1e_exec_id, t1_applicant_id) VALUES
(1, 1), (2, 1),
(3, 2), (4, 2),
(5, 3), (6, 3),
(1, 4), (2, 4),
(3, 5), (4, 5),
(5, 6), (6, 6);


-- adjacent_neighbor_owners (linking adjacent_property_owner_forms (form_id 7-12) to adjacent_property_owners)
INSERT INTO adjacent_neighbor_owners (form_id, adjacent_property_owner_id) VALUES
(7, 1), (7, 2),
(8, 3), (8, 4),
(9, 5), (9, 6),
(10, 1), (10, 3),
(11, 2), (11, 5),
(12, 4), (12, 6);

-- permits_link_signs (linking sign_permit_applications (form_id 43-48) to signs)
INSERT INTO permits_link_signs (form_id, sign_id) VALUES
(43, 1), (43, 2),
(44, 3),
(45, 4),
(46, 5),
(47, 6),
(48, 1);

SET FOREIGN_KEY_CHECKS = 1;