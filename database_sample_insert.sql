-- =========================================================================
-- PANDZ DATABASE - SAMPLE INSERT STATEMENTS
-- Complete set of realistic test data for all tables
-- =========================================================================

-- -------------------------------------------------------------------------
-- SECTION 1: REFERENCE DATA (Lookup Tables)
-- -------------------------------------------------------------------------
-- Public Records
INSERT INTO public_records (public_record_id, public_record_description) VALUES 
    (1, 'Zoning ordinance text and maps'),
    (2, 'Approved subdivision plats and final plans'),
    (3, 'Site development plans and engineering drawings'),
    (4, 'Variance applications and Board of Adjustments decisions'),
    (5, 'Planning Commission meeting minutes and agendas'),
    (6, 'Building permit applications and inspection records'),
    (7, 'Comprehensive plan and future land use maps'),
    (8, 'Conditional use permit applications and approvals'),
    (9, 'Administrative appeals and hearing transcripts'),
    (10, 'Sign permit applications and approved plans');

-- Departments
INSERT INTO departments (department_id, department_name) VALUES 
    (1, 'Planning and Zoning'),
    (2, 'Engineering and Public Works'),
    (3, 'Building Inspection'),
    (4, 'Fire Marshal'),
    (5, 'Health Department'),
    (6, 'City Attorney'),
    (7, 'Parks and Recreation'),
    (8, 'Utilities Department');

-- -------------------------------------------------------------------------
-- SECTION 2: PROPERTIES
-- -------------------------------------------------------------------------

INSERT INTO properties (PVA_parcel_number, property_street_address, property_city, state_code, property_zip_code, property_acreage, property_current_zoning) VALUES 
    (200001, '1500 Maple Street', 'Danville', 'KY', '40422', '0.25', 'R-1'),
    (200002, '2750 Oak Avenue', 'Danville', 'KY', '40422', '1.5', 'R-2'),
    (200003, '3200 Elm Drive', 'Danville', 'KY', '40422', '0.75', 'R-3'),
    (200004, '4500 Main Street', 'Danville', 'KY', '40422', '2.3', 'B-1'),
    (200005, '5100 Commerce Parkway', 'Danville', 'KY', '40422', '5.8', 'B-2'),
    (200006, '6800 Industrial Boulevard', 'Danville', 'KY', '40422', '15.5', 'I-1'),
    (200007, '7200 Highway 150 West', 'Danville', 'KY', '40422', '25.0', 'I-2'),
    (200008, '8900 Danville Bypass', 'Danville', 'KY', '40422', '10.2', 'B-3'),
    (200009, '9100 Perryville Road', 'Danville', 'KY', '40422', '50.0', 'AG'),
    (200010, '1025 Stanford Road', 'Danville', 'KY', '40422', '3.7', 'R-2'),
    (200011, '1200 Fourth Street', 'Danville', 'KY', '40422', '0.5', 'R-3'),
    (200012, '1350 Lexington Avenue', 'Danville', 'KY', '40422', '8.9', 'PUD');

-- -------------------------------------------------------------------------
-- SECTION 3: PROFESSIONAL CONTACTS
-- -------------------------------------------------------------------------

-- Surveyors
INSERT INTO surveyors (surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell) VALUES 
    ('James', 'Richardson', 'Richardson Land Surveying Inc', 'jrichardson@richardsonsurvey.com', '859-236-5100', '859-555-0101'),
    ('Patricia', 'Coleman', 'Coleman & Associates Surveying', 'pcoleman@colemansurvey.com', '859-236-5200', '859-555-0201'),
    ('Michael', 'Bennett', 'Precision Survey Solutions', 'mbennett@precisionsurveysolutions.com', '859-236-5300', '859-555-0301'),
    ('Linda', 'Gray', 'Gray Land Surveying LLC', 'lgray@graylandsurvey.com', '859-236-5400', '859-555-0401'),
    ('Robert', 'Powell', 'Powell Survey Group', 'rpowell@powellsurveygroup.com', '859-236-5500', '859-555-0501');

-- Engineers
INSERT INTO engineers (engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell) VALUES 
    ('David', 'Henderson', 'Henderson Engineering Associates', 'dhenderson@hendersoneng.com', '859-236-6100', '859-555-0601'),
    ('Barbara', 'Jenkins', 'Jenkins Civil Engineering', 'bjenkins@jenkinscivileng.com', '859-236-6200', '859-555-0701'),
    ('William', 'Perry', 'Perry & Associates PE', 'wperry@perryassociates.com', '859-236-6300', '859-555-0801'),
    ('Elizabeth', 'Russell', 'Russell Engineering Solutions', 'erussell@russellengsolutions.com', '859-236-6400', '859-555-0901'),
    ('Richard', 'Griffin', 'Griffin Design Group', 'rgriffin@griffindesigngroup.com', '859-236-6500', '859-555-1001');

-- Contractors
INSERT INTO contractors (contractor_first_name, contractor_last_name, contractor_law_firm, contractor_email, contractor_phone, contractor_cell) VALUES 
    ('John', 'Alexander', 'Alexander Construction Company', 'jalexander@alexanderconstruction.com', '859-236-7100', '859-555-1101'),
    ('Mary', 'Butler', 'Butler Builders LLC', 'mbutler@butlerbuilders.com', '859-236-7200', '859-555-1201'),
    ('Charles', 'Simmons', 'Simmons Development Corp', 'csimmons@simmonsdevelopment.com', '859-236-7300', '859-555-1301'),
    ('Nancy', 'Foster', 'Foster Construction Group', 'nfoster@fosterconstructiongroup.com', '859-236-7400', '859-555-1401'),
    ('Thomas', 'Bryant', 'Bryant Building Solutions', 'tbryant@bryantbuilding.com', '859-236-7500', '859-555-1501');

