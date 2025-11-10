/*
  form_insert_procedures.sql
  Auto-generated stored procedures for inserting each form type and related records
  Target: MariaDB / MySQL (uses DELIMITER and LAST_INSERT_ID())
  Note: Procedures insert into `forms` table first (with CURRENT_TIMESTAMP) and then
  insert into related tables using LAST_INSERT_ID() as form_id.
  Updated to handle insertion of related entities (surveyors, engineers, etc.) when IDs are NULL.
*/

/* ---------------------------
   1) Administrative Appeal Request
   Tables: forms, administrative_appeal_requests, administrative_appellants, administrative_property_owners
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_administrative_appeal_request(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- administrative_appeal_requests fields
  IN p_aar_hearing_date DATE,
  IN p_aar_submit_date DATE,
  IN p_aar_street_address VARCHAR(255),
  IN p_aar_city_address VARCHAR(255),
  IN p_state_code CHAR(2),
  IN p_aar_zip_code VARCHAR(50),
  IN p_aar_property_location VARCHAR(255),
  IN p_aar_official_decision VARCHAR(255),
  IN p_aar_relevant_provisions VARCHAR(255),
  -- single appellant (optional)
  IN p_aar_appellant_first_name VARCHAR(255),
  IN p_aar_appellant_last_name VARCHAR(255),
  -- single adjacent property owner (optional)
  IN p_adjacent_property_owner_street VARCHAR(255),
  IN p_adjacent_property_owner_city VARCHAR(255),
  IN p_adjacent_property_owner_state_code CHAR(2),
  IN p_adjacent_property_owner_zip VARCHAR(50),
  -- single property owner (optional)
  IN p_aar_property_owner_first_name VARCHAR(255),
  IN p_aar_property_owner_last_name VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Administrative Appeal Request', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO administrative_appeal_requests(
    form_id, aar_hearing_date, aar_submit_date, aar_street_address, aar_city_address,
    state_code, aar_zip_code, aar_property_location, aar_official_decision, aar_relevant_provisions
  ) VALUES (
    @new_form_id, p_aar_hearing_date, p_aar_submit_date, p_aar_street_address, p_aar_city_address,
    p_state_code, p_aar_zip_code, p_aar_property_location, p_aar_official_decision, p_aar_relevant_provisions
  );

  IF p_aar_appellant_first_name IS NOT NULL OR p_aar_appellant_last_name IS NOT NULL THEN
    INSERT INTO aar_appellants(aar_first_name, aar_last_name)
      VALUES(p_aar_appellant_first_name, p_aar_appellant_last_name);
    SET @new_appellant_id = LAST_INSERT_ID();
    INSERT INTO administrative_appellants(form_id, aar_appellant_id)
      VALUES(@new_form_id, @new_appellant_id);
  END IF;

  IF p_adjacent_property_owner_street IS NOT NULL OR p_adjacent_property_owner_city IS NOT NULL THEN
    INSERT INTO adjacent_property_owners(adjacent_property_owner_street, adjacent_property_owner_city, state_code, adjacent_property_owner_zip)
      VALUES(p_adjacent_property_owner_street, p_adjacent_property_owner_city, p_adjacent_property_owner_state_code, p_adjacent_property_owner_zip);
    SET @new_adj_owner_id = LAST_INSERT_ID();
    INSERT INTO adjacent_neighbor_owners(form_id, adjacent_property_owner_id)
      VALUES(@new_form_id, @new_adj_owner_id);
  END IF;

   -- ✅ New insert for property owner
  IF p_aar_property_owner_first_name IS NOT NULL OR p_aar_property_owner_last_name IS NOT NULL THEN
    INSERT INTO aar_property_owners(aar_property_owner_first_name, aar_property_owner_last_name)
      VALUES(p_aar_property_owner_first_name, p_aar_property_owner_last_name);
    SET @new_property_owner_id = LAST_INSERT_ID();

    INSERT INTO administrative_property_owners(form_id, aar_property_owner_id)
      VALUES(@new_form_id, @new_property_owner_id);
    END IF;

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   2) Adjacent Property Owners Form
   Tables: forms, adjacent_property_owner_forms, apof_neighbors, adjacent_neighbors
   Note: will insert one neighbor record if provided
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_adjacent_property_owners_form(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- adjacent_property_owner_forms has only form_id (auto from forms)
  -- apof_neighbors fields (optional)
  IN p_PVA_map_code VARCHAR(255),
  IN p_apof_neighbor_property_location VARCHAR(255),
  IN p_apof_neighbor_property_street VARCHAR(255),
  IN p_apof_neighbor_property_city VARCHAR(255),
  IN p_apof_state_code CHAR(2),
  IN p_apof_neighbor_property_zip VARCHAR(50),
  IN p_apof_neighbor_property_deed_book VARCHAR(50),
  IN p_apof_property_street_pg_number VARCHAR(255),
  -- adjacent_property_owners fields 
  IN p_adjacent_property_owner_street VARCHAR(255),
  IN p_adjacent_property_owner_city VARCHAR(255),
  IN p_adjacent_state_code CHAR(2),
  IN p_adjacent_property_owner_zip VARCHAR(50)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Adjacent Property Owners Form', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO adjacent_property_owner_forms(form_id) VALUES(@new_form_id);

  IF p_PVA_map_code IS NOT NULL OR p_apof_neighbor_property_location IS NOT NULL THEN
    INSERT INTO apof_neighbors(
      PVA_map_code, apof_neighbor_property_location, apof_neighbor_property_street,
      apof_neighbor_property_city, state_code, apof_neighbor_property_zip,
      apof_neighbor_property_deed_book, apof_property_street_pg_number
    ) VALUES (
      p_PVA_map_code, p_apof_neighbor_property_location, p_apof_neighbor_property_street,
      p_apof_neighbor_property_city, p_apof_state_code, p_apof_neighbor_property_zip,
      p_apof_neighbor_property_deed_book, p_apof_property_street_pg_number
    );
    SET @new_neighbor_id = LAST_INSERT_ID();
    INSERT INTO adjacent_neighbors(form_id, neighbor_id) VALUES(@new_form_id, @new_neighbor_id);
  END IF;

  -- Insert into adjacent_property_owners if data provided
  IF p_adjacent_property_owner_street IS NOT NULL OR p_adjacent_property_owner_city IS NOT NULL THEN
    INSERT INTO adjacent_property_owners(
      adjacent_property_owner_street, adjacent_property_owner_city, state_code, adjacent_property_owner_zip
    )
    VALUES (
      p_adjacent_property_owner_street, p_adjacent_property_owner_city, p_adjacent_state_code, p_adjacent_property_owner_zip
    );
    SET @new_owner_id = LAST_INSERT_ID();

    -- Link the property owner to the form
    INSERT INTO adjacent_neighbor_owners(form_id, adjacent_property_owner_id)
    VALUES(@new_form_id, @new_owner_id);
  END IF;

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   3) Conditional Use Permit Application
   Tables: forms, conditional_use_permit_applications
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_conditional_use_permit_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_cupa_permit_request VARCHAR(255),
  IN p_cupa_proposed_conditions VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Conditional Use Permit Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO conditional_use_permit_applications(form_id, cupa_permit_request, cupa_proposed_conditions)
    VALUES(@new_form_id, p_cupa_permit_request, p_cupa_proposed_conditions);

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   4) Development Plan Application (General)
   Tables: forms, general_development_plan_applications
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_general_development_plan_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_state_code CHAR(2),
  IN p_gdpa_applicant_zip VARCHAR(50),
  IN p_gdpa_applicant_phone VARCHAR(50),
  IN p_gdpa_plan_amendment_request VARCHAR(255),
  IN p_gdpa_proposed_conditions VARCHAR(255),
  IN p_required_findings_type VARCHAR(255),
  IN p_gdpa_concept_plan VARCHAR(255),
  IN p_gdpa_traffic_study VARCHAR(255),
  IN p_gdpa_geologic_analysis VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Development Plan Application (General)', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO general_development_plan_applications(
    form_id, state_code, gdpa_applicant_zip, gdpa_applicant_phone,
    gdpa_plan_amendment_request, gdpa_proposed_conditions, required_findings_type,
    gdpa_concept_plan, gdpa_traffic_study, gdpa_geologic_analysis
  ) VALUES (
    @new_form_id, p_state_code, p_gdpa_applicant_zip, p_gdpa_applicant_phone,
    p_gdpa_plan_amendment_request, p_gdpa_proposed_conditions, p_required_findings_type,
    p_gdpa_concept_plan, p_gdpa_traffic_study, p_gdpa_geologic_analysis
  );

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   5) Development Plan Application (Site)
   Tables: forms, site_development_plan_applications
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_site_development_plan_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- surveyor parameters
  IN p_surveyor_id INT,
  IN p_surveyor_first_name VARCHAR(255),
  IN p_surveyor_last_name VARCHAR(255),
  IN p_surveyor_firm VARCHAR(255),
  IN p_surveyor_email VARCHAR(255),
  IN p_surveyor_phone VARCHAR(50),
  IN p_surveyor_cell VARCHAR(255),
  -- land architect parameters
  IN p_land_architect_id INT,
  IN p_land_architect_first_name VARCHAR(255),
  IN p_land_architect_last_name VARCHAR(255),
  IN p_land_architect_law_firm VARCHAR(255),
  IN p_land_architect_email VARCHAR(255),
  IN p_land_architect_phone VARCHAR(50),
  IN p_land_architect_cell VARCHAR(255),
  -- engineer parameters
  IN p_engineer_id INT,
  IN p_engineer_first_name VARCHAR(255),
  IN p_engineer_last_name VARCHAR(255),
  IN p_engineer_firm VARCHAR(255),
  IN p_engineer_email VARCHAR(255),
  IN p_engineer_phone VARCHAR(50),
  IN p_engineer_cell VARCHAR(255),
  -- architect parameters
  IN p_architect_id INT,
  IN p_architect_first_name VARCHAR(255),
  IN p_architect_last_name VARCHAR(255),
  IN p_architect_law_firm VARCHAR(255),
  IN p_architect_email VARCHAR(255),
  IN p_architect_phone VARCHAR(50),
  IN p_architect_cell VARCHAR(255),
  -- application data
  IN p_site_plan_request VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Development Plan Application (Site)', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  -- Handle surveyor
  IF p_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET @insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_surveyor_id = NULL;
    END IF;
  ELSE
    SET @insert_surveyor_id = p_surveyor_id;
  END IF;

  -- Handle land architect
  IF p_land_architect_id IS NULL THEN
    IF p_land_architect_first_name IS NOT NULL OR p_land_architect_last_name IS NOT NULL THEN
      INSERT INTO land_architects(land_architect_first_name, land_architect_last_name, land_architect_law_firm, land_architect_email, land_architect_phone, land_architect_cell)
        VALUES(p_land_architect_first_name, p_land_architect_last_name, p_land_architect_law_firm, p_land_architect_email, p_land_architect_phone, p_land_architect_cell);
      SET @insert_land_architect_id = LAST_INSERT_ID();
    ELSE
      SET @insert_land_architect_id = NULL;
    END IF;
  ELSE
    SET @insert_land_architect_id = p_land_architect_id;
  END IF;

  -- Handle engineer
  IF p_engineer_id IS NULL THEN
    IF p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL THEN
      INSERT INTO engineers(engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell)
        VALUES(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
      SET @insert_engineer_id = LAST_INSERT_ID();
    ELSE
      SET @insert_engineer_id = NULL;
    END IF;
  ELSE
    SET @insert_engineer_id = p_engineer_id;
  END IF;

  -- Handle architect
  IF p_architect_id IS NULL THEN
    IF p_architect_first_name IS NOT NULL OR p_architect_last_name IS NOT NULL THEN
      INSERT INTO architects(architect_first_name, architect_last_name, architect_law_firm, architect_email, architect_phone, architect_cell)
        VALUES(p_architect_first_name, p_architect_last_name, p_architect_law_firm, p_architect_email, p_architect_phone, p_architect_cell);
      SET @insert_architect_id = LAST_INSERT_ID();
    ELSE
      SET @insert_architect_id = NULL;
    END IF;
  ELSE
    SET @insert_architect_id = p_architect_id;
  END IF;

  INSERT INTO site_development_plan_applications(form_id, surveyor_id, land_architect_id, engineer_id, architect_id, site_plan_request)
    VALUES(@new_form_id, @insert_surveyor_id, @insert_land_architect_id, @insert_engineer_id, @insert_architect_id, p_site_plan_request);

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   6) Future Land Use Map (FLUM) Application
   Tables: forms, future_land_use_map_applications
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_future_land_use_map_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_future_land_use_map_amendment_prop VARCHAR(255),
  IN p_PVA_parcel_number INT
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Future Land Use Map (FLUM) Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO future_land_use_map_applications(form_id, future_land_use_map_amendment_prop, PVA_parcel_number)
    VALUES(@new_form_id, p_future_land_use_map_amendment_prop, p_PVA_parcel_number);

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   7) Open Records Request
   Tables: forms, open_record_requests, orr_applicants, orr_public_record_names (via public_records)
   Note: For simplicity this procedure can insert one orr_applicant and optionally one public_record mapping.
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_open_records_request(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_orr_commercial_purpose VARCHAR(255),
  IN p_orr_request_for_copies VARCHAR(255),
  IN p_orr_received_on_datetime DATE,
  IN p_orr_receivable_datetime DATE,
  IN p_orr_denied_reasons TEXT,
  -- orr_applicant fields (optional)
  IN p_orr_applicant_first_name VARCHAR(255),
  IN p_orr_applicant_last_name VARCHAR(255),
  IN p_orr_applicant_telephone VARCHAR(50),
  IN p_orr_applicant_street VARCHAR(255),
  IN p_orr_applicant_city VARCHAR(255),
  IN p_orr_state_code CHAR(2),
  IN p_orr_applicant_zip_code VARCHAR(50),
  -- public record to map (optional)
  IN p_public_record_description TEXT
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Open Records Request', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  IF p_orr_applicant_first_name IS NOT NULL OR p_orr_applicant_last_name IS NOT NULL THEN
    INSERT INTO orr_applicants(
      orr_applicant_first_name, orr_applicant_last_name, orr_applicant_telephone,
      orr_applicant_street, orr_applicant_city, state_code, orr_applicant_zip_code
    ) VALUES (
      p_orr_applicant_first_name, p_orr_applicant_last_name, p_orr_applicant_telephone,
      p_orr_applicant_street, p_orr_applicant_city, p_orr_state_code, p_orr_applicant_zip_code
    );
    SET @new_orr_applicant_id = LAST_INSERT_ID();
  ELSE
    SET @new_orr_applicant_id = NULL;
  END IF;

  INSERT INTO open_record_requests(
    form_id, orr_commercial_purpose, orr_request_for_copies, orr_received_on_datetime,
    orr_receivable_datetime, orr_denied_reasons, orr_applicant_id
  ) VALUES (
    @new_form_id, p_orr_commercial_purpose, p_orr_request_for_copies, p_orr_received_on_datetime,
    p_orr_receivable_datetime, p_orr_denied_reasons, @new_orr_applicant_id
  );

  IF p_public_record_description IS NOT NULL THEN
    INSERT INTO public_records(public_record_description)
      VALUES(p_public_record_description);
    SET @new_public_record_id = LAST_INSERT_ID();
    INSERT INTO orr_public_record_names(form_id, public_record_id) VALUES(@new_form_id, @new_public_record_id);
  END IF;

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   8) Sign Permit Application
   Tables: forms, sign_permit_applications, sp_property_owners, sp_businesses, sp_contractors, signs, permits_link_signs
   This procedure supports inserting one owner, one business, one contractor, and one sign then linking them.
   --------------------------- */
