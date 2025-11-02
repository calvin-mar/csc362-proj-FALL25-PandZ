-- ===================================================================
-- P&Z DATABASE VIEWS
-- Description: All useful pre-built views for analysis, reporting,
--              and staff operations within the Planning & Zoning DB.
-- ===================================================================

-- ============================================================
-- 🔹 GENERAL OVERVIEW & CLIENT RELATIONSHIP VIEWS
-- ============================================================

-- 1. Master Form Overview
CREATE OR REPLACE VIEW v_all_forms AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    f.created_at,
    f.updated_at,
    d.department_name
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN departments d ON f.department_id = d.department_id;

-- 2. Client Information Overview
CREATE OR REPLACE VIEW v_clients_summary AS
SELECT 
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    c.phone,
    c.email,
    c.address,
    s.state_name
FROM clients c
LEFT JOIN states s ON c.state_code = s.state_code;

-- 3. Form-to-Client Relationship
CREATE OR REPLACE VIEW v_form_clients AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    c.email,
    c.phone,
    CASE 
        WHEN icf.form_id IS NOT NULL THEN 'Incomplete'
        ELSE 'Complete'
    END AS form_status
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN incomplete_client_forms icf ON f.form_id = icf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type;

-- ============================================================
-- 🔹 ZONING & DEVELOPMENT APPLICATION VIEWS
-- ============================================================

-- 4. Active Zoning Permit Applications
CREATE OR REPLACE VIEW v_zoning_permit_applications AS
SELECT 
    f.form_id,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    zpa.application_date,
    zpa.status,
    p.parcel_number,
    p.address,
    p.city,
    s.state_name
FROM zoning_permit_applications zpa
JOIN forms f ON f.form_id = zpa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON zpa.property_id = p.property_id
LEFT JOIN states s ON p.state_code = s.state_code
WHERE zpa.status IS NULL OR zpa.status NOT IN ('Rejected', 'Withdrawn');

-- 5. Subdivision Plat Applications (Major + Minor)
CREATE OR REPLACE VIEW v_all_subdivision_applications AS
SELECT 
    f.form_id,
    'Major Subdivision' AS subdivision_type,
    mspa.submission_date,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    p.parcel_number,
    p.city,
    s.state_name
FROM major_subdivision_plat_applications mspa
JOIN forms f ON f.form_id = mspa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mspa.property_id = p.property_id
LEFT JOIN states s ON p.state_code = s.state_code

UNION ALL

SELECT 
    f.form_id,
    'Minor Subdivision' AS subdivision_type,
    mispa.submission_date,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    p.parcel_number,
    p.city,
    s.state_name
FROM minor_subdivision_plat_applications mispa
JOIN forms f ON f.form_id = mispa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mispa.property_id = p.property_id
LEFT JOIN states s ON p.state_code = s.state_code;

-- 6. Site Development & General Development Plans
CREATE OR REPLACE VIEW v_development_applications AS
SELECT 
    f.form_id,
    'Site Development Plan' AS plan_type,
    sdp.application_date,
    sdp.status,
    p.parcel_number,
    p.city,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM site_development_plan_applications sdp
JOIN forms f ON f.form_id = sdp.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON sdp.property_id = p.property_id

UNION ALL

SELECT 
    f.form_id,
    'General Development Plan' AS plan_type,
    gdpa.application_date,
    gdpa.status,
    p.parcel_number,
    p.city,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM general_development_plan_applications gdpa
JOIN forms f ON f.form_id = gdpa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON gdpa.property_id = p.property_id;

-- 7. Sign Permit Applications + Associated Signs
CREATE OR REPLACE VIEW v_sign_permits AS
SELECT 
    spa.form_id,
    spa.application_date,
    spa.status,
    s.sign_id,
    s.sign_type,
    s.height,
    s.width,
    s.location_description,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM sign_permit_applications spa
LEFT JOIN permits_link_signs pls ON spa.form_id = pls.form_id
LEFT JOIN signs s ON pls.sign_id = s.sign_id
LEFT JOIN client_forms cf ON spa.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- ============================================================
-- 🔹 DEPARTMENTAL, RECORDS, & SUMMARY REPORTS
-- ============================================================

-- 8. All Active Applications by Department
CREATE OR REPLACE VIEW v_department_applications AS
SELECT 
    d.department_name,
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    f.created_at
FROM forms f
LEFT JOIN departments d ON f.department_id = d.department_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type
WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- 9. Open Records and Appeals Summary
CREATE OR REPLACE VIEW v_records_and_appeals AS
SELECT 
    f.form_id,
    'Open Record Request' AS request_type,
    orr.request_date,
    orr.status,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM open_record_requests orr
JOIN forms f ON f.form_id = orr.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id

UNION ALL

SELECT 
    f.form_id,
    'Administrative Appeal' AS request_type,
    aar.submission_date,
    aar.status,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM administrative_appeal_requests aar
