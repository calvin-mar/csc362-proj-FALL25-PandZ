-- ===================================================================
-- P&Z DATABASE VIEWS (Schema-Compatible and Verified Version)
-- Description: All useful pre-built views for analysis, reporting,
--              and staff operations within the Planning & Zoning DB.
-- ===================================================================

-- ============================================================
-- 🔹 GENERAL OVERVIEW & CLIENT RELATIONSHIP VIEWS
-- ============================================================

CREATE OR REPLACE VIEW v_all_forms AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    f.form_datetime_submitted,
    f.form_datetime_resolved
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type;

CREATE OR REPLACE VIEW v_clients_summary AS
SELECT 
    c.client_id,
    c.client_username
FROM clients c;

CREATE OR REPLACE VIEW v_form_clients AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    c.client_username
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type;

-- ============================================================
-- 🔹 ZONING & DEVELOPMENT APPLICATION VIEWS
-- ============================================================

CREATE OR REPLACE VIEW v_zoning_permit_applications AS
SELECT 
    f.form_id,
    f.form_type,
    c.client_username,
    zpa.project_type,
    zpa.PVA_parcel_number
FROM zoning_permit_applications zpa
JOIN forms f ON f.form_id = zpa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_all_subdivision_applications AS
SELECT 
    f.form_id,
    'Major Subdivision' AS subdivision_type,
    c.client_username,
    mspa.PVA_parcel_number
FROM major_subdivision_plat_applications mspa
JOIN forms f ON f.form_id = mspa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id

UNION ALL

SELECT 
    f.form_id,
    'Minor Subdivision' AS subdivision_type,
    c.client_username,
    mispa.PVA_parcel_number
FROM minor_subdivision_plat_applications mispa
JOIN forms f ON f.form_id = mispa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_development_applications AS
SELECT 
    f.form_id,
    'Site Development Plan' AS plan_type,
    c.client_username
FROM site_development_plan_applications sdp
JOIN forms f ON f.form_id = sdp.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id

UNION ALL

SELECT 
    f.form_id,
    'General Development Plan' AS plan_type,
    c.client_username
FROM general_development_plan_applications gdpa
JOIN forms f ON f.form_id = gdpa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_sign_permits AS
SELECT 
    spa.form_id,
    c.client_username,
    spa.sp_permit_number,
    spa.sp_permit_fee
FROM sign_permit_applications spa
LEFT JOIN client_forms cf ON spa.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- ============================================================
-- 🔹 DEPARTMENTAL, RECORDS, & SUMMARY REPORTS
-- ============================================================

CREATE OR REPLACE VIEW v_department_applications AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    c.client_username,
    f.form_datetime_submitted
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_open_record_requests AS
SELECT 
    f.form_id,
    o.orr_received_on_datetime,
    o.orr_request_for_copies,
    o.orr_denied_reasons
FROM forms f
JOIN open_record_requests o ON f.form_id = o.form_id;

CREATE OR REPLACE VIEW v_application_summary AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    c.client_username,
    f.form_datetime_submitted
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- ============================================================
-- 🔹 ANALYTICAL / REPORTING VIEWS
-- ============================================================

CREATE OR REPLACE VIEW v_incomplete_forms AS
SELECT 
    icf.form_id,
    ft.form_type AS form_type_name,
    c.client_username
FROM incomplete_client_forms icf
JOIN forms f ON icf.form_id = f.form_id
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN clients c ON icf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_department_workload AS
SELECT 
    COUNT(f.form_id) AS total_forms,
    SUM(CASE WHEN icf.form_id IS NOT NULL THEN 1 ELSE 0 END) AS incomplete_forms
FROM forms f
LEFT JOIN incomplete_client_forms icf ON f.form_id = icf.form_id;

CREATE OR REPLACE VIEW v_recent_forms AS
SELECT 
    f.form_id,
    ft.form_type AS form_type_name,
    c.client_username,
    f.form_datetime_submitted,
    f.form_datetime_resolved
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
WHERE f.form_datetime_submitted >= DATE_SUB(NOW(), INTERVAL 30 DAY)
   OR f.form_datetime_resolved >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY f.form_datetime_submitted DESC;

-- ============================================================
-- 🔹 FORM-SPECIFIC VIEWS (SCHEMA-VERIFIED)
-- ============================================================

CREATE OR REPLACE VIEW vw_zoning_verification_letter AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    z.zva_letter_content,
    z.zva_zoning_letter_street,
    z.zva_zoning_letter_city,
    z.zva_zoning_letter_zip,
    z.zva_property_street,
    z.zva_property_state_code,
    z.zva_property_zip,
    z.property_city
FROM forms f
JOIN zoning_verification_letter z ON f.form_id = z.form_id;

CREATE OR REPLACE VIEW vw_adjacent_property_owner_forms AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool
FROM forms f
JOIN adjacent_property_owner_forms ap ON f.form_id = ap.form_id;

CREATE OR REPLACE VIEW vw_major_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    mspa.PVA_parcel_number
FROM forms f
JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id;

CREATE OR REPLACE VIEW vw_minor_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    mispa.PVA_parcel_number
FROM forms f
JOIN minor_subdivision_plat_applications mispa ON f.form_id = mispa.form_id;

CREATE OR REPLACE VIEW vw_site_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool
FROM forms f
JOIN site_development_plan_applications s ON f.form_id = s.form_id;

CREATE OR REPLACE VIEW vw_general_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool
FROM forms f
JOIN general_development_plan_applications g ON f.form_id = g.form_id;

CREATE OR REPLACE VIEW vw_variance_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    v.PVA_parcel_number
FROM forms f
JOIN variance_applications v ON f.form_id = v.form_id;

CREATE OR REPLACE VIEW vw_zoning_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    zp.project_type,
    zp.PVA_parcel_number
FROM forms f
JOIN zoning_permit_applications zp ON f.form_id = zp.form_id;

CREATE OR REPLACE VIEW vw_zoning_map_amendment_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    zma.zoning_map_amendment_request
FROM forms f
JOIN zoning_map_amendment_applications zma ON f.form_id = zma.form_id;

CREATE OR REPLACE VIEW vw_open_record_requests AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    o.orr_received_on_datetime,
    o.orr_request_for_copies,
    o.orr_denied_reasons
FROM forms f
JOIN open_record_requests o ON f.form_id = o.form_id;

-- ============================================================
-- ✅ END OF VERIFIED P&Z DATABASE VIEWS FILE
-- ============================================================