DELIMITER $$
CREATE PROCEDURE sp_insert_sign_permit_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- sign_permit_applications fields
  IN p_sp_owner_id INT, -- optional: if provided will use existing owner id
  IN p_contractor_id INT, -- optional existing contractor
  IN p_sp_business_id INT, -- optional existing business
  IN p_sp_date DATE,
  IN p_sp_permit_number VARCHAR(255),
  IN p_sp_building_coverage_percent VARCHAR(255),
  IN p_sp_permit_fee VARCHAR(255),
  -- new owner fields (if p_sp_owner_id IS NULL)
  IN p_sp_owner_first_name VARCHAR(255),
  IN p_sp_owner_last_name VARCHAR(255),
  IN p_sp_owner_street VARCHAR(255),
  IN p_sp_owner_city VARCHAR(255),
  IN p_sp_owner_state_code CHAR(2),
  IN p_sp_owner_zip_code VARCHAR(50),
  -- new business fields (if p_sp_business_id IS NULL)
  IN p_sp_business_name VARCHAR(255),
  IN p_sp_business_street VARCHAR(255),
  IN p_sp_business_city VARCHAR(255),
  IN p_sp_business_state_code CHAR(2),
  IN p_sp_business_zip_code VARCHAR(50),
  -- new contractor fields (if p_contractor_id IS NULL)
  IN p_sp_contractor_first_name VARCHAR(255),
  IN p_sp_contractor_last_name VARCHAR(255),
  IN p_sp_contractor_phone_number VARCHAR(50),
  -- sign fields (optional)
  IN p_sign_type VARCHAR(255),
  IN p_sign_square_footage DECIMAL(12,2),
  IN p_lettering_height VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Sign Permit Appplication', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  -- owner
  IF p_sp_owner_id IS NULL THEN
    IF p_sp_owner_first_name IS NOT NULL OR p_sp_owner_last_name IS NOT NULL THEN
      INSERT INTO sp_property_owners(sp_owner_first_name, sp_owner_last_name, sp_owner_street, sp_owner_city, state_code, sp_owner_zip_code)
        VALUES(p_sp_owner_first_name, p_sp_owner_last_name, p_sp_owner_street, p_sp_owner_city, p_sp_owner_state_code, p_sp_owner_zip_code);
      SET @insert_owner_id = LAST_INSERT_ID();
    ELSE
      SET @insert_owner_id = NULL;
    END IF;
  ELSE
    SET @insert_owner_id = p_sp_owner_id;
  END IF;

  -- business
  IF p_sp_business_id IS NULL THEN
    IF p_sp_business_name IS NOT NULL THEN
      INSERT INTO sp_businesses(sp_business_name, sp_business_street, sp_business_city, state_code, sp_business_zip_code)
        VALUES(p_sp_business_name, p_sp_business_street, p_sp_business_city, p_sp_business_state_code, p_sp_business_zip_code);
      SET @insert_business_id = LAST_INSERT_ID();
    ELSE
      SET @insert_business_id = NULL;
    END IF;
  ELSE
    SET @insert_business_id = p_sp_business_id;
  END IF;

  -- contractor
  IF p_contractor_id IS NULL THEN
    IF p_sp_contractor_first_name IS NOT NULL OR p_sp_contractor_last_name IS NOT NULL THEN
      INSERT INTO sp_contractors(sp_contractor_first_name, sp_contractor_last_name, sp_contractor_phone_number)
        VALUES(p_sp_contractor_first_name, p_sp_contractor_last_name, p_sp_contractor_phone_number);
      SET @insert_contractor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_contractor_id = NULL;
    END IF;
  ELSE
    SET @insert_contractor_id = p_contractor_id;
  END IF;

  INSERT INTO sign_permit_applications(form_id, sp_owner_id, contractor_id, sp_business_id, sp_date, sp_permit_number, sp_building_coverage_percent, sp_permit_fee)
    VALUES(@new_form_id, @insert_owner_id, @insert_contractor_id, @insert_business_id, p_sp_date, p_sp_permit_number, p_sp_building_coverage_percent, p_sp_permit_fee);

  -- optional sign
  IF p_sign_type IS NOT NULL OR p_sign_square_footage IS NOT NULL THEN
    INSERT INTO signs(sp_owner_id, sign_type, sign_square_footage, lettering_height)
      VALUES(@insert_owner_id, p_sign_type, p_sign_square_footage, p_lettering_height);
    SET @new_sign_id = LAST_INSERT_ID();
    INSERT INTO permits_link_signs(form_id, sign_id) VALUES(@new_form_id, @new_sign_id);
  END IF;

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   9) Major Subdivision Plat Application
   Tables: forms, major_subdivision_plat_applications
   --------------------------- */