JOIN forms f ON f.form_id = aar.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- 10. Comprehensive Application Summary
CREATE OR REPLACE VIEW v_application_summary AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    d.department_name,
    p.city,
    s.state_name,
    f.created_at
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN departments d ON f.department_id = d.department_id
LEFT JOIN properties p ON f.property_id = p.property_id
LEFT JOIN states s ON p.state_code = s.state_code;

-- ============================================================
-- 🔹 ADVANCED ANALYTICAL / REPORTING VIEWS
-- ============================================================

-- 11. Reviewer Workload Overview
CREATE OR REPLACE VIEW v_reviewer_workload AS
SELECT 
    cb.correction_box_reviewer AS reviewer_name,
    COUNT(cf.correction_form_id) AS assigned_corrections
FROM correction_boxes cb
LEFT JOIN correction_forms cf 
    ON cb.correction_box_id = cf.correction_box_id
GROUP BY cb.correction_box_reviewer;

-- 12. Correction Activity Summary
CREATE OR REPLACE VIEW v_correction_summary AS
SELECT 
    f.form_id,
    ft.form_type AS form_type_name,
    cb.correction_box_reviewer AS reviewer_name,
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    cf.created_at AS correction_created_at
FROM correction_forms cf
JOIN forms f ON cf.form_id = f.form_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN correction_boxes cb ON cf.correction_box_id = cb.correction_box_id
LEFT JOIN client_forms clf ON f.form_id = clf.form_id
LEFT JOIN clients c ON clf.client_id = c.client_id;

-- 13. Project Type Overview
CREATE OR REPLACE VIEW v_project_type_counts AS
SELECT 
    pt.project_type_name,
    COUNT(f.form_id) AS total_forms
FROM forms f
LEFT JOIN project_types pt ON f.project_type_id = pt.project_type_id
GROUP BY pt.project_type_name
ORDER BY total_forms DESC;

-- 14. Property-Based Application Summary
CREATE OR REPLACE VIEW v_property_applications AS
SELECT 
    p.property_id,
    p.parcel_number,
    p.address,
    p.city,
    s.state_name,
    f.form_id,
    ft.form_type AS form_type_name,
    f.created_at,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name
FROM properties p
LEFT JOIN forms f ON p.property_id = f.property_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN states s ON p.state_code = s.state_code
ORDER BY p.property_id, f.created_at DESC;

-- 15. Incomplete Forms Tracker
CREATE OR REPLACE VIEW v_incomplete_forms AS
SELECT 
    icf.form_id,
    ft.form_type AS form_type_name,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    c.email,
    c.phone
FROM incomplete_client_forms icf
JOIN forms f ON icf.form_id = f.form_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN clients c ON icf.client_id = c.client_id;

-- 16. Departmental Workload Summary
CREATE OR REPLACE VIEW v_department_workload AS
SELECT 
    d.department_name,
    COUNT(f.form_id) AS total_forms,
    SUM(CASE WHEN icf.form_id IS NOT NULL THEN 1 ELSE 0 END) AS incomplete_forms
FROM departments d
LEFT JOIN forms f ON d.department_id = f.department_id
LEFT JOIN incomplete_client_forms icf ON f.form_id = icf.form_id
GROUP BY d.department_name;

-- 17. Recent Submissions (Last 30 Days)
CREATE OR REPLACE VIEW v_recent_forms AS
SELECT 
    f.form_id,
    ft.form_type AS form_type_name,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    f.created_at,
    f.updated_at
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
   OR f.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY f.created_at DESC;

-- 18. Application Timeline View
CREATE OR REPLACE VIEW v_application_timeline AS
SELECT 
    f.form_id,
    ft.form_type AS form_type_name,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    f.created_at AS form_created,
    cf.created_at AS correction_created,
    COALESCE(zpa.application_date, mspa.submission_date, sdp.application_date, gdpa.application_date) AS application_date,
    COALESCE(zpa.status, mspa.status, sdp.status, gdpa.status) AS application_status
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms clf ON f.form_id = clf.form_id
LEFT JOIN clients c ON clf.client_id = c.client_id
LEFT JOIN correction_forms cf ON f.form_id = cf.form_id
LEFT JOIN zoning_permit_applications zpa ON f.form_id = zpa.form_id
LEFT JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id
LEFT JOIN site_development_plan_applications sdp ON f.form_id = sdp.form_id
LEFT JOIN general_development_plan_applications gdpa ON f.form_id = gdpa.form_id;

-- 19. Form Type Usage Statistics
CREATE OR REPLACE VIEW v_form_type_usage AS
SELECT 
    ft.form_type AS form_type_name,
    COUNT(f.form_id) AS total_submissions
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
GROUP BY ft.form_type
ORDER BY total_submissions DESC;

-- 20. Client Activity Summary
CREATE OR REPLACE VIEW v_client_activity AS
SELECT 
    c.client_id,
    CONCAT(c.first_name, ' ', c.last_name) AS client_name,
    COUNT(DISTINCT cf.form_id) AS total_forms,
    COUNT(DISTINCT icf.form_id) AS incomplete_forms,
    MAX(f.created_at) AS last_submission