-- Architects
INSERT INTO architects (architect_first_name, architect_last_name, architect_law_firm, architect_email, architect_phone, architect_cell) VALUES 
    ('Steven', 'Patterson', 'Patterson Architecture Studio', 'spatterson@pattersonarch.com', '859-236-8100', '859-555-1601'),
    ('Susan', 'Hughes', 'Hughes Design Architects', 'shughes@hughesdesignarch.com', '859-236-8200', '859-555-1701'),
    ('Daniel', 'Washington', 'Washington Architectural Group', 'dwashington@washingtonarchgroup.com', '859-236-8300', '859-555-1801'),
    ('Carol', 'Kelly', 'Kelly & Associates Architects', 'ckelly@kellyarchitects.com', '859-236-8400', '859-555-1901'),
    ('Joseph', 'Barnes', 'Barnes Architecture PLLC', 'jbarnes@barnesarchpllc.com', '859-236-8500', '859-555-2001');

-- Land Architects
INSERT INTO land_architects (land_architect_first_name, land_architect_last_name, land_architect_law_firm, land_architect_email, land_architect_phone, land_architect_cell) VALUES 
    ('Kevin', 'Ross', 'Ross Landscape Architecture', 'kross@rosslandarch.com', '859-236-9100', '859-555-2101'),
    ('Dorothy', 'Howard', 'Howard Land Design Studio', 'dhoward@howardlanddesign.com', '859-236-9200', '859-555-2201'),
    ('Paul', 'Ward', 'Ward Environmental Design', 'pward@wardenvironmental.com', '859-236-9300', '859-555-2301'),
    ('Helen', 'Cooper', 'Cooper Site Planning & Design', 'hcooper@coopersiteplanning.com', '859-236-9400', '859-555-2401');

-- Attorneys
INSERT INTO attorneys (attorney_first_name, attorney_last_name, attorney_law_firm, attorney_email, attorney_phone, attorney_cell) VALUES 
    ('George', 'Morgan', 'Morgan Law Office PLLC', 'gmorgan@morganlawoffice.com', '859-236-9500', '859-555-2501'),
    ('Margaret', 'Reed', 'Reed Legal Services', 'mreed@reedlegalservices.com', '859-236-9600', '859-555-2601'),
    ('Kenneth', 'Bailey', 'Bailey & Associates Law Firm', 'kbailey@baileyassociates.com', '859-236-9700', '859-555-2701'),
    ('Betty', 'Rivera', 'Rivera Law Group', 'brivera@riveralawgroup.com', '859-236-9800', '859-555-2801');

-- -------------------------------------------------------------------------
-- SECTION 4: CLIENTS
-- -------------------------------------------------------------------------

INSERT INTO clients (client_username) VALUES 
    ('developer_smith'),
    ('contractor_jones'),
    ('property_owner_davis'),
    ('architect_wilson'),
    ('business_owner_brown'),
    ('resident_taylor'),
    ('attorney_martin');

-- -------------------------------------------------------------------------
-- SECTION 5: APPLICANTS (Various Types)
-- -------------------------------------------------------------------------

-- Type One Applicants
INSERT INTO type_one_applicants (t1_applicant_first_name, t1_applicant_last_name, t1_applicant_street_address, t1_applicant_city, state_code, t1_applicant_zip_code, t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email) VALUES 
    ('Amanda', 'Thompson', '1234 Walnut Street', 'Danville', 'KY', '40422', '859-555-3000', '859-555-3001', 'athompson@email.com'),
    ('Brian', 'Martinez', '2345 Cherry Lane', 'Danville', 'KY', '40422', '859-555-3100', '859-555-3101', 'bmartinez@email.com'),
    ('Christine', 'Anderson', '3456 Birch Avenue', 'Danville', 'KY', '40422', '859-555-3200', '859-555-3201', 'canderson@email.com'),
    ('Dennis', 'White', '4567 Pine Court', 'Danville', 'KY', '40422', '859-555-3300', '859-555-3301', 'dwhite@email.com'),
    ('Emily', 'Harris', '5678 Cedar Drive', 'Danville', 'KY', '40422', '859-555-3400', '859-555-3401', 'eharris@email.com'),
    ('Frank', 'Clark', '6789 Spruce Road', 'Danville', 'KY', '40422', '859-555-3500', '859-555-3501', 'fclark@email.com'),
    ('Grace', 'Lewis', '7890 Poplar Street', 'Danville', 'KY', '40422', '859-555-3600', '859-555-3601', 'glewis@email.com'),
    ('Henry', 'Robinson', '8901 Willow Way', 'Danville', 'KY', '40422', '859-555-3700', '859-555-3701', 'hrobinson@email.com');

-- Type One Executives
INSERT INTO type_one_execs (t1e_exec_first_name, t1e_exec_last_name) VALUES 
    ('Victor', 'Campbell'),
    ('Alice', 'Mitchell'),
    ('Oscar', 'Carter'),
    ('Rachel', 'Phillips');

-- Link Executives to Applicants
INSERT INTO type_one_applicant_execs (t1e_exec_id, t1_applicant_id) VALUES 
    (1, 1),
    (1, 2),
    (2, 3),
    (3, 4),
    (4, 5);

-- Type One Owners
INSERT INTO type_one_owners (t1o_owner_first_name, t1o_owner_last_name) VALUES 
    ('Lawrence', 'Evans'),
    ('Diane', 'Turner'),
    ('Gerald', 'Torres'),
    ('Virginia', 'Parker');

-- ZVA Applicants
INSERT INTO zva_applicants (zva_applicant_first_name, zva_applicant_last_name, zva_applicant_street, zva_applicant_city, state_code, zva_applicant_zip_code, zva_applicant_phone_number, zva_applicant_fax_number) VALUES 
    ('Marcus', 'Edwards', '111 Application Lane', 'Danville', 'KY', '40422', '859-555-4000', '859-555-4001'),
    ('Laura', 'Collins', '222 Request Avenue', 'Danville', 'KY', '40422', '859-555-4100', '859-555-4101'),
    ('Nathan', 'Stewart', '333 Verification Drive', 'Danville', 'KY', '40422', '859-555-4200', '859-555-4201');

