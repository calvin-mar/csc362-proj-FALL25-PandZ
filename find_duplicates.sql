-- Creates and replaces duplicate functions
DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_surveyor_id(
    s_first VARCHAR(255),
    s_last VARCHAR(255),
    s_firm VARCHAR(255),
    s_email VARCHAR(255),
    s_phone VARCHAR(50),
    s_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT surveyor_id INTO result
    FROM surveyors s
    WHERE (s.surveyor_first_name = s_first OR (s.surveyor_first_name IS NULL AND s_first IS NULL))
      AND (s.surveyor_last_name = s_last OR (s.surveyor_last_name IS NULL AND s_last IS NULL))
      AND (s.surveyor_firm = s_firm OR (s.surveyor_firm IS NULL AND s_firm IS NULL))
      AND (s.surveyor_email = s_email OR (s.surveyor_email IS NULL AND s_email IS NULL))
      AND (s.surveyor_phone = s_phone OR (s.surveyor_phone IS NULL AND s_phone IS NULL))
      AND (s.surveyor_cell = s_cell OR (s.surveyor_cell IS NULL AND s_cell IS NULL))
    LIMIT 1;
    
    RETURN result;
END //
DELIMITER ;


DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_engineer_id(
    e_first VARCHAR(255),
    e_last VARCHAR(255),
    e_firm VARCHAR(255),
    e_email VARCHAR(255),
    e_phone VARCHAR(50),
    e_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT engineer_id INTO result
    FROM engineers e
    WHERE (e.engineer_first_name = e_first OR (e.engineer_first_name IS NULL AND e_first IS NULL))
      AND (e.engineer_last_name = e_last OR (e.engineer_last_name IS NULL AND e_last IS NULL))
      AND (e.engineer_firm = e_firm OR (e.engineer_firm IS NULL AND e_firm IS NULL))
      AND (e.engineer_email = e_email OR (e.engineer_email IS NULL AND e_email IS NULL))
      AND (e.engineer_phone = e_phone OR (e.engineer_phone IS NULL AND e_phone IS NULL))
      AND (e.engineer_cell = e_cell OR (e.engineer_cell IS NULL AND e_cell IS NULL))
    LIMIT 1;
    
    RETURN result;
END //
DELIMITER ;

DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_architect_id(
    a_first VARCHAR(255),
    a_last VARCHAR(255),
    a_firm VARCHAR(255),
    a_email VARCHAR(255),
    a_phone VARCHAR(50),
    a_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT architect_id INTO result
    FROM architects a
    WHERE (a.architect_first_name = a_first OR (a.architect_first_name IS NULL AND a_first IS NULL))
      AND (a.architect_last_name = a_last OR (a.architect_last_name IS NULL AND a_last IS NULL))
      AND (a.architect_firm = a_firm OR (a.architect_firm IS NULL AND a_firm IS NULL))
      AND (a.architect_email = a_email OR (a.architect_email IS NULL AND a_email IS NULL))
      AND (a.architect_phone = a_phone OR (a.architect_phone IS NULL AND a_phone IS NULL))
      AND (a.architect_cell = a_cell OR (a.architect_cell IS NULL AND a_cell IS NULL))
    LIMIT 1;
    
    RETURN result;
END //
DELIMITER ;

DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_land_architect_id(
    la_first VARCHAR(255),
    la_last VARCHAR(255),
    la_firm VARCHAR(255),
    la_email VARCHAR(255),
    la_phone VARCHAR(50),
    la_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT land_architect_id INTO result
    FROM land_architects la
    WHERE (la.land_architect_first_name = la_first OR (la.land_architect_first_name IS NULL AND la_first IS NULL))
      AND (la.land_architect_last_name = la_last OR (la.land_architect_last_name IS NULL AND la_last IS NULL))
      AND (la.land_architect_firm = la_firm OR (la.land_architect_firm IS NULL AND la_firm IS NULL))
      AND (la.land_architect_email = la_email OR (la.land_architect_email IS NULL AND la_email IS NULL))
      AND (la.land_architect_phone = la_phone OR (la.land_architect_phone IS NULL AND la_phone IS NULL))
      AND (la.land_architect_cell = la_cell OR (la.land_architect_cell IS NULL AND la_cell IS NULL))
    LIMIT 1;
    
    RETURN result;
END //
DELIMITER ;

DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_contractor_id(
    c_first VARCHAR(255),
    c_last VARCHAR(255),
    c_firm VARCHAR(255),
    c_email VARCHAR(255),
    c_phone VARCHAR(50),
    c_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT contractor_id INTO result
    FROM contractors c
    WHERE (c.contractor_first_name = c_first OR (c.contractor_first_name IS NULL AND c_first IS NULL))
      AND (c.contractor_last_name = c_last OR (c.contractor_last_name IS NULL AND c_last IS NULL))
      AND (c.contractor_firm = c_firm OR (c.contractor_firm IS NULL AND c_firm IS NULL))
      AND (c.contractor_email = c_email OR (c.contractor_email IS NULL AND c_email IS NULL))
      AND (c.contractor_phone = c_phone OR (c.contractor_phone IS NULL AND c_phone IS NULL))
      AND (c.contractor_cell = c_cell OR (c.contractor_cell IS NULL AND c_cell IS NULL))
    LIMIT 1;
    
    RETURN result;
END //
DELIMITER ;

DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_attorney_id(
    at_first VARCHAR(255),
    at_last VARCHAR(255),
    at_firm VARCHAR(255),
    at_email VARCHAR(255),
    at_phone VARCHAR(50),
    at_cell VARCHAR(50)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT attorney_id INTO result
    FROM attorneys att
    WHERE (att.attorney_first_name = at_first OR (att.attorney_first_name IS NULL AND at_first IS NULL))
      AND (att.attorney_last_name = at_last OR (att.attorney_last_name IS NULL AND at_last IS NULL))
      AND (att.attorney_law_firm = at_firm OR (att.attorney_firm IS NULL AND at_firm IS NULL))
      AND (att.attorney_email = at_email OR (att.attorney_email IS NULL AND at_email IS NULL))
      AND (att.attorney_phone = at_phone OR (att.attorney_phone IS NULL AND at_phone IS NULL))
      AND (att.attorney_cell = at_cell OR (att.attorney_cell IS NULL AND at_cell IS NULL))
    LIMIT 1;
    RETURN result;
END //
DELIMITER ;

DELIMITER //
CREATE OR REPLACE FUNCTION find_duplicate_address_id(
    add_str VARCHAR(255),
    add_city VARCHAR(255),
    add_state VARCHAR(2),
    add_zip VARCHAR(255)
)
RETURNS INT
BEGIN
    DECLARE result INT DEFAULT NULL;
    SELECT address_id INTO result
    FROM addresses
    LEFT JOIN states USING(state_code)
    WHERE (addresses.address_street = add_str OR (addresses.address_street IS NULL AND add_str IS NULL))
      AND (addresses.address_city = add_city OR (addresses.address_city IS NULL AND add_city IS NULL))
      AND (states.state_code = add_state OR (states.state_code IS NULL AND add_state IS NULL))
      AND (addresses.address_zip_code = add_zip OR (addresses.address_zip_code IS NULL AND add_zip IS NULL))
    LIMIT 1;
    RETURN result;
END //
DELIMITER ;
