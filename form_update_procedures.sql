DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_administrative_appeal_request$$
CREATE PROCEDURE sp_update_administrative_appeal_request(IN p_form_id INT,
-- Form metadata
IN p_form_paid_bool BOOLEAN,
-- Hearing/submission dates
IN p_aar_hearing_date DATE,
IN p_aar_submit_date DATE,
-- Address information
IN p_aar_street_address VARCHAR(255),
IN p_aar_city_address VARCHAR(255),
IN p_state_code CHAR(2),
IN p_aar_zip_code VARCHAR(255),
-- Appeal details
IN p_aar_property_location TEXT,
IN p_aar_official_decision TEXT,
IN p_aar_relevant_provisions TEXT,
-- Primary appellant
IN p_aar_appellant_first_name VARCHAR(255),
IN p_aar_appellant_last_name VARCHAR(255),
-- Additional appellants (JSON array)
IN p_additional_appellants TEXT,
-- Adjacent property owner (optional)
IN p_adjacent_property_owner_street VARCHAR(255),
IN p_adjacent_property_owner_city VARCHAR(255),
IN p_adjacent_property_owner_state_code CHAR(2),
IN p_adjacent_property_owner_zip VARCHAR(255),
-- Primary property owner
IN p_aar_property_owner_first_name VARCHAR(255),
IN p_aar_property_owner_last_name VARCHAR(255),
-- Additional property owners (JSON array)
IN p_additional_property_owners TEXT)
BEGIN
DECLARE v_address_id INT DEFAULT NULL;
  DECLARE v_adjacent_address_id INT DEFAULT NULL;
  DECLARE v_primary_appellant_id INT DEFAULT NULL;
  DECLARE v_primary_property_owner_id INT DEFAULT NULL;
  DECLARE v_adjacent_owner_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_temp_appellant_id INT;
  DECLARE v_temp_owner_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP, form_paid_bool = p_form_paid_bool WHERE form_id = p_form_id;