-- ZVA Property Owners
INSERT INTO zva_property_owners (zva_owner_first_name, zva_owner_last_name, zva_owner_street, zva_owner_city, state_code, zva_owner_zip_code) VALUES 
    ('Peter', 'Morris', '444 Owner Boulevard', 'Danville', 'KY', '40422'),
    ('Sandra', 'Rogers', '555 Property Circle', 'Danville', 'KY', '40422'),
    ('Walter', 'Reed', '666 Parcel Parkway', 'Danville', 'KY', '40422');

-- ORR Applicants
INSERT INTO orr_applicants (orr_applicant_first_name, orr_applicant_last_name, orr_applicant_telephone, orr_applicant_street, orr_applicant_city, state_code, orr_applicant_zip_code) VALUES 
    ('Timothy', 'Cook', '859-555-5000', '777 Records Lane', 'Danville', 'KY', '40422'),
    ('Michelle', 'Bell', '859-555-5100', '888 Information Street', 'Danville', 'KY', '40422'),
    ('Andrew', 'Murphy', '859-555-5200', '999 Document Drive', 'Danville', 'KY', '40422');

-- AAR Appellants
INSERT INTO aar_appellants (aar_first_name, aar_last_name) VALUES 
    ('Raymond', 'Price'),
    ('Deborah', 'Bennett'),
    ('Keith', 'Wood'),
    ('Joyce', 'Barnes');

-- AAR Property Owners
INSERT INTO aar_property_owners (aar_property_owner_name) VALUES 
    ('Bluegrass Development LLC'),
    ('Heritage Properties Inc'),
    ('Southside Holdings Group'),
    ('Main Street Investors');

-- Sign Permit Property Owners
INSERT INTO sp_property_owners (sp_owner_first_name, sp_owner_last_name, sp_owner_street, sp_owner_city, state_code, sp_owner_zip_code) VALUES 
    ('Albert', 'Long', '1111 Business Row', 'Danville', 'KY', '40422'),
    ('Gloria', 'Richardson', '1212 Commercial Drive', 'Danville', 'KY', '40422'),
    ('Arthur', 'Cox', '1313 Enterprise Boulevard', 'Danville', 'KY', '40422');

-- Sign Permit Businesses
INSERT INTO sp_businesses (sp_business_name, sp_business_street, sp_business_city, state_code, sp_business_zip_code) VALUES 
    ('Danville Coffee & Bakery', '4500 Main Street', 'Danville', 'KY', '40422'),
    ('Kentucky Auto Service Center', '5100 Commerce Parkway', 'Danville', 'KY', '40422'),
    ('Bluegrass Medical Clinic', '1025 Stanford Road', 'Danville', 'KY', '40422'),
    ('Pioneer Hardware & Supply', '8900 Danville Bypass', 'Danville', 'KY', '40422'),
    ('Heritage Restaurant Group', '1200 Fourth Street', 'Danville', 'KY', '40422');

-- Sign Permit Contractors
INSERT INTO sp_contractors (sp_contractor_first_name, sp_contractor_last_name, sp_contractor_phone_number) VALUES 
    ('Vincent', 'Howard', '859-555-6000'),
    ('Angela', 'Ward', '859-555-6100'),
    ('Russell', 'Torres', '859-555-6200');

-- Adjacent Property Owners
INSERT INTO adjacent_property_owners (adjacent_property_owner_street, adjacent_property_owner_city, state_code, adjacent_property_owner_zip) VALUES 
    ('2748 Oak Avenue', 'Danville', 'KY', '40422'),
    ('2752 Oak Avenue', 'Danville', 'KY', '40422'),
    ('2746 Oak Avenue', 'Danville', 'KY', '40422'),
    ('2750 Oak Avenue (Rear)', 'Danville', 'KY', '40422'),
    ('9098 Perryville Road', 'Danville', 'KY', '40422'),
    ('9102 Perryville Road', 'Danville', 'KY', '40422');

-- APOF Neighbors
INSERT INTO apof_neighbors (PVA_map_code, apof_neighbor_property_location, apof_neighbor_property_street, apof_neighbor_property_city, state_code, apof_neighbor_property_zip, apof_neighbor_property_deed_book, apof_property_street_pg_number) VALUES 
    ('MAP-42-A-001', 'North', 2748.00, 'Danville', 'KY', '40422', 145.00, '125'),
    ('MAP-42-A-002', 'South', 2752.00, 'Danville', 'KY', '40422', 145.00, '126'),
    ('MAP-42-A-003', 'East', 2746.00, 'Danville', 'KY', '40422', 146.00, '75'),
    ('MAP-42-A-004', 'West', 2754.00, 'Danville', 'KY', '40422', 146.00, '76'),
    ('MAP-42-B-001', 'North', 9098.00, 'Danville', 'KY', '40422', 152.00, '200'),
    ('MAP-42-B-002', 'South', 9102.00, 'Danville', 'KY', '40422', 152.00, '201'),
    ('MAP-42-B-003', 'East', 9096.00, 'Danville', 'KY', '40422', 153.00, '50'),
    ('MAP-42-B-004', 'West', 9104.00, 'Danville', 'KY', '40422', 153.00, '51');

-- -------------------------------------------------------------------------
-- SECTION 6: BASE FORMS
-- -------------------------------------------------------------------------

