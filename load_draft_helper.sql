-- Helper procedure to get adjacent property owner form draft data as JSON
DROP PROCEDURE IF EXISTS sp_get_adjacent_property_owners_draft$$
CREATE PROCEDURE sp_get_adjacent_property_owners_draft(
  IN p_form_id INT
)
BEGIN
  SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- PVA map codes as JSON array
    JSON_ARRAYAGG(JSON_QUOTE(COALESCE(n.PVA_map_code, ''))) AS pva_map_codes,
    -- Property locations as JSON array
    JSON_ARRAYAGG(JSON_QUOTE(COALESCE(n.apof_neighbor_property_location, ''))) AS neighbor_property_locations,
    -- Deed books as JSON array
    JSON_ARRAYAGG(JSON_QUOTE(COALESCE(n.apof_neighbor_property_deed_book, ''))) AS neighbor_property_deed_books,
    -- Page numbers as JSON array
    JSON_ARRAYAGG(JSON_QUOTE(COALESCE(n.apof_property_street_pg_number, ''))) AS property_street_pg_numbers
  FROM forms f
  LEFT JOIN adjacent_property_owner_forms apof ON f.form_id = apof.form_id
  LEFT JOIN adjacent_neighbors an ON f.form_id = an.form_id
  LEFT JOIN apof_neighbors n ON an.neighbor_id = n.neighbor_id
  WHERE f.form_id = p_form_id AND f.form_type = 'Adjacent Property Owners Form'
  GROUP BY f.form_id;
END$$
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_get_adjacent_property_owners_draft TO 'webuser'@'localhost';