DELIMITER $$
CREATE PROCEDURE sp_insert_major_subdivision_plat_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- surveyor parameters
  IN p_surveyor_id INT,
  IN p_surveyor_first_name VARCHAR(255),
  IN p_surveyor_last_name VARCHAR(255),
  IN p_surveyor_firm VARCHAR(255),
  IN p_surveyor_email VARCHAR(255),
  IN p_surveyor_phone VARCHAR(50),
  IN p_surveyor_cell VARCHAR(255),
  -- engineer parameters
  IN p_engineer_id INT,
  IN p_engineer_first_name VARCHAR(255),
  IN p_engineer_last_name VARCHAR(255),
  IN p_engineer_firm VARCHAR(255),
  IN p_engineer_email VARCHAR(255),
  IN p_engineer_phone VARCHAR(50),
  IN p_engineer_cell VARCHAR(255),
  -- application data
  IN p_PVA_parcel_number INT,
  IN p_mspa_topographic_survey VARCHAR(255),
  IN p_mspa_proposed_plot_layout VARCHAR(255),
  IN p_mspa_plat_restrictions VARCHAR(255),
  IN p_mspa_property_owner_convenants VARCHAR(255),
  IN p_mspa_association_covenants VARCHAR(255),
  IN p_mspa_master_deed VARCHAR(255),
  IN p_mspa_construction_plans VARCHAR(255),
  IN p_mspa_traffic_impact_study VARCHAR(255),
  IN p_mspa_geologic_study VARCHAR(255),
  IN p_mspa_drainage_plan VARCHAR(255),
  IN p_mspa_pavement_design VARCHAR(255),
  IN p_mspa_SWPPP_EPSC_plan VARCHAR(255),
  IN p_mspa_construction_bond_est VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Major Subdivision Plat Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  -- Handle surveyor
  IF p_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET @insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_surveyor_id = NULL;
    END IF;
  ELSE
    SET @insert_surveyor_id = p_surveyor_id;
  END IF;

  -- Handle engineer
  IF p_engineer_id IS NULL THEN
    IF p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL THEN
      INSERT INTO engineers(engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell)
        VALUES(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
      SET @insert_engineer_id = LAST_INSERT_ID();
    ELSE
      SET @insert_engineer_id = NULL;
    END IF;
  ELSE
    SET @insert_engineer_id = p_engineer_id;
  END IF;

  INSERT INTO major_subdivision_plat_applications(
    form_id, surveyor_id, engineer_id, PVA_parcel_number, mspa_topographic_survey, mspa_proposed_plot_layout,
    mspa_plat_restrictions, mspa_property_owner_convenants, mspa_association_covenants, mspa_master_deed,
    mspa_construction_plans, mspa_traffic_impact_study, mspa_geologic_study, mspa_drainage_plan,
    mspa_pavement_design, mspa_SWPPP_EPSC_plan, mspa_construction_bond_est
  ) VALUES (
    @new_form_id, @insert_surveyor_id, @insert_engineer_id, p_PVA_parcel_number, p_mspa_topographic_survey, p_mspa_proposed_plot_layout,
    p_mspa_plat_restrictions, p_mspa_property_owner_convenants, p_mspa_association_covenants, p_mspa_master_deed,
    p_mspa_construction_plans, p_mspa_traffic_impact_study, p_mspa_geologic_study, p_mspa_drainage_plan,
    p_mspa_pavement_design, p_mspa_SWPPP_EPSC_plan, p_mspa_construction_bond_est
  );

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   10) Minor Subdivision Plat Application
   Tables: forms, minor_subdivision_plat_applications
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_minor_subdivision_plat_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- surveyor parameters
  IN p_surveyor_id INT,
  IN p_surveyor_first_name VARCHAR(255),
  IN p_surveyor_last_name VARCHAR(255),
  IN p_surveyor_firm VARCHAR(255),
  IN p_surveyor_email VARCHAR(255),
  IN p_surveyor_phone VARCHAR(50),
  IN p_surveyor_cell VARCHAR(255),
  -- engineer parameters
  IN p_engineer_id INT,
  IN p_engineer_first_name VARCHAR(255),
  IN p_engineer_last_name VARCHAR(255),
  IN p_engineer_firm VARCHAR(255),
  IN p_engineer_email VARCHAR(255),
  IN p_engineer_phone VARCHAR(50),
  IN p_engineer_cell VARCHAR(255),
  -- application data
  IN p_PVA_parcel_number INT,
  IN p_minspa_topographic_survey VARCHAR(255),
  IN p_minspa_proposed_plot_layout VARCHAR(255),
  IN p_minspa_plat_restrictions VARCHAR(255),
  IN p_minspa_property_owner_convenants VARCHAR(255),
  IN p_minspa_association_covenants VARCHAR(255),
  IN p_minspa_master_deed VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Minor Subdivision Plat Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  -- Handle surveyor
  IF p_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET @insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_surveyor_id = NULL;
    END IF;
  ELSE
    SET @insert_surveyor_id = p_surveyor_id;
  END IF;

  -- Handle engineer
  IF p_engineer_id IS NULL THEN
    IF p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL THEN
      INSERT INTO engineers(engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell)
        VALUES(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
      SET @insert_engineer_id = LAST_INSERT_ID();
    ELSE
      SET @insert_engineer_id = NULL;
    END IF;
  ELSE
    SET @insert_engineer_id = p_engineer_id;
  END IF;

  INSERT INTO minor_subdivision_plat_applications(
    form_id, surveyor_id, engineer_id, PVA_parcel_number, minspa_topographic_survey, minspa_proposed_plot_layout,
    minspa_plat_restrictions, minspa_property_owner_covenants, minspa_association_covenants, minspa_master_deed
  ) VALUES (
    @new_form_id, @insert_surveyor_id, @insert_engineer_id, p_PVA_parcel_number, p_minspa_topographic_survey, p_minspa_proposed_plot_layout,
    p_minspa_plat_restrictions, p_minspa_property_owner_convenants, p_minspa_association_covenants, p_minspa_master_deed
  );

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   11) Telecommunication Tower Uniform Application
   Tables: forms (schema contains no specific table for telecom tower)
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_telecommunication_tower_uniform_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Telecommunication Tower Uniform Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   12) Variance Application
   Tables: forms, variance_applications
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_variance_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_va_variance_request VARCHAR(255),
  IN p_va_proposed_conditions VARCHAR(255),
  IN p_PVA_parcel_number INT
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Variance Applicatioin', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO variance_applications(form_id, va_variance_request, va_proposed_conditions, PVA_parcel_number)
    VALUES(@new_form_id, p_va_variance_request, p_va_proposed_conditions, p_PVA_parcel_number);

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   13) Zoning Map Amendment Application
   Tables: forms, zoning_map_amendment_applications
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_zoning_map_amendment_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_zoning_map_amendment_request VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Zoning Map Amendment Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO zoning_map_amendment_applications(form_id, zoning_map_amendment_request)
    VALUES(@new_form_id, p_zoning_map_amendment_request);

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   14) Zoning Permit Application
   Tables: forms, zoning_permit_applications
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_zoning_permit_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  -- surveyor parameters
  IN p_surveyor_id INT,
  IN p_surveyor_first_name VARCHAR(255),
  IN p_surveyor_last_name VARCHAR(255),
  IN p_surveyor_firm VARCHAR(255),
  IN p_surveyor_email VARCHAR(255),
  IN p_surveyor_phone VARCHAR(50),
  IN p_surveyor_cell VARCHAR(255),
  -- architect parameters
  IN p_architect_id INT,
  IN p_architect_first_name VARCHAR(255),
  IN p_architect_last_name VARCHAR(255),
  IN p_architect_law_firm VARCHAR(255),
  IN p_architect_email VARCHAR(255),
  IN p_architect_phone VARCHAR(50),
  IN p_architect_cell VARCHAR(255),
  -- land architect parameters
  IN p_land_architect_id INT,
  IN p_land_architect_first_name VARCHAR(255),
  IN p_land_architect_last_name VARCHAR(255),
  IN p_land_architect_law_firm VARCHAR(255),
  IN p_land_architect_email VARCHAR(255),
  IN p_land_architect_phone VARCHAR(50),
  IN p_land_architect_cell VARCHAR(255),
  -- contractor parameters
  IN p_contractor_id INT,
  IN p_contractor_first_name VARCHAR(255),
  IN p_contractor_last_name VARCHAR(255),
  IN p_contractor_law_firm VARCHAR(255),
  IN p_contractor_email VARCHAR(255),
  IN p_contractor_phone VARCHAR(50),
  IN p_contractor_cell VARCHAR(255),
  -- application data
  IN p_PVA_parcel_number INT,
  IN p_project_type VARCHAR(255),
  IN p_zpa_project_plans VARCHAR(255),
  IN p_zpa_preliminary_site_evaluation VARCHAR(255)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Zoning Permit Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  -- Handle surveyor
  IF p_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET @insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_surveyor_id = NULL;
    END IF;
  ELSE
    SET @insert_surveyor_id = p_surveyor_id;
  END IF;

  -- Handle architect
  IF p_architect_id IS NULL THEN
    IF p_architect_first_name IS NOT NULL OR p_architect_last_name IS NOT NULL THEN
      INSERT INTO architects(architect_first_name, architect_last_name, architect_law_firm, architect_email, architect_phone, architect_cell)
        VALUES(p_architect_first_name, p_architect_last_name, p_architect_law_firm, p_architect_email, p_architect_phone, p_architect_cell);
      SET @insert_architect_id = LAST_INSERT_ID();
    ELSE
      SET @insert_architect_id = NULL;
    END IF;
  ELSE
    SET @insert_architect_id = p_architect_id;
  END IF;

  -- Handle land architect
  IF p_land_architect_id IS NULL THEN
    IF p_land_architect_first_name IS NOT NULL OR p_land_architect_last_name IS NOT NULL THEN
      INSERT INTO land_architects(land_architect_first_name, land_architect_last_name, land_architect_law_firm, land_architect_email, land_architect_phone, land_architect_cell)
        VALUES(p_land_architect_first_name, p_land_architect_last_name, p_land_architect_law_firm, p_land_architect_email, p_land_architect_phone, p_land_architect_cell);
      SET @insert_land_architect_id = LAST_INSERT_ID();
    ELSE
      SET @insert_land_architect_id = NULL;
    END IF;
  ELSE
    SET @insert_land_architect_id = p_land_architect_id;
  END IF;

  -- Handle contractor
  IF p_contractor_id IS NULL THEN
    IF p_contractor_first_name IS NOT NULL OR p_contractor_last_name IS NOT NULL THEN
      INSERT INTO contractors(contractor_first_name, contractor_last_name, contractor_law_firm, contractor_email, contractor_phone, contractor_cell)
        VALUES(p_contractor_first_name, p_contractor_last_name, p_contractor_law_firm, p_contractor_email, p_contractor_phone, p_contractor_cell);
      SET @insert_contractor_id = LAST_INSERT_ID();
    ELSE
      SET @insert_contractor_id = NULL;
    END IF;
  ELSE
    SET @insert_contractor_id = p_contractor_id;
  END IF;

  INSERT INTO zoning_permit_applications(
    form_id, surveyor_id, architect_id, land_architect_id, contractor_id, PVA_parcel_number,
    project_type, zpa_project_plans, zpa_preliminary_site_evaluation
  ) VALUES (
    @new_form_id, @insert_surveyor_id, @insert_architect_id, @insert_land_architect_id, @insert_contractor_id, p_PVA_parcel_number,
    p_project_type, p_zpa_project_plans, p_zpa_preliminary_site_evaluation
  );

  COMMIT;