INSERT INTO forms (form_type, form_datetime_submitted) VALUES 
    ("Development Plan Application (General)", '2024-10-10'),
    ("Administrative Appeal Request", '2024-10-10'),
    ("Variance Applicatioin", '2024-10-10'),
    ("Zoning Map Amendment Application", '2024-10-10'),
    ("Zoning Permit Application", '2024-10-10'),
    ("Development Plan Application (General)", '2024-10-10'),
    ("Adjacent Property Owners Form", '2024-10-10'),
    ("Conditional Use Permit Application", '2024-10-10'),
    ("Development Plan Application (General)", '2024-10-10'),
    ("Minor Subdivision Plat Application", '2024-10-10'),
    ("Telecommunication Tower Uniform Application", '2024-10-10'),
    ("Development Plan Application (Site)", '2024-10-10'),
    ("Future Land Use Map (FLUM) Application", '2024-10-10'),
    ("Open Records Request", '2024-10-10'),
    ("Open Records Request", '2024-10-10'),
    ("Sign Permit Appplication", '2024-10-10'),
    ("Major Subdivision Plat Application", '2024-10-10'),
    ("Minor Subdivision Plat Application", '2024-10-10'),
    ("Telecommunication Tower Uniform Application", '2024-10-10'),
    ("Variance Applicatioin", '2024-10-10'),
    ("Zoning Map Amendment Application", '2024-10-10'),
    ("Zoning Permit Application", '2024-10-10'),
    ("Development Plan Application (General)", '2024-10-10'),
    ("Development Plan Application (Site)", '2024-10-10'),
    ("Zoning Verification Application", '2024-10-10');

-- -------------------------------------------------------------------------
-- SECTION 7: LINK APPLICANTS AND CLIENTS TO FORMS
-- -------------------------------------------------------------------------

-- Link Type One Applicants to Forms
INSERT INTO applicants_link_forms (t1_applicant_id, form_id) VALUES 
    (1, 2),
    (2, 3),
    (3, 4),
    (4, 5),
    (5, 6),
    (6, 7),
    (7, 9),
    (8, 14);

-- Link Clients to Complete Forms
INSERT INTO client_forms (form_id, client_id) VALUES 
    (1, 1),
    (2, 1),
    (4, 2),
    (6, 3),
    (10, 4),
    (12, 5),
    (13, 6);

-- Link Clients to Incomplete Forms
INSERT INTO incomplete_client_forms (form_id, client_id) VALUES 
    (3, 2),
    (5, 3),
    (7, 1);

-- Type One Forms
INSERT INTO type_one_forms (form_id) VALUES 
    (1),
    (2),
    (3),
    (4),
    (18);

-- -------------------------------------------------------------------------
-- SECTION 8: SPECIFIC APPLICATION FORMS
-- -------------------------------------------------------------------------

-- Zoning Verification Letters
INSERT INTO zoning_verification_letter (form_id, zva_letter_content, zva_zoning_letter_street, zva_state_code, zva_zoning_letter_city, zva_zoning_letter_zip, zva_property_street, zva_property_state_code, zva_property_zip, property_city) VALUES 
    (1, 'Verification that property is zoned R-1 Single Family Residential', '101 City Hall Plaza', 'KY', 'Danville', '40422', '1500 Maple Street', 'KY', '40422', 'Danville'),
    (18, 'Verification that property is zoned B-2 General Business', '101 City Hall Plaza', 'KY', 'Danville', '40422', '5100 Commerce Parkway', 'KY', '40422', 'Danville');

-- Major Subdivision Plat Applications
INSERT INTO major_subdivision_plat_applications (form_id, surveyor_id, engineer_id, PVA_parcel_number, mspa_topographic_survey, mspa_proposed_plot_layout, mspa_plat_restrictions, mspa_property_owner_convenants, mspa_association_covenants, mspa_master_deed, mspa_construction_plans, mspa_traffic_impact_study, mspa_geologic_study, mspa_drainage_plan, mspa_pavement_design, mspa_SWPPP_EPSC_plan, mspa_construction_bond_est) VALUES 
    (2, 1, 1, 200002, 'oak_ridge_topo_survey.pdf', 'oak_ridge_plot_layout.pdf', 'oak_ridge_restrictions.pdf', 'oak_ridge_owner_covenants.pdf', 'oak_ridge_hoa_covenants.pdf', 'oak_ridge_master_deed.pdf', 'oak_ridge_construction.pdf', 'oak_ridge_traffic_study.pdf', 'oak_ridge_geologic.pdf', 'oak_ridge_drainage.pdf', 'oak_ridge_pavement.pdf', 'oak_ridge_swppp.pdf', '$375000'),
    (14, 2, 3, 200009, 'perryville_estates_topo.pdf', 'perryville_estates_layout.pdf', 'perryville_estates_restrictions.pdf', 'perryville_estates_covenants.pdf', 'perryville_estates_hoa.pdf', 'perryville_estates_deed.pdf', 'perryville_estates_construction.pdf', 'perryville_estates_traffic.pdf', 'perryville_estates_geologic.pdf', 'perryville_estates_drainage.pdf', 'perryville_estates_pavement.pdf', 'perryville_estates_swppp.pdf', '$850000');

-- Minor Subdivision Plat Applications
INSERT INTO minor_subdivision_plat_applications (form_id, surveyor_id, engineer_id, PVA_parcel_number, minspa_topographic_survey, minspa_proposed_plot_layout, minspa_plat_restrictions, minspa_property_owner_covenants, minspa_association_covenants, minspa_master_deed) VALUES 
    (3, 3, 2, 200003, 'elm_drive_minor_topo.pdf', 'elm_drive_minor_layout.pdf', 'elm_drive_minor_restrictions.pdf', 'elm_drive_minor_covenants.pdf', NULL, 'elm_drive_minor_deed.pdf');