FROM clients c
LEFT JOIN client_forms cf ON c.client_id = cf.client_id
LEFT JOIN incomplete_client_forms icf ON c.client_id = icf.client_id
LEFT JOIN forms f ON cf.form_id = f.form_id
GROUP BY c.client_id, c.first_name, c.last_name
ORDER BY last_submission DESC;

-- ============================================================
-- FORM SPECIFIC VIEWS
-- ============================================================

-- Administrative Appeal Request
CREATE VIEW vw_administrative_appeal_requests AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    a.request_reason,
    a.appellant_name,
    a.appeal_date
FROM forms f
JOIN administrative_appeal_requests a ON f.form_id = a.form_id;

-- Adjacent Property Owner Form
CREATE VIEW vw_adjacent_property_owner_forms AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    ap.property_owner_name,
    ap.property_address,
    ap.owner_mail_city,
    ap.owner_mail_state,
    ap.owner_mail_zip
FROM forms f
JOIN adjacent_property_owner_forms ap ON f.form_id = ap.form_id;

-- Conditional Use Permit Application
CREATE VIEW vw_conditional_use_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.project_name,
    c.property_street,
    c.property_city,
    c.property_state_code,
    c.property_zip,
    c.conditional_use_details
FROM forms f
JOIN conditional_use_permit_applications c ON f.form_id = c.form_id;

-- Development Plan Application (General)
CREATE VIEW vw_general_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    g.project_name,
    g.plan_summary,
    g.engineer_id,
    g.architect_id,
    g.contractor_id
FROM forms f
JOIN general_development_plan_applications g ON f.form_id = g.form_id;

-- Development Plan Application (Site)
CREATE VIEW vw_site_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    s.project_name,
    s.site_location,
    s.plan_description,
    s.engineer_id
FROM forms f
JOIN site_development_plan_applications s ON f.form_id = s.form_id;

-- Future Land Use Map (FLUM) Application
CREATE VIEW vw_future_land_use_map_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    fl.project_name,
    fl.existing_land_use,
    fl.proposed_land_use,
    fl.map_area_description
FROM forms f
JOIN future_land_use_map_applications fl ON f.form_id = fl.form_id;

-- Open Records Request
CREATE VIEW vw_open_record_requests AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    o.requester_name,
    o.request_description,
    o.request_datetime_fulfilled
FROM forms f
JOIN open_record_requests o ON f.form_id = o.form_id;

-- Sign Permit Application
CREATE VIEW vw_sign_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    s.project_name,
    s.sign_location,
    s.sign_dimensions,
    s.sign_type,
    s.sign_text
FROM forms f
JOIN sign_permit_applications s ON f.form_id = s.form_id;

-- Major Subdivision Plat Application
CREATE VIEW vw_major_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    m.project_name,
    m.subdivision_name,
    m.number_of_lots,
    m.engineer_id,
    m.surveyor_id
FROM forms f
JOIN major_subdivision_plat_applications m ON f.form_id = m.form_id;

-- Minor Subdivision Plat Application
CREATE VIEW vw_minor_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    m.project_name,
    m.subdivision_name,
    m.number_of_lots,
    m.engineer_id,
    m.surveyor_id
FROM forms f
JOIN minor_subdivision_plat_applications m ON f.form_id = m.form_id;

-- Telecommunication Tower Uniform Application
CREATE VIEW vw_telecommunication_tower_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    t.tower_location,
    t.tower_height,
    t.tower_type,
    t.provider_name
FROM forms f
JOIN telecommunication_tower_uniform_applications t ON f.form_id = t.form_id;

-- Variance Application
CREATE VIEW vw_variance_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    v.project_name,
    v.property_street,
    v.property_city,
    v.property_state_code,
    v.property_zip,
    v.variance_reason,
    v.justification
FROM forms f
JOIN variance_applications v ON f.form_id = v.form_id;

-- Zoning Map Amendment Application
CREATE VIEW vw_zoning_map_amendment_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    z.project_name,
    z.current_zoning,
    z.proposed_zoning,
    z.property_street,
    z.property_city,
    z.property_state_code,
    z.property_zip
FROM forms f
JOIN zoning_map_amendment_applications z ON f.form_id = z.form_id;

-- Zoning Permit Application
CREATE VIEW vw_zoning_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    zp.project_name,
    zp.property_street,
    zp.property_city,
    zp.property_state_code,
    zp.property_zip,
    zp.zoning_permit_type,
    zp.zoning_permit_description
FROM forms f
JOIN zoning_permit_applications zp ON f.form_id = zp.form_id;

-- Zoning Verification Letter
CREATE VIEW vw_zoning_verification_letter AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    z.zva_letter_content,
    z.zva_zoning_letter_street,
    z.zva_zoning_letter_city,
    z.zva_property_street,
    z.zva_property_state_code,
    z.zva_property_zip,
    z.property_city,
    z.zva_letter_reviewer,
    z.zva_letter_datetime_sent
FROM forms f
JOIN zoning_verification_letter z ON f.form_id = z.form_id;

-- ============================================================
-- ✅ END OF VIEWS SECTION
-- ============================================================