END$$
DELIMITER ;



/* ---------------------------
   15) Zoning Verification Application
   Tables: forms, zoning_verification_letter, zva_applicants, zva_property_owners
   --------------------------- */

DELIMITER $
CREATE PROCEDURE sp_insert_zoning_verification_application(
  IN p_form_datetime_resolved DATETIME,
  IN p_form_paid_bool BOOLEAN,
  IN p_zva_letter_content VARCHAR(255),
  IN p_zva_zoning_letter_street VARCHAR(255),
  IN p_zva_state_code CHAR(2),
  IN p_zva_zoning_letter_city VARCHAR(255),
  IN p_zva_zoning_letter_zip VARCHAR(50),
  IN p_zva_property_street VARCHAR(255),
  IN p_zva_property_state_code CHAR(2),
  IN p_zva_property_zip VARCHAR(50),
  IN p_property_city VARCHAR(255),
  -- optional applicant
  IN p_zva_applicant_first_name VARCHAR(255),
  IN p_zva_applicant_last_name VARCHAR(255),
  IN p_zva_applicant_street VARCHAR(255),
  IN p_zva_applicant_city VARCHAR(255),
  IN p_zva_applicant_state_code CHAR(2),
  IN p_zva_applicant_zip_code VARCHAR(50),
  IN p_zva_applicant_phone_number VARCHAR(50),
  IN p_zva_applicant_fax_number VARCHAR(255),
  -- optional property owner
  IN p_zva_owner_first_name VARCHAR(255),
  IN p_zva_owner_last_name VARCHAR(255),
  IN p_zva_owner_street VARCHAR(255),
  IN p_zva_owner_city VARCHAR(255),
  IN p_zva_owner_state_code CHAR(2),
  IN p_zva_owner_zip_code VARCHAR(50)
)
BEGIN
  START TRANSACTION;
  INSERT INTO forms(form_type, form_datetime_submitted, form_datetime_resolved, form_paid_bool, correction_form_id)
    VALUES('Zoning Verification Application', CURRENT_TIMESTAMP, p_form_datetime_resolved, p_form_paid_bool, NULL);
  SET @new_form_id = LAST_INSERT_ID();

  INSERT INTO zoning_verification_letter(
    form_id, zva_letter_content, zva_zoning_letter_street, zva_state_code, zva_zoning_letter_city,
    zva_zoning_letter_zip, zva_property_street, zva_property_state_code, zva_property_zip, property_city
  ) VALUES (
    @new_form_id, p_zva_letter_content, p_zva_zoning_letter_street, p_zva_state_code, p_zva_zoning_letter_city,
    p_zva_zoning_letter_zip, p_zva_property_street, p_zva_property_state_code, p_zva_property_zip, p_property_city
  );

  IF p_zva_applicant_first_name IS NOT NULL OR p_zva_applicant_last_name IS NOT NULL THEN
    INSERT INTO zva_applicants(
      zva_applicant_first_name, zva_applicant_last_name, zva_applicant_street,
      zva_applicant_city, state_code, zva_applicant_zip_code, zva_applicant_phone_number, zva_applicant_fax_number
    ) VALUES (
      p_zva_applicant_first_name, p_zva_applicant_last_name, p_zva_applicant_street,
      p_zva_applicant_city, p_zva_applicant_state_code, p_zva_applicant_zip_code, p_zva_applicant_phone_number, p_zva_applicant_fax_number
    );
    SET @new_zva_applicant_id = LAST_INSERT_ID();
  END IF;

  IF p_zva_owner_first_name IS NOT NULL OR p_zva_owner_last_name IS NOT NULL THEN
    INSERT INTO zva_property_owners(
      zva_owner_first_name, zva_owner_last_name, zva_owner_street, zva_owner_city, state_code, zva_owner_zip_code
    ) VALUES (
      p_zva_owner_first_name, p_zva_owner_last_name, p_zva_owner_street, p_zva_owner_city, p_zva_owner_state_code, p_zva_owner_zip_code
    );
    SET @new_zva_owner_id = LAST_INSERT_ID();
  END IF;

  COMMIT;
END$$
DELIMITER ;