-- Zoning Permit Applications
INSERT INTO zoning_permit_applications (form_id, surveyor_id, architect_id, land_architect_id, contractor_id, PVA_parcel_number, project_type, zpa_project_plans, zpa_preliminary_site_evaluation) VALUES 
    (4, 1, 1, 1, 1, 200004, 'Commercial Use', 'professional_office_plans.pdf', 'main_street_site_eval.pdf'),
    (15, 4, 3, 2, 3, 200008, 'Temporary Use', 'retail_center_plans.pdf', 'bypass_site_evaluation.pdf');

-- Zoning Map Amendment Applications
INSERT INTO zoning_map_amendment_applications (form_id, zoning_map_amendment_request) VALUES 
    (5, 'Request to rezone from R-2 to R-3 for townhome development at 1025 Stanford Road');

-- General Development Plan Applications
INSERT INTO general_development_plan_applications (form_id, state_code, gdpa_applicant_zip, gdpa_applicant_phone, gdpa_plan_amendment_request, gdpa_proposed_conditions, required_findings_type, gdpa_concept_plan, gdpa_traffic_study, gdpa_geologic_analysis) VALUES 
    (6, 'KY', '40422', '859-555-7500', 'Conditional Use Permit Application', 'Maximum building height 50 feet, minimum 150 parking spaces, landscape buffer along residential boundary', 'petition_movement', 'mixed_use_concept_plan.pdf', 'mixed_use_traffic_study.pdf', 'mixed_use_geologic_analysis.pdf');

-- Variance Applications
INSERT INTO variance_applications (form_id, va_variance_request, va_proposed_conditions, PVA_parcel_number) VALUES 
    (7, 'Request variance for reduced front setback - 20 feet instead of required 30 feet', 'Install enhanced landscaping, decorative fence along front property line', 200001),
    (16, 'Request variance for increased lot coverage - 40% instead of 35% maximum', 'Provide additional stormwater management, green roof on portion of building', 200001);

-- Future Land Use Map Applications
INSERT INTO future_land_use_map_applications (form_id, future_land_use_map_amendment_prop, PVA_parcel_number) VALUES 
    (8, 'Change future land use from Low Density Residential to Medium Density Residential to allow multi-family development', 200010);

-- Conditional Use Permit Applications
INSERT INTO conditional_use_permit_applications (form_id, cupa_permit_request, cupa_proposed_conditions) VALUES 
    (9, 'Child daycare facility in R-2 residential zone', 'Operating hours 6:30 AM to 6:30 PM Monday-Friday only, maximum 60 children, secure outdoor play area, adequate off-street parking and drop-off/pick-up lanes'),
    (19, 'Home occupation - professional office in residence', 'No external signage, no retail sales, maximum 2 non-resident employees, adequate off-street parking');

-- Site Development Plan Applications
INSERT INTO site_development_plan_applications (form_id, surveyor_id, land_architect_id, engineer_id, architect_id, site_plan_request) VALUES 
    (10, 2, 3, 4, 2, 'Medical office complex with parking, landscaping, and stormwater management');

-- Administrative Appeal Requests
INSERT INTO administrative_appeal_requests (form_id, aar_hearing_date, aar_submit_date, aar_street_address, aar_city_address, state_code, aar_zip_code, aar_property_location, aar_official_decision, aar_relevant_provisions) VALUES 
    (11, '2025-12-10', '2025-11-05', '1200 Fourth Street', 'Danville', 'KY', '40422', '1200 Fourth Street', 'Denial of variance for fence height exceeding 6 feet in front yard', 'Zoning Ordinance Section 8.2.4 - Fence Regulations');

-- Link Appellants to Administrative Appeal
INSERT INTO administrative_appellants (form_id, aar_appellant_id) VALUES 
    (11, 1),
    (11, 2);

-- Link Property Owners to Administrative Appeal
INSERT INTO administrative_property_owners (form_id, aar_property_owner_id) VALUES 
    (11, 2);

-- Sign Permit Applications
INSERT INTO sign_permit_applications (form_id, sp_owner_id, contractor_id, sp_business_id, sp_date, sp_permit_number, sp_building_coverage_percent, sp_permit_fee) VALUES 
    (12, 1, 1, 1, '2025-10-15', 'SP-2025-0015', '3.5%', '$175.00'),
    (17, 2, 2, 4, '2025-10-22', 'SP-2025-0023', '6.2%', '$325.00');

-- Signs
INSERT INTO signs (sp_owner_id, sign_type, sign_square_footage, lettering_height) VALUES 
    (1, 'Wall-Mounted Channel Letters', 28.50, '14 inches'),
    (1, 'Freestanding Monument Sign', 42.00, '16 inches'),
    (2, 'Double-Faced Pole Sign', 96.00, '24 inches'),
    (3, 'Window Graphics', 18.00, '8 inches');

-- Link Signs to Permits
INSERT INTO permits_link_signs (form_id, sign_id) VALUES 
    (12, 1),
    (12, 2),
    (17, 3);

-- Open Record Requests
INSERT INTO open_record_requests (form_id, orr_commercial_purpose, orr_request_for_copies, orr_received_on_datetime, orr_receivable_datetime, orr_denied_reasons, orr_applicant_id) VALUES 
    (13, 'No', 'Yes', '2025-10-18', '2025-10-21', NULL, 1),
    (20, 'Yes', 'No', '2025-10-25', '2025-10-28', NULL, 2);

-- Link Public Records to Open Record Requests
INSERT INTO orr_public_record_names (form_id, public_record_id) VALUES 
    (13,1),
    (13, 2),
    (13, 3),
    (20, 4),
    (20, 5);

-- -------------------------------------------------------------------------
-- SECTION 9: TECHNICAL PROCESSING FORMS
-- -------------------------------------------------------------------------

INSERT INTO technical_forms (form_id, technical_app_filing_date, technical_review_date, technical_prelim_approval_date, technical_final_approval_date) VALUES 
    (2, '2025-08-15', '2025-09-01', '2025-09-20', NULL),
    (3, '2025-09-05', '2025-09-12', '2025-09-18', '2025-10-10'),
    (4, '2025-07-20', '2025-08-05', '2025-08-25', '2025-09-15'),
    (14, '2025-09-10', '2025-09-25', NULL, NULL),
    (15, '2025-10-01', '2025-10-10', NULL, NULL);