DELETE FROM administrative_appellants WHERE form_id = p_form_id;
DELETE FROM administrative_property_owners WHERE form_id = p_form_id;
DELETE FROM adjacent_neighbor_owners WHERE form_id = p_form_id;
DELETE FROM administrative_appeal_requests WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Create address for the appeal
  SET v_address_id = find_duplicate_address_id(p_aar_street_address, p_aar_city_address, p_state_code, p_aar_zip_code);
  IF v_address_id IS NULL AND (p_aar_street_address IS NOT NULL OR p_aar_city_address IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_aar_street_address, p_aar_city_address, p_state_code, p_aar_zip_code);
    SET v_address_id = LAST_INSERT_ID();
  END IF;

  -- 3. Insert primary appellant
  IF p_aar_appellant_first_name IS NOT NULL OR p_aar_appellant_last_name IS NOT NULL THEN
    INSERT INTO aar_appellants(aar_first_name, aar_last_name)
      VALUES(p_aar_appellant_first_name, p_aar_appellant_last_name);
    SET v_primary_appellant_id = LAST_INSERT_ID();
    
    -- Link appellant to form
    INSERT INTO administrative_appellants(form_id, aar_appellant_id)
      VALUES(p_form_id, v_primary_appellant_id);
  END IF;

  -- 4. Insert additional appellants from JSON array
  IF p_additional_appellants IS NOT NULL AND JSON_VALID(p_additional_appellants) THEN
    SET v_count = JSON_LENGTH(p_additional_appellants);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_appellants, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert appellant
        INSERT INTO aar_appellants(aar_first_name, aar_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_appellant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO administrative_appellants(form_id, aar_appellant_id)
          VALUES(p_form_id, v_temp_appellant_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 5. Insert primary property owner
  IF p_aar_property_owner_first_name IS NOT NULL OR p_aar_property_owner_last_name IS NOT NULL THEN
    INSERT INTO aar_property_owners(aar_property_owner_first_name, aar_property_owner_last_name)
      VALUES(p_aar_property_owner_first_name, p_aar_property_owner_last_name);
    SET v_primary_property_owner_id = LAST_INSERT_ID();
    
    -- Link property owner to form
    INSERT INTO administrative_property_owners(form_id, aar_property_owner_id)
      VALUES(p_form_id, v_primary_property_owner_id);
  END IF;

  -- 6. Insert additional property owners from JSON array
  IF p_additional_property_owners IS NOT NULL AND JSON_VALID(p_additional_property_owners) THEN
    SET v_count = JSON_LENGTH(p_additional_property_owners);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_property_owners, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert property owner
        INSERT INTO aar_property_owners(aar_property_owner_first_name, aar_property_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO administrative_property_owners(form_id, aar_property_owner_id)
          VALUES(p_form_id, v_temp_owner_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert adjacent property owner if provided
  SET v_adjacent_address_id = find_duplicate_address_id(p_adjacent_property_owner_street, p_adjacent_property_owner_city, p_adjacent_property_owner_state_code, p_adjacent_property_owner_zip);
  IF p_adjacent_property_owner_street IS NOT NULL OR p_adjacent_property_owner_city IS NOT NULL THEN
    -- Create address for adjacent property owner
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_adjacent_property_owner_street, p_adjacent_property_owner_city, p_adjacent_property_owner_state_code, p_adjacent_property_owner_zip);
    SET v_adjacent_address_id = LAST_INSERT_ID();
    
    -- Insert adjacent property owner
    INSERT INTO adjacent_property_owners(address_id)
      VALUES(v_adjacent_address_id);
    SET v_adjacent_owner_id = LAST_INSERT_ID();
    
    -- Link to form
    INSERT INTO adjacent_neighbor_owners(form_id, adjacent_property_owner_id)
      VALUES(p_form_id, v_adjacent_owner_id);
  END IF;

  -- 8. Insert into administrative_appeal_requests
  INSERT INTO administrative_appeal_requests(
    form_id,
    aar_hearing_date,
    aar_submit_date,
    address_id,
    aar_official_decision,
    aar_relevant_provisions
  ) VALUES (
    p_form_id,
    p_aar_hearing_date,
    p_aar_submit_date,
    v_address_id,
    p_aar_official_decision,
    p_aar_relevant_provisions
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_administrative_appeal_request TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_adjacent_property_owners_form_json$$
CREATE PROCEDURE sp_update_adjacent_property_owners_form_json(IN p_form_id INT,
-- JSON arrays for neighbor properties
IN p_pva_map_codes TEXT,                     -- JSON array of PVA map codes
IN p_neighbor_property_locations TEXT,        -- JSON array of property locations
IN p_neighbor_property_deed_books TEXT,       -- JSON array of deed books
IN p_property_street_pg_numbers TEXT,         -- JSON array of page numbers
-- JSON arrays for property owners (nested: array of arrays)
IN p_property_owner_names TEXT,               -- JSON: {"0": ["Owner1", "Owner2"], "1": ["Owner3"]}
IN p_property_owner_streets TEXT,             -- JSON: {"0": ["Street1", "Street2"], "1": ["Street3"]}
IN p_property_owner_cities TEXT,              -- JSON: {"0": ["City1", "City2"], "1": ["City3"]}
IN p_property_owner_state_codes TEXT,         -- JSON: {"0": ["KY", "KY"], "1": ["KY"]}
IN p_property_owner_zips TEXT)                 -- JSON: {"0": ["40223", "40223"], "1": ["40223"]})
BEGIN
DECLARE v_neighbor_count INT DEFAULT 0;
  DECLARE v_neighbor_idx INT DEFAULT 0;
  DECLARE v_owner_count INT DEFAULT 0;
  DECLARE v_owner_idx INT DEFAULT 0;
  
  -- Neighbor property variables
  DECLARE v_pva_map_code VARCHAR(255);
  DECLARE v_neighbor_location VARCHAR(500);
  DECLARE v_deed_book VARCHAR(255);
  DECLARE v_page_number VARCHAR(255);
  
  -- Owner variables
  DECLARE v_owner_name VARCHAR(255);
  DECLARE v_owner_street VARCHAR(255);
  DECLARE v_owner_city VARCHAR(255);
  DECLARE v_owner_state VARCHAR(2);
  DECLARE v_owner_zip VARCHAR(50);
  
  -- Temp variables
  DECLARE v_neighbor_id INT;
  DECLARE v_owner_id INT;
  DECLARE v_address_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_owner_array TEXT;
  DECLARE v_neighbor_key VARCHAR(10);
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM adjacent_property_owner_forms WHERE form_id = p_form_id;
DELETE FROM adjacent_neighbors WHERE form_id = p_form_id;
DELETE FROM adjacent_neighbor_owners WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert into adjacent_property_owner_forms
  INSERT INTO adjacent_property_owner_forms(form_id)
    VALUES(p_form_id);

  -- 3. Validate and parse JSON arrays for neighbors
  IF p_pva_map_codes IS NOT NULL AND JSON_VALID(p_pva_map_codes) THEN
    SET v_neighbor_count = JSON_LENGTH(p_pva_map_codes);
  ELSE
    SET v_neighbor_count = 0;
  END IF;

  -- 4. Loop through each neighbor property
  SET v_neighbor_idx = 0;
  WHILE v_neighbor_idx < v_neighbor_count DO
    -- Extract neighbor property data
    SET v_pva_map_code = IF(JSON_VALID(p_pva_map_codes), 
                           JSON_UNQUOTE(JSON_EXTRACT(p_pva_map_codes, CONCAT('$[', v_neighbor_idx, ']'))), 
                           NULL);
    SET v_neighbor_location = IF(JSON_VALID(p_neighbor_property_locations), 
                                JSON_UNQUOTE(JSON_EXTRACT(p_neighbor_property_locations, CONCAT('$[', v_neighbor_idx, ']'))), 
                                NULL);
    SET v_deed_book = IF(JSON_VALID(p_neighbor_property_deed_books), 
                        JSON_UNQUOTE(JSON_EXTRACT(p_neighbor_property_deed_books, CONCAT('$[', v_neighbor_idx, ']'))), 
                        NULL);
    SET v_page_number = IF(JSON_VALID(p_property_street_pg_numbers), 
                          JSON_UNQUOTE(JSON_EXTRACT(p_property_street_pg_numbers, CONCAT('$[', v_neighbor_idx, ']'))), 
                          NULL);

    -- Insert neighbor property
    INSERT INTO apof_neighbors(
      PVA_map_code,
      apof_neighbor_property_location,
      apof_neighbor_property_deed_book,
      apof_property_street_pg_number
    )
    VALUES(
      v_pva_map_code,
      v_neighbor_location,
      v_deed_book,
      v_page_number
    );
    SET v_neighbor_id = LAST_INSERT_ID();

    -- Link neighbor to form
    INSERT INTO adjacent_neighbors(form_id, neighbor_id)
      VALUES(p_form_id, v_neighbor_id);

    -- 5. Process owners for this neighbor property
    SET v_neighbor_key = CAST(v_neighbor_idx AS CHAR);
    
    -- Check if owner data exists for this neighbor
    IF p_property_owner_names IS NOT NULL AND JSON_VALID(p_property_owner_names) THEN
      IF JSON_CONTAINS_PATH(p_property_owner_names, 'one', CONCAT('$.', v_neighbor_key)) THEN
        SET v_owner_array = JSON_EXTRACT(p_property_owner_names, CONCAT('$.', v_neighbor_key));
        
        IF JSON_VALID(v_owner_array) THEN
          SET v_owner_count = JSON_LENGTH(v_owner_array);
          SET v_owner_idx = 0;
          
          -- Loop through each owner for this neighbor
          WHILE v_owner_idx < v_owner_count DO
            -- Extract owner data
            SET v_owner_name = JSON_UNQUOTE(JSON_EXTRACT(v_owner_array, CONCAT('$[', v_owner_idx, ']')));
            
            -- Extract owner address data
            SET v_owner_street = NULL;
            SET v_owner_city = NULL;
            SET v_owner_state = NULL;
            SET v_owner_zip = NULL;
            
            IF p_property_owner_streets IS NOT NULL AND JSON_VALID(p_property_owner_streets) THEN
              IF JSON_CONTAINS_PATH(p_property_owner_streets, 'one', CONCAT('$.', v_neighbor_key)) THEN
                SET @owner_street_array = JSON_EXTRACT(p_property_owner_streets, CONCAT('$.', v_neighbor_key));
                IF JSON_VALID(@owner_street_array) THEN
                  SET v_owner_street = JSON_UNQUOTE(JSON_EXTRACT(@owner_street_array, CONCAT('$[', v_owner_idx, ']')));
                END IF;
              END IF;
            END IF;
            
            IF p_property_owner_cities IS NOT NULL AND JSON_VALID(p_property_owner_cities) THEN
              IF JSON_CONTAINS_PATH(p_property_owner_cities, 'one', CONCAT('$.', v_neighbor_key)) THEN
                SET @owner_city_array = JSON_EXTRACT(p_property_owner_cities, CONCAT('$.', v_neighbor_key));
                IF JSON_VALID(@owner_city_array) THEN
                  SET v_owner_city = JSON_UNQUOTE(JSON_EXTRACT(@owner_city_array, CONCAT('$[', v_owner_idx, ']')));
                END IF;
              END IF;
            END IF;
            
            IF p_property_owner_state_codes IS NOT NULL AND JSON_VALID(p_property_owner_state_codes) THEN
              IF JSON_CONTAINS_PATH(p_property_owner_state_codes, 'one', CONCAT('$.', v_neighbor_key)) THEN
                SET @owner_state_array = JSON_EXTRACT(p_property_owner_state_codes, CONCAT('$.', v_neighbor_key));
                IF JSON_VALID(@owner_state_array) THEN
                  SET v_owner_state = JSON_UNQUOTE(JSON_EXTRACT(@owner_state_array, CONCAT('$[', v_owner_idx, ']')));
                END IF;
              END IF;
            END IF;
            
            IF p_property_owner_zips IS NOT NULL AND JSON_VALID(p_property_owner_zips) THEN
              IF JSON_CONTAINS_PATH(p_property_owner_zips, 'one', CONCAT('$.', v_neighbor_key)) THEN
                SET @owner_zip_array = JSON_EXTRACT(p_property_owner_zips, CONCAT('$.', v_neighbor_key));
                IF JSON_VALID(@owner_zip_array) THEN
                  SET v_owner_zip = JSON_UNQUOTE(JSON_EXTRACT(@owner_zip_array, CONCAT('$[', v_owner_idx, ']')));
                END IF;
              END IF;
            END IF;

            -- Create owner address if any address data provided
            SET v_address_id = find_duplicate_address_id(v_owner_street, v_owner_city, v_owner_state, v_owner_zip);
            IF v_address_id IS NULL AND (v_owner_street IS NOT NULL OR v_owner_city IS NOT NULL OR v_owner_state IS NOT NULL OR v_owner_zip IS NOT NULL) THEN
              INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
                VALUES(v_owner_street, v_owner_city, v_owner_state, v_owner_zip);
              SET v_address_id = LAST_INSERT_ID();
            END IF;

            -- Parse owner name (simple first/last split)
            IF v_owner_name IS NOT NULL AND v_owner_name != '' THEN
              SET v_first_name = SUBSTRING_INDEX(v_owner_name, ' ', 1);
              SET v_last_name = SUBSTRING_INDEX(v_owner_name, ' ', -1);
              IF v_first_name = v_last_name THEN
                SET v_last_name = '';
              END IF;
            ELSE
              SET v_first_name = NULL;
              SET v_last_name = NULL;
            END IF;

            -- Insert property owner
            INSERT INTO adjacent_property_owners(
              adjacent_property_owner_first_name,
              adjacent_property_owner_last_name,
              address_id
            )
            VALUES(
              v_first_name,
              v_last_name,
              v_address_id
            );
            SET v_owner_id = LAST_INSERT_ID();

            -- Link owner to form
            INSERT INTO adjacent_neighbor_owners(form_id, adjacent_property_owner_id)
              VALUES(p_form_id, v_owner_id);

            SET v_owner_idx = v_owner_idx + 1;
          END WHILE;
        END IF;
      END IF;
    END IF;

    SET v_neighbor_idx = v_neighbor_idx + 1;
  END WHILE;

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_adjacent_property_owners_form_json TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_conditional_use_permit_application$$
CREATE PROCEDURE sp_update_conditional_use_permit_application(IN p_form_id INT,
-- Hearing information
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_mailing_address VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_mailing_addresses TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_name VARCHAR(255),
IN p_owner_mailing_address VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_mailing_addresses TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Property information
IN p_property_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- CUP request
IN p_cupa_permit_request TEXT,
IN p_cupa_proposed_conditions TEXT,
-- Checklist items
IN p_checklist_application BOOLEAN,
IN p_checklist_exhibit BOOLEAN,
IN p_checklist_adjacent BOOLEAN,
IN p_checklist_fees BOOLEAN,
-- File uploads (filenames)
IN p_file_exhibit VARCHAR(255),
IN p_file_adjacent VARCHAR(255))
BEGIN
DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_address VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM conditional_use_permit_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create/get attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, NULL, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 4. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, NULL,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          -- Parse officer name
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          -- Link exec to applicant
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 5. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Extract other fields for this applicant
        SET v_temp_address = IF(JSON_VALID(p_additional_applicant_mailing_addresses), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_mailing_addresses, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert applicant
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, NULL,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for this additional applicant
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 6. Insert primary property owner
  IF p_owner_name IS NOT NULL THEN
    -- Parse name
    SET v_first_name = SUBSTRING_INDEX(p_owner_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_owner_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(v_first_name, v_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    -- Link owner to form
    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 7. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert owner
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 8. Insert into conditional_use_permit_applications
  INSERT INTO conditional_use_permit_applications(
    form_id,
    cupa_permit_request,
    cupa_proposed_conditions,
    PVA_parcel_number
  ) VALUES (
    p_form_id,
    p_cupa_permit_request,
    p_cupa_proposed_conditions,
    p_parcel_number
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_conditional_use_permit_application TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_general_development_plan_application_comprehensive$$
CREATE PROCEDURE sp_update_general_development_plan_application_comprehensive(IN p_form_id INT,
-- Hearing information (4)
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant (9)
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays) (9)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner (9)
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays) (8)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney (6)
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Property information (8)
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- GDP details (3)
IN p_gdp_amendment_request TEXT,
IN p_proposed_conditions TEXT,
IN p_finding_type VARCHAR(255),
-- Findings explanation (1)
IN p_findings_explanation TEXT,
-- Checklist items (8)
IN p_checklist_application BOOLEAN,
IN p_checklist_adjacent BOOLEAN,
IN p_checklist_verification BOOLEAN,
IN p_checklist_fees BOOLEAN,
IN p_checklist_gdp_conditions BOOLEAN,
IN p_checklist_concept BOOLEAN,
IN p_checklist_traffic BOOLEAN,
IN p_checklist_geologic BOOLEAN,
-- Signatures (4)
IN p_signature_date_1 DATE,
IN p_signature_name_1 VARCHAR(255),
IN p_signature_date_2 DATE,
IN p_signature_name_2 VARCHAR(255))
BEGIN
DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM general_development_plan_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF v_property_address_id IS NULL AND (p_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF v_primary_applicant_address_id IS NULL AND (p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL) THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants (same logic as other forms)
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Insert into general_development_plan_applications
  INSERT INTO general_development_plan_applications(
    form_id, address_id, gdpa_applicant_phone, gdpa_plan_amendment_request,
    gdpa_proposed_conditions, required_findings_type
  ) VALUES (
    p_form_id, v_primary_applicant_address_id, p_applicant_phone,
    p_gdp_amendment_request, p_proposed_conditions, p_finding_type
  );

  COMMIT;
  
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_general_development_plan_application_comprehensive TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_site_development_plan_application_comprehensive$$
CREATE PROCEDURE sp_update_site_development_plan_application_comprehensive(IN p_form_id INT,
-- Form metadata (3)
IN p_form_datetime_resolved DATETIME,
IN p_form_paid_bool BOOLEAN,
IN p_correction_form_id INT,
-- Hearing information (4)
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant (9)
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays) (9)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner (8)
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays) (8)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney (6)
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Surveyor (6)
IN p_surveyor_first_name VARCHAR(255),
IN p_surveyor_last_name VARCHAR(255),
IN p_surveyor_firm VARCHAR(255),
IN p_surveyor_phone VARCHAR(50),
IN p_surveyor_cell VARCHAR(255),
IN p_surveyor_email VARCHAR(255),
-- Engineer (6)
IN p_engineer_first_name VARCHAR(255),
IN p_engineer_last_name VARCHAR(255),
IN p_engineer_firm VARCHAR(255),
IN p_engineer_phone VARCHAR(50),
IN p_engineer_cell VARCHAR(255),
IN p_engineer_email VARCHAR(255),
-- Architect (6)
IN p_architect_first_name VARCHAR(255),
IN p_architect_last_name VARCHAR(255),
IN p_architect_firm VARCHAR(255),
IN p_architect_phone VARCHAR(50),
IN p_architect_cell VARCHAR(255),
IN p_architect_email VARCHAR(255),
-- Landscape Architect (6)
IN p_land_architect_first_name VARCHAR(255),
IN p_land_architect_last_name VARCHAR(255),
IN p_land_architect_firm VARCHAR(255),
IN p_land_architect_phone VARCHAR(50),
IN p_land_architect_cell VARCHAR(255),
IN p_land_architect_email VARCHAR(255),
-- Application details (2)
IN p_application_type VARCHAR(255),
IN p_site_plan_request TEXT,
-- Checklist items (9)
IN p_checklist_application BOOLEAN,
IN p_checklist_verification BOOLEAN,
IN p_checklist_project_plans BOOLEAN,
IN p_checklist_landscape BOOLEAN,
IN p_checklist_topographic BOOLEAN,
IN p_checklist_traffic BOOLEAN,
IN p_checklist_architectural BOOLEAN,
IN p_checklist_covenants BOOLEAN,
IN p_checklist_fees BOOLEAN,
-- File uploads (7)
IN p_file_verification VARCHAR(255),
IN p_file_project_plans VARCHAR(255),
IN p_file_landscape VARCHAR(255),
IN p_file_topographic VARCHAR(255),
IN p_file_traffic VARCHAR(255),
IN p_file_architectural VARCHAR(255),
IN p_file_covenants VARCHAR(255),
-- Signatures (4)
IN p_signature_date_1 DATE,
IN p_signature_name_1 VARCHAR(255),
IN p_signature_date_2 DATE,
IN p_signature_name_2 VARCHAR(255))
BEGIN
DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_surveyor_id INT DEFAULT NULL;
  DECLARE v_engineer_id INT DEFAULT NULL;
  DECLARE v_architect_id INT DEFAULT NULL;
  DECLARE v_land_architect_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP, form_paid_bool = p_form_paid_bool, form_datetime_resolved = p_form_datetime_resolved, correction_form_id = p_correction_form_id WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM site_development_plan_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create surveyor if provided
  SET v_surveyor_id = find_duplicate_surveyor_id(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
  IF v_surveyor_id IS NULL AND (p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL) THEN
    INSERT INTO surveyors(
      surveyor_first_name, surveyor_last_name, surveyor_firm,
      surveyor_email, surveyor_phone, surveyor_cell
    ) VALUES (
      p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm,
      p_surveyor_email, p_surveyor_phone, p_surveyor_cell
    );
    SET v_surveyor_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create engineer if provided
  SET v_engineer_id = find_duplicate_engineer_id(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);

  IF v_engineer_id IS NULL AND (p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL) THEN
    INSERT INTO engineers(
      engineer_first_name, engineer_last_name, engineer_firm,
      engineer_email, engineer_phone, engineer_cell
    ) VALUES (
      p_engineer_first_name, p_engineer_last_name, p_engineer_firm,
      p_engineer_email, p_engineer_phone, p_engineer_cell
    );
    SET v_engineer_id = LAST_INSERT_ID();
  END IF;

  -- 5. Create architect if provided
  SET v_architect_id = find_duplicate_architect_id(p_architect_first_name, p_architect_last_name, p_architect_firm, p_architect_email, p_architect_phone, p_architect_cell);
  IF v_architect_id IS NULL AND (p_architect_first_name IS NOT NULL OR p_architect_last_name IS NOT NULL) THEN
    INSERT INTO architects(
      architect_first_name, architect_last_name, architect_law_firm,
      architect_email, architect_phone, architect_cell
    ) VALUES (
      p_architect_first_name, p_architect_last_name, p_architect_firm,
      p_architect_email, p_architect_phone, p_architect_cell
    );
    SET v_architect_id = LAST_INSERT_ID();
  END IF;

  -- 6. Create land architect if provided
  SET v_land_architect_id = find_duplicate_land_architect_id(p_land_architect_first_name, p_land_architect_last_name, p_land_architect_firm, p_land_architect_email, p_land_architect_phone, p_land_architect_cell);
  IF v_land_architect_id IS NULL AND (p_land_architect_first_name IS NOT NULL OR p_land_architect_last_name IS NOT NULL) THEN
    INSERT INTO land_architects(
      land_architect_first_name, land_architect_last_name, land_architect_law_firm,
      land_architect_email, land_architect_phone, land_architect_cell
    ) VALUES (
      p_land_architect_first_name, p_land_architect_last_name, p_land_architect_firm,
      p_land_architect_email, p_land_architect_phone, p_land_architect_cell
    );
    SET v_land_architect_id = LAST_INSERT_ID();
  END IF;

  -- 7. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 8. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for additional applicants
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 10. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 11. Insert into site_development_plan_applications
  INSERT INTO site_development_plan_applications(
    form_id, surveyor_id, land_architect_id, engineer_id, architect_id, site_plan_request
  ) VALUES (
    p_form_id, v_surveyor_id, v_land_architect_id, v_engineer_id, v_architect_id, p_site_plan_request
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_site_development_plan_application_comprehensive TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_future_land_use_map_application_comprehensive$$
CREATE PROCEDURE sp_update_future_land_use_map_application_comprehensive(IN p_form_id INT,
-- Hearing information
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_other_address VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_other_addresses TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_other_address VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_other_addresses TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Property information
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- FLUM request
IN p_flum_request TEXT,
-- Findings
IN p_finding_type VARCHAR(255),
IN p_findings_explanation TEXT,
-- File uploads (stored as file paths/names)
IN p_file_exhibit VARCHAR(255),
IN p_file_concept VARCHAR(255),
IN p_file_compatibility VARCHAR(255))
BEGIN
DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM future_land_use_map_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create/get attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF v_property_address_id IS NULL AND (p_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF v_primary_applicant_address_id IS NULL AND (p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL) THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants from JSON arrays (similar logic as zoning map amendment)
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for additional applicants
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Insert into future_land_use_map_applications
  INSERT INTO future_land_use_map_applications(
    form_id,
    future_land_use_map_amendment_prop,
    required_findings_type,
    findings_explanation,
    PVA_parcel_number
  ) VALUES (
    p_form_id,
    p_flum_request,
    p_finding_type,
    p_findings_explanation,
    p_parcel_number
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_future_land_use_map_application_comprehensive TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_major_subdivision_plat_application$$
CREATE PROCEDURE sp_update_major_subdivision_plat_application(IN p_form_id INT,
-- Technical form dates
IN p_application_filing_date DATE,
IN p_technical_review_date DATE,
IN p_preliminary_approval_date DATE,
IN p_final_approval_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_other_address VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_other_addresses TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_other_address VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_other_addresses TEXT,
IN p_additional_owner_emails TEXT,
-- Surveyor (can be new or existing)
IN p_surveyor_first_name VARCHAR(255),
IN p_surveyor_last_name VARCHAR(255),
IN p_surveyor_firm VARCHAR(255),
IN p_surveyor_email VARCHAR(255),
IN p_surveyor_phone VARCHAR(50),
IN p_surveyor_cell VARCHAR(255),
-- Engineer (can be new or existing)
IN p_engineer_first_name VARCHAR(255),
IN p_engineer_last_name VARCHAR(255),
IN p_engineer_firm VARCHAR(255),
IN p_engineer_email VARCHAR(255),
IN p_engineer_phone VARCHAR(50),
IN p_engineer_cell VARCHAR(255),
-- Property information
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- Subdivision plat details (expanded for major subdivision)
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
IN p_mspa_construction_bond_est VARCHAR(255),
-- Checklist items (15 items for major subdivision)
IN p_checklist_application BOOLEAN,
IN p_checklist_agency_signatures BOOLEAN,
IN p_checklist_lot_layout BOOLEAN,
IN p_checklist_topographic BOOLEAN,
IN p_checklist_restrictions BOOLEAN,
IN p_checklist_fees BOOLEAN,
IN p_checklist_construction_plans BOOLEAN,
IN p_checklist_traffic_study BOOLEAN,
IN p_checklist_drainage BOOLEAN,
IN p_checklist_pavement BOOLEAN,
IN p_checklist_swppp BOOLEAN,
IN p_checklist_bond_estimate BOOLEAN,
IN p_checklist_construction_contract BOOLEAN,
IN p_checklist_construction_bond BOOLEAN,
IN p_checklist_notice_proceed BOOLEAN,
-- File uploads (stored as file paths/names)
IN p_file_agency_signatures VARCHAR(255),
IN p_file_lot_layout VARCHAR(255),
IN p_file_topographic VARCHAR(255),
IN p_file_restrictions VARCHAR(255),
IN p_file_construction_plans VARCHAR(255),
IN p_file_traffic_study VARCHAR(255),
IN p_file_drainage VARCHAR(255),
IN p_file_pavement VARCHAR(255),
IN p_file_swppp VARCHAR(255),
IN p_file_bond_estimate VARCHAR(255),
IN p_file_construction_contract VARCHAR(255),
IN p_file_construction_bond VARCHAR(255))
BEGIN
DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_insert_surveyor_id INT DEFAULT NULL;
  DECLARE v_insert_engineer_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM technical_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM major_subdivision_plat_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert technical form dates if provided
  IF p_application_filing_date IS NOT NULL OR p_technical_review_date IS NOT NULL OR 
     p_preliminary_approval_date IS NOT NULL OR p_final_approval_date IS NOT NULL THEN
    INSERT INTO technical_forms(
      form_id, technical_app_filing_date, technical_review_date,
      technical_prelim_approval_date, technical_final_approval_date
    ) VALUES (
      p_form_id, p_application_filing_date, p_technical_review_date,
      p_preliminary_approval_date, p_final_approval_date
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF p_property_street IS NOT NULL OR p_property_city IS NOT NULL THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF v_primary_applicant_address_id IS NULL AND (p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL) THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          -- Parse officer name
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          -- Link exec to applicant
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Extract other fields for this applicant
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        -- Create address if provided
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert applicant
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for this additional applicant
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    -- Link owner to form
    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert owner
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Handle surveyor
  SET v_insert_surveyor_id = find_duplicate_surveyor_id(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
  IF v_insert_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET v_insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET v_insert_surveyor_id = NULL;
    END IF;
  END IF;

  -- 10. Handle engineer
  SET v_insert_engineer_id = find_duplicate_engineer_id(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
  IF v_insert_engineer_id IS NULL THEN
    IF p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL THEN
      INSERT INTO engineers(engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell)
        VALUES(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
      SET v_insert_engineer_id = LAST_INSERT_ID();
    ELSE
      SET v_insert_engineer_id = NULL;
    END IF;
  END IF;

  -- 11. Insert into major_subdivision_plat_applications
  INSERT INTO major_subdivision_plat_applications(
    form_id, surveyor_id, engineer_id, PVA_parcel_number,
    mspa_topographic_survey, mspa_proposed_plot_layout,
    mspa_plat_restrictions, mspa_property_owner_convenants,
    mspa_association_covenants, mspa_master_deed,
    mspa_construction_plans, mspa_traffic_impact_study,
    mspa_geologic_study, mspa_drainage_plan, mspa_pavement_design,
    mspa_SWPPP_EPSC_plan, mspa_construction_bond_est
  ) VALUES (
    p_form_id, v_insert_surveyor_id, v_insert_engineer_id, p_parcel_number,
    p_mspa_topographic_survey, p_mspa_proposed_plot_layout,
    p_mspa_plat_restrictions, p_mspa_property_owner_convenants,
    p_mspa_association_covenants, p_mspa_master_deed,
    p_mspa_construction_plans, p_mspa_traffic_impact_study,
    p_mspa_geologic_study, p_mspa_drainage_plan, p_mspa_pavement_design,
    p_mspa_SWPPP_EPSC_plan, p_mspa_construction_bond_est
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_major_subdivision_plat_application TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_minor_subdivision_plat_application$$
CREATE PROCEDURE sp_update_minor_subdivision_plat_application(IN p_form_id INT,
-- Technical form dates
IN p_application_filing_date DATE,
IN p_technical_review_date DATE,
IN p_preliminary_approval_date DATE,
IN p_final_approval_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_other_address VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_other_addresses TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_other_address VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_other_addresses TEXT,
IN p_additional_owner_emails TEXT,
-- Surveyor (can be new or existing)
IN p_surveyor_first_name VARCHAR(255),
IN p_surveyor_last_name VARCHAR(255),
IN p_surveyor_firm VARCHAR(255),
IN p_surveyor_email VARCHAR(255),
IN p_surveyor_phone VARCHAR(50),
IN p_surveyor_cell VARCHAR(255),
-- Engineer (can be new or existing)
IN p_engineer_first_name VARCHAR(255),
IN p_engineer_last_name VARCHAR(255),
IN p_engineer_firm VARCHAR(255),
IN p_engineer_email VARCHAR(255),
IN p_engineer_phone VARCHAR(50),
IN p_engineer_cell VARCHAR(255),
-- Property information
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- Subdivision plat details
IN p_minspa_topographic_survey VARCHAR(255),
IN p_minspa_proposed_plot_layout VARCHAR(255),
IN p_minspa_plat_restrictions VARCHAR(255),
IN p_minspa_property_owner_covenants VARCHAR(255),
IN p_minspa_association_covenants VARCHAR(255),
IN p_minspa_master_deed VARCHAR(255),
-- Checklist items
IN p_checklist_application BOOLEAN,
IN p_checklist_agency_signatures BOOLEAN,
IN p_checklist_lot_layout BOOLEAN,
IN p_checklist_topographic BOOLEAN,
IN p_checklist_restrictions BOOLEAN,
IN p_checklist_fees BOOLEAN,
-- File uploads (stored as file paths/names)
IN p_file_agency_signatures VARCHAR(255),
IN p_file_lot_layout VARCHAR(255),
IN p_file_topographic VARCHAR(255),
IN p_file_restrictions VARCHAR(255))
BEGIN
DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_insert_surveyor_id INT DEFAULT NULL;
  DECLARE v_insert_engineer_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP WHERE form_id = p_form_id;
DELETE FROM technical_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM minor_subdivision_plat_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert technical form dates if provided
  IF p_application_filing_date IS NOT NULL OR p_technical_review_date IS NOT NULL OR 
     p_preliminary_approval_date IS NOT NULL OR p_final_approval_date IS NOT NULL THEN
    INSERT INTO technical_forms(
      form_id, technical_app_filing_date, technical_review_date,
      technical_prelim_approval_date, technical_final_approval_date
    ) VALUES (
      p_form_id, p_application_filing_date, p_technical_review_date,
      p_preliminary_approval_date, p_final_approval_date
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF v_property_address_id IS NULL AND (p_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF v_primary_applicant_address_id IS NULL AND (p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL) THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          -- Parse officer name
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          -- Link exec to applicant
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Extract other fields for this applicant
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        -- Create address if provided
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert applicant
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for this additional applicant
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    -- Link owner to form
    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert owner
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Handle surveyor
  SET v_insert_surveyor_id = find_duplicate_surveyor_id(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
  IF v_insert_surveyor_id IS NULL THEN
    IF p_surveyor_first_name IS NOT NULL OR p_surveyor_last_name IS NOT NULL THEN
      INSERT INTO surveyors(surveyor_first_name, surveyor_last_name, surveyor_firm, surveyor_email, surveyor_phone, surveyor_cell)
        VALUES(p_surveyor_first_name, p_surveyor_last_name, p_surveyor_firm, p_surveyor_email, p_surveyor_phone, p_surveyor_cell);
      SET v_insert_surveyor_id = LAST_INSERT_ID();
    ELSE
      SET v_insert_surveyor_id = NULL;
    END IF;
  END IF;

  -- 10. Handle engineer
  SET v_insert_engineer_id = find_duplicate_engineer_id(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
  IF v_insert_engineer_id IS NULL THEN
    IF p_engineer_first_name IS NOT NULL OR p_engineer_last_name IS NOT NULL THEN
      INSERT INTO engineers(engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone, engineer_cell)
        VALUES(p_engineer_first_name, p_engineer_last_name, p_engineer_firm, p_engineer_email, p_engineer_phone, p_engineer_cell);
      SET v_insert_engineer_id = LAST_INSERT_ID();
    ELSE
      SET v_insert_engineer_id = NULL;
    END IF;
  END IF;

  -- 11. Insert into minor_subdivision_plat_applications
  INSERT INTO minor_subdivision_plat_applications(
    form_id, surveyor_id, engineer_id, PVA_parcel_number,
    minspa_topographic_survey, minspa_proposed_plot_layout,
    minspa_plat_restrictions, minspa_property_owner_covenants,
    minspa_association_covenants, minspa_master_deed
  ) VALUES (
    p_form_id, v_insert_surveyor_id, v_insert_engineer_id, p_parcel_number,
    p_minspa_topographic_survey, p_minspa_proposed_plot_layout,
    p_minspa_plat_restrictions, p_minspa_property_owner_covenants,
    p_minspa_association_covenants, p_minspa_master_deed
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_minor_subdivision_plat_application TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_variance_application$$
CREATE PROCEDURE sp_update_variance_application(IN p_form_id INT,
-- Form metadata
IN p_form_datetime_resolved DATETIME,
IN p_form_paid_bool BOOLEAN,
IN p_correction_form_id INT,
-- Hearing information
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_other_address VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_other_addresses TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_other_address VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_other_addresses TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Property information
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- Variance request
IN p_variance_request TEXT,
IN p_proposed_conditions TEXT,
IN p_findings_explanation TEXT,
-- Checklist items
IN p_checklist_application BOOLEAN,
IN p_checklist_exhibit BOOLEAN,
IN p_checklist_adjacent BOOLEAN,
IN p_checklist_fees BOOLEAN,
-- File uploads (stored as file paths/names)
IN p_file_exhibit VARCHAR(255),
IN p_file_adjacent VARCHAR(255),
-- Signatures
IN p_signature_date_1 DATE,
IN p_signature_name_1 VARCHAR(255),
IN p_signature_date_2 DATE,
IN p_signature_name_2 VARCHAR(255),
-- Admin/fees
IN p_application_fee VARCHAR(255),
IN p_certificate_fee VARCHAR(255))
BEGIN
DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP, form_paid_bool = p_form_paid_bool, form_datetime_resolved = p_form_datetime_resolved, correction_form_id = p_correction_form_id WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM variance_applications WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create/get attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF v_property_address_id IS NULL AND (p_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          -- Parse officer name
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          -- Link exec to applicant
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Extract other fields for this applicant
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        -- Create address if provided
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert applicant
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for this additional applicant
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    -- Link owner to form
    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert owner
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Insert into variance_applications
  INSERT INTO variance_applications(
    form_id,
    va_variance_request,
    va_proposed_conditions,
    PVA_parcel_number
  ) VALUES (
    p_form_id,
    p_variance_request,
    p_proposed_conditions,
    p_parcel_number
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_variance_application TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_zoning_map_amendment_application$$
CREATE PROCEDURE sp_update_zoning_map_amendment_application(IN p_form_id INT,
-- Form metadata
IN p_form_datetime_resolved DATETIME,
IN p_form_paid_bool BOOLEAN,
IN p_correction_form_id INT,
-- Hearing information
IN p_docket_number VARCHAR(255),
IN p_public_hearing_date DATE,
IN p_date_application_filed DATE,
IN p_preapp_meeting_date DATE,
-- Primary applicant
IN p_applicant_name VARCHAR(255),
IN p_officers_names TEXT, -- JSON array
IN p_applicant_street VARCHAR(255),
IN p_applicant_phone VARCHAR(50),
IN p_applicant_cell VARCHAR(50),
IN p_applicant_city VARCHAR(255),
IN p_applicant_state CHAR(2),
IN p_applicant_zip_code VARCHAR(255),
IN p_applicant_other_address VARCHAR(255),
IN p_applicant_email VARCHAR(255),
-- Additional applicants (JSON arrays)
IN p_additional_applicant_names TEXT,
IN p_additional_applicant_officers TEXT,
IN p_additional_applicant_streets TEXT,
IN p_additional_applicant_phones TEXT,
IN p_additional_applicant_cells TEXT,
IN p_additional_applicant_cities TEXT,
IN p_additional_applicant_states TEXT,
IN p_additional_applicant_zip_codes TEXT,
IN p_additional_applicant_other_addresses TEXT,
IN p_additional_applicant_emails TEXT,
-- Property owner
IN p_owner_first_name VARCHAR(255),
IN p_owner_last_name VARCHAR(255),
IN p_owner_street VARCHAR(255),
IN p_owner_phone VARCHAR(50),
IN p_owner_cell VARCHAR(50),
IN p_owner_city VARCHAR(255),
IN p_owner_state CHAR(2),
IN p_owner_zip_code VARCHAR(255),
IN p_owner_other_address VARCHAR(255),
IN p_owner_email VARCHAR(255),
-- Additional owners (JSON arrays)
IN p_additional_owner_names TEXT,
IN p_additional_owner_streets TEXT,
IN p_additional_owner_phones TEXT,
IN p_additional_owner_cells TEXT,
IN p_additional_owner_cities TEXT,
IN p_additional_owner_states TEXT,
IN p_additional_owner_zip_codes TEXT,
IN p_additional_owner_other_addresses TEXT,
IN p_additional_owner_emails TEXT,
-- Attorney
IN p_attorney_first_name VARCHAR(255),
IN p_attorney_last_name VARCHAR(255),
IN p_law_firm VARCHAR(255),
IN p_attorney_phone VARCHAR(50),
IN p_attorney_cell VARCHAR(255),
IN p_attorney_email VARCHAR(255),
-- Property information
IN p_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_property_state CHAR(2),
IN p_property_zip_code VARCHAR(255),
IN p_property_other_address VARCHAR(255),
IN p_parcel_number INT,
IN p_acreage VARCHAR(255),
IN p_current_zoning VARCHAR(255),
-- Amendment request
IN p_zoning_map_amendment_request TEXT,
IN p_zmaa_proposed_conditions TEXT,
-- Findings
IN p_finding_type VARCHAR(255),
IN p_findings_explanation TEXT,
-- Checklist items
IN p_checklist_application BOOLEAN,
IN p_checklist_exhibit BOOLEAN,
IN p_checklist_adjacent BOOLEAN,
IN p_checklist_verification BOOLEAN,
IN p_checklist_fees BOOLEAN,
IN p_checklist_conditions BOOLEAN,
IN p_checklist_concept BOOLEAN,
IN p_checklist_traffic BOOLEAN,
IN p_checklist_geologic BOOLEAN,
-- File uploads (BLOBs) - stored as file paths/names
IN p_file_exhibit VARCHAR(255),
IN p_file_adjacent VARCHAR(255),
IN p_file_verification VARCHAR(255),
IN p_file_conditions VARCHAR(255),
IN p_file_concept VARCHAR(255),
IN p_file_traffic VARCHAR(255),
IN p_file_geologic VARCHAR(255),
-- Signatures
IN p_signature_date_1 DATE,
IN p_signature_name_1 VARCHAR(255),
IN p_signature_date_2 DATE,
IN p_signature_name_2 VARCHAR(255),
-- Admin/fees
IN p_application_fee VARCHAR(255),
IN p_certificate_fee VARCHAR(255))
BEGIN
  DECLARE v_property_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_address_id INT DEFAULT NULL;
  DECLARE v_owner_address_id INT DEFAULT NULL;
  DECLARE v_primary_applicant_id INT DEFAULT NULL;
  DECLARE v_primary_owner_id INT DEFAULT NULL;
  DECLARE v_attorney_id INT DEFAULT NULL;
  DECLARE v_idx INT DEFAULT 0;
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_temp_name VARCHAR(255);
  DECLARE v_temp_street VARCHAR(255);
  DECLARE v_temp_city VARCHAR(255);
  DECLARE v_temp_state CHAR(2);
  DECLARE v_temp_zip VARCHAR(255);
  DECLARE v_temp_phone VARCHAR(50);
  DECLARE v_temp_cell VARCHAR(50);
  DECLARE v_temp_email VARCHAR(255);
  DECLARE v_temp_address_id INT;
  DECLARE v_temp_applicant_id INT;
  DECLARE v_temp_owner_id INT;
  DECLARE v_first_name VARCHAR(255);
  DECLARE v_last_name VARCHAR(255);
  DECLARE v_officer_name VARCHAR(255);
  DECLARE v_exec_id INT;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP, form_paid_bool = p_form_paid_bool, form_datetime_resolved = p_form_datetime_resolved, correction_form_id = p_correction_form_id WHERE form_id = p_form_id;
DELETE FROM hearing_forms WHERE form_id = p_form_id;
DELETE FROM applicants_link_forms WHERE form_id = p_form_id;
DELETE FROM owners_link_forms WHERE form_id = p_form_id;
DELETE FROM zoning_map_amendment_applications WHERE form_id = p_form_id;

COMMIT;
  START TRANSACTION;
  
  -- 1. Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- 2. Insert hearing information if provided
  IF p_docket_number IS NOT NULL OR p_public_hearing_date IS NOT NULL THEN
    -- Create/get attorney first if provided
    SET v_attorney_id = find_duplicate_attorney_id(
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
    IF v_attorney_id IS NULL AND (p_attorney_first_name IS NOT NULL OR p_attorney_last_name IS NOT NULL) THEN
      INSERT INTO attorneys(
        attorney_first_name, attorney_last_name, attorney_law_firm,
        attorney_email, attorney_phone, attorney_cell
      ) VALUES (
        p_attorney_first_name, p_attorney_last_name, p_law_firm,
        p_attorney_email, p_attorney_phone, p_attorney_cell
      );
      SET v_attorney_id = LAST_INSERT_ID();
    END IF;

    INSERT INTO hearing_forms(
      form_id, hearing_docket_number, hearing_date_application_filed,
      hearing_date, hearing_preapp_meeting_date, attorney_id
    ) VALUES (
      p_form_id, p_docket_number, p_date_application_filed,
      p_public_hearing_date, p_preapp_meeting_date, v_attorney_id
    );
  END IF;

  -- 3. Create property address if provided
  SET v_property_address_id = find_duplicate_address_id(p_property_street, p_property_city, p_property_state, p_property_zip_code);
  IF v_property_address_id IS NULL AND (p_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_property_street, p_property_city, p_property_state, p_property_zip_code);
    SET v_property_address_id = LAST_INSERT_ID();
  END IF;

  -- 4. Create property record if parcel number provided
  IF p_parcel_number IS NOT NULL THEN
    INSERT INTO properties(PVA_parcel_number, address_id, property_acreage, property_current_zoning)
      VALUES(p_parcel_number, v_property_address_id, p_acreage, p_current_zoning)
      ON DUPLICATE KEY UPDATE 
        address_id = v_property_address_id,
        property_acreage = p_acreage,
        property_current_zoning = p_current_zoning;
  END IF;

  -- 5. Insert primary applicant
  IF p_applicant_name IS NOT NULL THEN
    -- Create applicant address
    SET v_primary_applicant_address_id = find_duplicate_address_id(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
    IF v_primary_applicant_address_id IS NULL AND (p_applicant_street IS NOT NULL OR p_applicant_city IS NOT NULL) THEN
      INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
        VALUES(p_applicant_street, p_applicant_city, p_applicant_state, p_applicant_zip_code);
      SET v_primary_applicant_address_id = LAST_INSERT_ID();
    END IF;

    -- Parse first and last name from applicant_name
    SET v_first_name = SUBSTRING_INDEX(p_applicant_name, ' ', 1);
    SET v_last_name = SUBSTRING_INDEX(p_applicant_name, ' ', -1);
    IF v_first_name = v_last_name THEN
      SET v_last_name = '';
    END IF;
    
    INSERT INTO type_one_applicants(
      t1_applicant_first_name, t1_applicant_last_name, address_id,
      t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
    ) VALUES (
      v_first_name, v_last_name, v_primary_applicant_address_id,
      p_applicant_phone, p_applicant_cell, p_applicant_email
    );
    SET v_primary_applicant_id = LAST_INSERT_ID();

    -- Link applicant to form
    INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
      VALUES(v_primary_applicant_id, p_form_id);

    -- Parse and insert officers/directors from JSON
    IF p_officers_names IS NOT NULL AND JSON_VALID(p_officers_names) THEN
      SET v_count = JSON_LENGTH(p_officers_names);
      SET v_idx = 0;
      WHILE v_idx < v_count DO
        SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(p_officers_names, CONCAT('$[', v_idx, ']')));
        IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
          -- Parse officer name
          SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
          SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
          IF v_first_name = v_last_name THEN
            SET v_last_name = '';
          END IF;
          
          INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
            VALUES(v_first_name, v_last_name);
          SET v_exec_id = LAST_INSERT_ID();
          
          -- Link exec to applicant
          INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
            VALUES(v_exec_id, v_primary_applicant_id);
        END IF;
        SET v_idx = v_idx + 1;
      END WHILE;
    END IF;
  END IF;

  -- 6. Insert additional applicants from JSON arrays
  IF p_additional_applicant_names IS NOT NULL AND JSON_VALID(p_additional_applicant_names) THEN
    SET v_count = JSON_LENGTH(p_additional_applicant_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Extract other fields for this applicant
        SET v_temp_street = IF(JSON_VALID(p_additional_applicant_streets), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_streets, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_city = IF(JSON_VALID(p_additional_applicant_cities), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cities, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_state = IF(JSON_VALID(p_additional_applicant_states), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_states, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_zip = IF(JSON_VALID(p_additional_applicant_zip_codes), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_zip_codes, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_phone = IF(JSON_VALID(p_additional_applicant_phones), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_phones, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_cell = IF(JSON_VALID(p_additional_applicant_cells), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_cells, CONCAT('$[', v_idx, ']'))), NULL);
        SET v_temp_email = IF(JSON_VALID(p_additional_applicant_emails), JSON_UNQUOTE(JSON_EXTRACT(p_additional_applicant_emails, CONCAT('$[', v_idx, ']'))), NULL);
        
        -- Create address if provided
        SET v_temp_address_id = find_duplicate_address_id(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
        IF v_temp_address_id IS NULL AND (v_temp_street IS NOT NULL OR v_temp_city IS NOT NULL) THEN
          INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
            VALUES(v_temp_street, v_temp_city, v_temp_state, v_temp_zip);
          SET v_temp_address_id = LAST_INSERT_ID();
        END IF;
        
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert applicant
        INSERT INTO type_one_applicants(
          t1_applicant_first_name, t1_applicant_last_name, address_id,
          t1_applicant_phone_number, t1_applicant_cell_phone, t1_applicant_email
        ) VALUES (
          v_first_name, v_last_name, v_temp_address_id,
          v_temp_phone, v_temp_cell, v_temp_email
        );
        SET v_temp_applicant_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO applicants_link_forms(t1_applicant_id, form_id)
          VALUES(v_temp_applicant_id, p_form_id);
        
        -- Handle officers for this additional applicant if they exist
        -- Officers are stored in p_additional_applicant_officers as {"0": ["name1", "name2"], "1": ["name3"]}
        IF p_additional_applicant_officers IS NOT NULL AND JSON_VALID(p_additional_applicant_officers) THEN
          SET @officer_key = CAST(v_idx AS CHAR);
          IF JSON_CONTAINS_PATH(p_additional_applicant_officers, 'one', CONCAT('$.', @officer_key)) THEN
            SET @officer_array = JSON_EXTRACT(p_additional_applicant_officers, CONCAT('$.', @officer_key));
            IF JSON_VALID(@officer_array) THEN
              SET @officer_count = JSON_LENGTH(@officer_array);
              SET @officer_idx = 0;
              WHILE @officer_idx < @officer_count DO
                SET v_officer_name = JSON_UNQUOTE(JSON_EXTRACT(@officer_array, CONCAT('$[', @officer_idx, ']')));
                IF v_officer_name IS NOT NULL AND v_officer_name != '' THEN
                  SET v_first_name = SUBSTRING_INDEX(v_officer_name, ' ', 1);
                  SET v_last_name = SUBSTRING_INDEX(v_officer_name, ' ', -1);
                  IF v_first_name = v_last_name THEN
                    SET v_last_name = '';
                  END IF;
                  
                  INSERT INTO type_one_execs(t1e_exec_first_name, t1e_exec_last_name)
                    VALUES(v_first_name, v_last_name);
                  SET v_exec_id = LAST_INSERT_ID();
                  
                  INSERT INTO type_one_applicant_execs(t1e_exec_id, t1_applicant_id)
                    VALUES(v_exec_id, v_temp_applicant_id);
                END IF;
                SET @officer_idx = @officer_idx + 1;
              END WHILE;
            END IF;
          END IF;
        END IF;
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 7. Insert primary property owner
  IF p_owner_first_name IS NOT NULL OR p_owner_last_name IS NOT NULL THEN
    INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
      VALUES(p_owner_first_name, p_owner_last_name);
    SET v_primary_owner_id = LAST_INSERT_ID();

    -- Link owner to form
    INSERT INTO owners_link_forms(t1_owner_id, form_id)
      VALUES(v_primary_owner_id, p_form_id);
  END IF;

  -- 8. Insert additional owners from JSON arrays
  IF p_additional_owner_names IS NOT NULL AND JSON_VALID(p_additional_owner_names) THEN
    SET v_count = JSON_LENGTH(p_additional_owner_names);
    SET v_idx = 0;
    WHILE v_idx < v_count DO
      SET v_temp_name = JSON_UNQUOTE(JSON_EXTRACT(p_additional_owner_names, CONCAT('$[', v_idx, ']')));
      
      IF v_temp_name IS NOT NULL AND v_temp_name != '' THEN
        -- Parse name
        SET v_first_name = SUBSTRING_INDEX(v_temp_name, ' ', 1);
        SET v_last_name = SUBSTRING_INDEX(v_temp_name, ' ', -1);
        IF v_first_name = v_last_name THEN
          SET v_last_name = '';
        END IF;
        
        -- Insert owner
        INSERT INTO type_one_owners(t1o_owner_first_name, t1o_owner_last_name)
          VALUES(v_first_name, v_last_name);
        SET v_temp_owner_id = LAST_INSERT_ID();
        
        -- Link to form
        INSERT INTO owners_link_forms(t1_owner_id, form_id)
          VALUES(v_temp_owner_id, p_form_id);
      END IF;
      SET v_idx = v_idx + 1;
    END WHILE;
  END IF;

  -- 9. Insert into zoning_map_amendment_applications
  INSERT INTO zoning_map_amendment_applications(
    form_id,
    zoning_map_amendment_request,
    zmaa_proposed_conditions,
    PVA_parcel_number
  ) VALUES (
    p_form_id,
    p_zoning_map_amendment_request,
    p_zmaa_proposed_conditions,
    p_parcel_number
  );

  COMMIT;
  
  -- Return the new form_id
  SELECT p_form_id AS form_id;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_zoning_map_amendment_application TO 'webuser'@'localhost';


DELIMITER $$
DROP PROCEDURE IF EXISTS sp_update_zoning_verification_application$$
CREATE PROCEDURE sp_update_zoning_verification_application(IN p_form_id INT,
IN p_form_datetime_resolved DATETIME,
IN p_form_paid_bool BOOLEAN,
IN p_correction_form_id INT,
IN p_zva_letter_content TEXT,
-- zoning address fields
IN p_zva_zoning_letter_street VARCHAR(255),
IN p_zva_zoning_letter_city VARCHAR(255),
IN p_zva_state_code CHAR(2),
IN p_zva_zoning_letter_zip VARCHAR(255),
-- property address fields
IN p_zva_property_street VARCHAR(255),
IN p_property_city VARCHAR(255),
IN p_zva_property_state_code CHAR(2),
IN p_zva_property_zip VARCHAR(255),
-- applicant fields
IN p_zva_applicant_first_name VARCHAR(255),
IN p_zva_applicant_last_name VARCHAR(255),
IN p_zva_applicant_street VARCHAR(255),
IN p_zva_applicant_city VARCHAR(255),
IN p_zva_applicant_state_code CHAR(2),
IN p_zva_applicant_zip_code VARCHAR(255),
IN p_zva_applicant_phone_number VARCHAR(50),
IN p_zva_applicant_fax_number VARCHAR(255),
-- property owner fields
IN p_zva_owner_first_name VARCHAR(255),
IN p_zva_owner_last_name VARCHAR(255),
IN p_zva_owner_street VARCHAR(255),
IN p_zva_owner_city VARCHAR(255),
IN p_zva_owner_state_code CHAR(2),
IN p_zva_owner_zip_code VARCHAR(255))
BEGIN
DECLARE v_zva_zoning_address_id INT DEFAULT NULL;
DECLARE v_zva_property_address_id INT DEFAULT NULL;
DECLARE v_zva_applicant_address_id INT DEFAULT NULL;
DECLARE v_zva_owner_address_id INT DEFAULT NULL;
DECLARE v_zva_applicant_id INT DEFAULT NULL;
DECLARE v_zva_owner_id INT DEFAULT NULL;
START TRANSACTION;
UPDATE forms SET form_datetime_submitted = CURRENT_TIMESTAMP, form_paid_bool = p_form_paid_bool, form_datetime_resolved = p_form_datetime_resolved, correction_form_id = p_correction_form_id WHERE form_id = p_form_id;
DELETE FROM zoning_verification_letter WHERE form_id = p_form_id;

  START TRANSACTION;
  
  -- Insert into forms table
  -- (removed forms insert in update) ;
  -- (removed new_form_id in update)

  -- Create zoning address if provided
  SET v_zva_zoning_address_id = find_duplicate_address_id(p_zva_zoning_letter_street, p_zva_zoning_letter_city, p_zva_state_code, p_zva_zoning_letter_zip);
  IF v_zva_zoning_address_id IS NULL AND (p_zva_zoning_letter_street IS NOT NULL OR p_zva_zoning_letter_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_zva_zoning_letter_street, p_zva_zoning_letter_city, p_zva_state_code, p_zva_zoning_letter_zip);
    SET v_zva_zoning_address_id = LAST_INSERT_ID();
  END IF;

  -- Create property address if provided
  SET v_zva_property_address_id = find_duplicate_address_id(p_zva_property_street, p_property_city, p_zva_property_state_code, p_zva_property_zip);
  IF v_zva_property_address_id IS NULL AND (p_zva_property_street IS NOT NULL OR p_property_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_zva_property_street, p_property_city, p_zva_property_state_code, p_zva_property_zip);
    SET v_zva_property_address_id = LAST_INSERT_ID();
  END IF;

  -- Create applicant address if provided
  SET v_zva_applicant_address_id = find_duplicate_address_id(p_zva_applicant_street, p_zva_applicant_city, p_zva_applicant_state_code, p_zva_applicant_zip_code);
  IF p_zva_applicant_street IS NOT NULL OR p_zva_applicant_city IS NOT NULL THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_zva_applicant_street, p_zva_applicant_city, p_zva_applicant_state_code, p_zva_applicant_zip_code);
    SET v_zva_applicant_address_id = LAST_INSERT_ID();
  END IF;

  -- Create applicant if provided
  IF p_zva_applicant_first_name IS NOT NULL OR p_zva_applicant_last_name IS NOT NULL THEN
    INSERT INTO zva_applicants(
      zva_applicant_first_name, zva_applicant_last_name, address_id,
      zva_applicant_phone_number, zva_applicant_fax_number
    ) VALUES (
      p_zva_applicant_first_name, p_zva_applicant_last_name, v_zva_applicant_address_id,
      p_zva_applicant_phone_number, p_zva_applicant_fax_number
    );
    SET v_zva_applicant_id = LAST_INSERT_ID();
  END IF;

  -- Create owner address if provided
  SET v_zva_owner_address_id = find_duplicate_address_id(p_zva_owner_street, p_zva_owner_city, p_zva_owner_state_code, p_zva_owner_zip_code);
  IF v_zva_owner_address_id IS NULL AND (p_zva_owner_street IS NOT NULL OR p_zva_owner_city IS NOT NULL) THEN
    INSERT INTO addresses(address_street, address_city, state_code, address_zip_code)
      VALUES(p_zva_owner_street, p_zva_owner_city, p_zva_owner_state_code, p_zva_owner_zip_code);
    SET v_zva_owner_address_id = LAST_INSERT_ID();
  END IF;

  -- Create property owner if provided
  IF p_zva_owner_first_name IS NOT NULL OR p_zva_owner_last_name IS NOT NULL THEN
    INSERT INTO zva_property_owners(
      zva_owner_first_name, zva_owner_last_name, address_id
    ) VALUES (
      p_zva_owner_first_name, p_zva_owner_last_name, v_zva_owner_address_id
    );
    SET v_zva_owner_id = LAST_INSERT_ID();
  END IF;

  -- Insert into zoning_verification_letter with proper foreign keys
  INSERT INTO zoning_verification_letter(
    form_id, zva_owner_id, zva_applicant_id, zva_letter_content,
    zva_zoning_address_id, zva_property_address_id
  ) VALUES (
    p_form_id, v_zva_owner_id, v_zva_applicant_id, p_zva_letter_content,
    v_zva_zoning_address_id, v_zva_property_address_id
  );

  COMMIT;
COMMIT;
SELECT p_form_id AS form_id;
END$$
DELIMITER ;
GRANT EXECUTE ON PROCEDURE sp_update_zoning_verification_application TO 'webuser'@'localhost';