-- Hearing Forms
INSERT INTO hearing_forms (form_id, hearing_docket_number, hearing_date_application_filed, hearing_date, hearing_preapp_meeting_date, attorney_id) VALUES 
    (5, 'ZMA-2025-008', '2025-09-20', '2025-11-14', '2025-08-30', 1),
    (6, 'GDP-2025-003', '2025-08-25', '2025-11-07', '2025-07-28', 2),
    (7, 'VAR-2025-012', '2025-10-05', '2025-12-05', '2025-09-15', 3),
    (16, 'VAR-2025-015', '2025-10-12', '2025-12-12', '2025-09-22', 1);

-- -------------------------------------------------------------------------
-- SECTION 10: SUPPORTING DOCUMENTS
-- -------------------------------------------------------------------------

-- LDS Plans
INSERT INTO LDS_plans (form_id, LDS_plan_file) VALUES 
    (2, 'oak_ridge_subdivision_lds_plan.pdf'),
    (14, 'perryville_estates_lds_plan.pdf'),
    (3, 'elm_drive_subdivision_lds_plan.pdf');

-- Structures
INSERT INTO structures (form_id, structure_type, structure_square_feet, structure_project_value, structrure_notes) VALUES 
    (4, 'Two-Story Office Building', 12500.00, '$2800000', 'Professional office complex with ground floor retail space'),
    (4, 'Surface Parking Lot', 35000.00, '$450000', '125 parking spaces with landscaped islands'),
    (15, 'Retail Strip Center', 22000.00, '$3500000', 'Five tenant spaces ranging from 3,000-6,000 sq ft each'),
    (15, 'Detention Basin', 8500.00, '$125000', 'Stormwater management facility with native landscaping'),
    (10, 'Medical Office Building', 18000.00, '$4200000', 'Three-story building with elevator, ground floor imaging center');

-- WSF Verifications
INSERT INTO WSF_verifications (form_id, WSF_verification_file) VALUES 
    (2, 'oak_ridge_wsf_verification.pdf'),
    (3, 'elm_drive_wsf_verification.pdf'),
    (14, 'perryville_estates_wsf_verification.pdf');

-- -------------------------------------------------------------------------
-- SECTION 11: ADJACENT PROPERTIES AND NEIGHBORS
-- -------------------------------------------------------------------------

-- Link Neighbors to Forms (for subdivision applications)
INSERT INTO adjacent_neighbors (form_id, neighbor_id) VALUES 
    (2, 1),
    (2, 2),
    (2, 3),
    (2, 4),
    (14, 5),
    (14, 6),
    (14, 7),
    (14, 8);

-- Adjacent Property Owner Forms
INSERT INTO adjacent_property_owner_forms (form_id) VALUES 
    (2),
    (14);

-- Link Adjacent Property Owners to Forms
INSERT INTO adjacent_neighbor_owners (form_id, adjacent_property_owner_id) VALUES 
    (2, 1),
    (2, 2),
    (2, 3),
    (2, 4),
    (14, 5),
    (14, 6);

-- -------------------------------------------------------------------------
-- SECTION 12: DEPARTMENT INTERACTIONS
-- -------------------------------------------------------------------------

INSERT INTO department_form_interactions (department_id, form_id, department_form_interaction_description) VALUES 
    (1, 2, 'Initial zoning compliance review completed - subdivision meets R-2 zoning requirements'),
    (2, 2, 'Engineering review in progress - reviewing drainage calculations and road design'),
    (3, 2, 'Building code review pending - awaiting final construction drawings'),
    (4, 4, 'Fire Marshal approved - adequate fire access roads and hydrant locations provided'),
    (5, 4, 'Health Department approved - water and sewer capacity confirmed'),
    (1, 5, 'Staff report prepared recommending approval with conditions - public hearing scheduled'),
    (6, 5, 'Legal review completed - proposed rezoning meets procedural requirements'),
    (1, 6, 'Comprehensive plan consistency analysis completed - project aligns with future land use goals'),
    (2, 6, 'Traffic study review in progress - additional turn lanes may be required'),
    (1, 7, 'Variance request under review - hardship evaluation pending site visit'),
    (1, 10, 'Site plan meets zoning requirements - parking and landscaping calculations verified'),
    (2, 10, 'Stormwater management plan approved with conditions'),
    (3, 12, 'Sign permit approved - meets size and setback requirements'),
    (1, 14, 'Pre-application meeting completed - discussed phasing and infrastructure requirements'),
    (2, 14, 'Preliminary engineering review - road connectivity and utility extension plans submitted'),
    (1, 15, 'Commercial site plan review initiated - checking compliance with architectural standards');

-- =========================================================================
-- VERIFICATION QUERIES
-- =========================================================================

-- Count all records by table
SELECT 'Table Record Counts' AS report_section;
SELECT 'form_types' AS table_name, COUNT(*) AS record_count FROM form_types
UNION ALL SELECT 'project_types', COUNT(*) FROM project_types
UNION ALL SELECT 'properties', COUNT(*) FROM properties
UNION ALL SELECT 'forms', COUNT(*) FROM forms
UNION ALL SELECT 'surveyors', COUNT(*) FROM surveyors
UNION ALL SELECT 'engineers', COUNT(*) FROM engineers
UNION ALL SELECT 'contractors', COUNT(*) FROM contractors
UNION ALL SELECT 'architects', COUNT(*) FROM architects
UNION ALL SELECT 'land_architects', COUNT(*) FROM land_architects
UNION ALL SELECT 'attorneys', COUNT(*) FROM attorneys
UNION ALL SELECT 'type_one_applicants', COUNT(*) FROM type_one_applicants
UNION ALL SELECT 'clients', COUNT(*) FROM clients
UNION ALL SELECT 'major_subdivision_plat_applications', COUNT(*) FROM major_subdivision_plat_applications
UNION ALL SELECT 'minor_subdivision_plat_applications', COUNT(*) FROM minor_subdivision_plat_applications
UNION ALL SELECT 'zoning_permit_applications', COUNT(*) FROM zoning_permit_applications
UNION ALL SELECT 'variance_applications', COUNT(*) FROM variance_applications
UNION ALL SELECT 'sign_permit_applications', COUNT(*) FROM sign_permit_applications
UNION ALL SELECT 'signs', COUNT(*) FROM signs
UNION ALL SELECT 'structures', COUNT(*) FROM structures
UNION ALL SELECT 'departments', COUNT(*) FROM departments
UNION ALL SELECT 'department_form_interactions', COUNT(*) FROM department_form_interactions
ORDER BY record_count DESC;

-- =========================================================================
-- SAMPLE QUERIES FOR DATA VALIDATION AND REPORTING
-- =========================================================================

-- Query 1: All forms with applicant information
SELECT 
    f.form_id,
    f.form_type,
    CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) AS applicant_name,
    t1a.t1_applicant_email,
    t1a.t1_applicant_phone_number
FROM forms f
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
ORDER BY f.form_id;

-- Query 2: Major subdivisions with full professional team and property details
SELECT 
    f.form_id,
    mspa.PVA_parcel_number,
    p.property_street_address,
    p.property_acreage,
    p.property_current_zoning,
    CONCAT(s.surveyor_first_name, ' ', s.surveyor_last_name) AS surveyor_name,
    s.surveyor_firm,
    CONCAT(e.engineer_first_name, ' ', e.engineer_last_name) AS engineer_name,
    e.engineer_firm,
    mspa.mspa_construction_bond_est,
    tf.technical_app_filing_date,
    tf.technical_prelim_approval_date
FROM major_subdivision_plat_applications mspa
JOIN forms f ON mspa.form_id = f.form_id
LEFT JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN surveyors s ON mspa.surveyor_id = s.surveyor_id
LEFT JOIN engineers e ON mspa.engineer_id = e.engineer_id
LEFT JOIN technical_forms tf ON f.form_id = tf.form_id
ORDER BY f.form_id;

-- Query 3: Zoning permit applications with structures and project value
SELECT 
    f.form_id,
    zpa.project_type,
    p.property_street_address,
    CONCAT(a.architect_first_name, ' ', a.architect_last_name) AS architect_name,
    CONCAT(c.contractor_first_name, ' ', c.contractor_last_name) AS contractor_name,
    s.structure_type,
    s.structure_square_feet,
    s.structure_project_value
FROM zoning_permit_applications zpa
JOIN forms f ON zpa.form_id = f.form_id
LEFT JOIN properties p ON zpa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN architects a ON zpa.architect_id = a.architect_id
LEFT JOIN contractors c ON zpa.contractor_id = c.contractor_id
LEFT JOIN structures s ON f.form_id = s.form_id
ORDER BY f.form_id, s.structure_id;

-- Query 4: Upcoming hearings schedule
SELECT 
    hf.hearing_docket_number,
    f.form_type,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    CONCAT(att.attorney_first_name, ' ', att.attorney_last_name) AS attorney_name,
    att.attorney_law_firm,
    CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) AS applicant_name
FROM hearing_forms hf
JOIN forms f ON hf.form_id = f.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
WHERE hf.hearing_date >= CURDATE()
ORDER BY hf.hearing_date;

-- Query 5: Sign permits with business information
SELECT 
    spa.form_id,
    spa.sp_permit_number,
    spa.sp_date,
    spa.sp_permit_fee,
    spb.sp_business_name,
    spb.sp_business_street,
    CONCAT(spo.sp_owner_first_name, ' ', spo.sp_owner_last_name) AS owner_name,
    s.sign_type,
    s.sign_square_footage,
    s.lettering_height
FROM sign_permit_applications spa
LEFT JOIN sp_businesses spb ON spa.sp_business_id = spb.sp_business_id
LEFT JOIN sp_property_owners spo ON spa.sp_owner_id = spo.sp_owner_id
LEFT JOIN permits_link_signs pls ON spa.form_id = pls.form_id
LEFT JOIN signs s ON pls.sign_id = s.sign_id
ORDER BY spa.sp_date DESC;

-- Query 6: Open record requests with requested documents
SELECT 
    orr.form_id,
    orr.orr_received_on_datetime,
    orr.orr_commercial_purpose,
    CONCAT(orra.orr_applicant_first_name, ' ', orra.orr_applicant_last_name) AS requestor_name,
    orra.orr_applicant_telephone,
    pr.public_record_id,
    pr.public_record_description
FROM open_record_requests orr
LEFT JOIN orr_applicants orra ON orr.orr_applicant_id = orra.orr_applicant_id
LEFT JOIN orr_public_record_names oprn ON orr.form_id = oprn.form_id
LEFT JOIN public_records pr ON oprn.public_record_id = pr.public_record_id
ORDER BY orr.orr_received_on_datetime DESC;

-- Query 7: Forms by processing status
SELECT 
    f.form_id,
    f.form_type,
    tf.technical_app_filing_date,
    tf.technical_review_date,
    tf.technical_prelim_approval_date,
    tf.technical_final_approval_date,
    CASE 
        WHEN tf.technical_final_approval_date IS NOT NULL THEN 'Final Approved'
        WHEN tf.technical_prelim_approval_date IS NOT NULL THEN 'Preliminary Approved'
        WHEN tf.technical_review_date IS NOT NULL THEN 'Under Review'
        WHEN tf.technical_app_filing_date IS NOT NULL THEN 'Filed'
        ELSE 'Not Started'
    END AS status,
    DATEDIFF(COALESCE(tf.technical_final_approval_date, CURDATE()), tf.technical_app_filing_date) AS days_in_process
FROM forms f
LEFT JOIN technical_forms tf ON f.form_id = tf.form_id
WHERE tf.form_id IS NOT NULL
ORDER BY tf.technical_app_filing_date DESC;

-- Query 8: Department workload by form
SELECT 
    d.department_name,
    COUNT(DISTINCT dfi.form_id) AS forms_assigned,
    f.form_type,
    COUNT(*) AS interaction_count
FROM department_form_interactions dfi
JOIN departments d ON dfi.department_id = d.department_id
JOIN forms f ON dfi.form_id = f.form_id
GROUP BY d.department_name, f.form_type
ORDER BY d.department_name, interaction_count DESC;

-- Query 9: Client activity summary
SELECT 
    c.client_id,
    c.client_username,
    COUNT(DISTINCT cf.form_id) AS completed_forms,
    COUNT(DISTINCT icf.form_id) AS incomplete_forms,
    COUNT(DISTINCT cf.form_id) + COUNT(DISTINCT icf.form_id) AS total_forms
FROM clients c
LEFT JOIN client_forms cf ON c.client_id = cf.client_id
LEFT JOIN incomplete_client_forms icf ON c.client_id = icf.client_id
GROUP BY c.client_id, c.client_username
ORDER BY total_forms DESC;

-- Query 10: Adjacent properties for subdivisions
SELECT 
    f.form_id,
    f.form_type,
    p.property_street_address AS subdivision_address,
    apofn.PVA_map_code,
    apofn.apof_neighbor_property_location AS direction,
    apofn.apof_neighbor_property_street,
    apofn.apof_neighbor_property_city,
    apo.adjacent_property_owner_street
FROM forms f
JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id
JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN adjacent_neighbors an ON f.form_id = an.form_id
LEFT JOIN apof_neighbors apofn ON an.neighbor_id = apofn.neighbor_id
LEFT JOIN adjacent_neighbor_owners ano ON f.form_id = ano.form_id
LEFT JOIN adjacent_property_owners apo ON ano.adjacent_property_owner_id = apo.adjacent_property_owner_id
ORDER BY f.form_id, apofn.apof_neighbor_property_location;

-- Query 11: Variance applications with hearing information
SELECT 
    f.form_id,
    va.va_variance_request,
    va.va_proposed_conditions,
    hf.hearing_docket_number,
    hf.hearing_date,
    CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) AS applicant_name,
    CONCAT(att.attorney_first_name, ' ', att.attorney_last_name) AS attorney_name
FROM variance_applications va
JOIN forms f ON va.form_id = f.form_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
ORDER BY hf.hearing_date;

-- Query 12: Properties with multiple applications
SELECT 
    p.PVA_parcel_number,
    p.property_street_address,
    p.property_current_zoning,
    COUNT(DISTINCT all_apps.form_id) AS application_count,
    GROUP_CONCAT(DISTINCT all_apps.form_type ORDER BY all_apps.form_id SEPARATOR '; ') AS application_types
FROM properties p
LEFT JOIN (
    SELECT mspa.PVA_parcel_number, f.form_id, f.form_type 
    FROM major_subdivision_plat_applications mspa
    JOIN forms f ON mspa.form_id = f.form_id
    UNION ALL
    SELECT minspa.PVA_parcel_number, f.form_id, f.form_type 
    FROM minor_subdivision_plat_applications minspa
    JOIN forms f ON minspa.form_id = f.form_id
    UNION ALL
    SELECT zpa.PVA_parcel_number, f.form_id, f.form_type 
    FROM zoning_permit_applications zpa
    JOIN forms f ON zpa.form_id = f.form_id
    UNION ALL
    SELECT fluma.PVA_parcel_number, f.form_id, f.form_type 
    FROM future_land_use_map_applications fluma
    JOIN forms f ON fluma.form_id = f.form_id
) AS all_apps ON p.PVA_parcel_number = all_apps.PVA_parcel_number
GROUP BY p.PVA_parcel_number, p.property_street_address, p.property_current_zoning
HAVING application_count > 0
ORDER BY application_count DESC;

-- Query 13: Professional contact usage statistics
SELECT 
    'Surveyors' AS professional_type,
    CONCAT(s.surveyor_first_name, ' ', s.surveyor_last_name) AS name,
    s.surveyor_firm AS firm,
    COUNT(DISTINCT mspa.form_id) + COUNT(DISTINCT minspa.form_id) + COUNT(DISTINCT zpa.form_id) AS total_projects
FROM surveyors s
LEFT JOIN major_subdivision_plat_applications mspa ON s.surveyor_id = mspa.surveyor_id
LEFT JOIN minor_subdivision_plat_applications minspa ON s.surveyor_id = minspa.surveyor_id
LEFT JOIN zoning_permit_applications zpa ON s.surveyor_id = zpa.surveyor_id
GROUP BY s.surveyor_id, name, firm
UNION ALL
SELECT 
    'Engineers',
    CONCAT(e.engineer_first_name, ' ', e.engineer_last_name),
    e.engineer_firm,
    COUNT(DISTINCT mspa.form_id) + COUNT(DISTINCT minspa.form_id)
FROM engineers e
LEFT JOIN major_subdivision_plat_applications mspa ON e.engineer_id = mspa.engineer_id
LEFT JOIN minor_subdivision_plat_applications minspa ON e.engineer_id = minspa.engineer_id
GROUP BY e.engineer_id, CONCAT(e.engineer_first_name, ' ', e.engineer_last_name), e.engineer_firm
ORDER BY total_projects DESC, professional_type;

-- =========================================================================
-- END OF SAMPLE INSERT STATEMENTS
-- =========================================================================