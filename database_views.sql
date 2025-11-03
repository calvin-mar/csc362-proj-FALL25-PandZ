-- ===================================================================
-- P&Z DATABASE VIEWS (FINAL, COMPLETE, AND OPTIMIZED VERSION)
-- Description: All useful pre-built views for analysis, reporting,
--              and staff operations within the Planning & Zoning DB.
-- ===================================================================

-- ============================================================
-- 🔹 1. GENERAL OVERVIEW & CLIENT RELATIONSHIP VIEWS
-- ============================================================

-- View of all forms with their type name and submission/resolution dates.
CREATE OR REPLACE VIEW v_all_forms AS
SELECT 
    f.form_id,
    f.form_type,
    ft.form_type AS form_type_name,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool
FROM forms f
LEFT JOIN form_types ft ON f.form_type = ft.form_type;

-- Summary of all registered clients.
CREATE OR REPLACE VIEW v_clients_summary AS
SELECT 
    c.client_id,
    c.client_username
FROM clients c;

-- Links forms to the clients who submitted them.
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
-- 🔹 2. ZONING & DEVELOPMENT APPLICATION VIEWS
-- ============================================================

-- Summary of Zoning Permit Applications with linked property details.
CREATE OR REPLACE VIEW v_zoning_permits AS
SELECT 
    f.form_id,
    f.form_type,
    c.client_username,
    zpa.project_type,
    p.PVA_parcel_number,
    p.property_street_address,
    p.property_city,
    p.property_current_zoning
FROM zoning_permit_applications zpa
JOIN forms f ON f.form_id = zpa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON zpa.PVA_parcel_number = p.PVA_parcel_number;

-- Unified view of all Major and Minor Subdivision Applications with property details.
CREATE OR REPLACE VIEW v_all_subdivision_applications AS
SELECT 
    f.form_id,
    'Major Subdivision' AS subdivision_type,
    c.client_username,
    p.PVA_parcel_number,
    p.property_street_address,
    p.property_current_zoning
FROM major_subdivision_plat_applications mspa
JOIN forms f ON f.form_id = mspa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number

UNION ALL

SELECT 
    f.form_id,
    'Minor Subdivision' AS subdivision_type,
    c.client_username,
    p.PVA_parcel_number,
    p.property_street_address,
    p.property_current_zoning
FROM minor_subdivision_plat_applications mispa
JOIN forms f ON f.form_id = mispa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mispa.PVA_parcel_number = p.PVA_parcel_number;

-- Unified view of all Site and General Development Plan Applications.
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

-- Summary of Sign Permit Applications with linked business and property owner.
CREATE OR REPLACE VIEW v_sign_permits AS
SELECT 
    spa.form_id,
    f.form_datetime_submitted,
    c.client_username,
    spa.sp_permit_number,
    spa.sp_permit_fee,
    b.sp_business_name,
    CONCAT(spo.sp_owner_first_name, ' ', spo.sp_owner_last_name) AS property_owner_name
FROM sign_permit_applications spa
JOIN forms f ON spa.form_id = f.form_id
LEFT JOIN client_forms cf ON spa.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN sp_businesses b ON spa.sp_business_id = b.sp_business_id
LEFT JOIN sp_property_owners spo ON spa.sp_owner_id = spo.sp_owner_id;

-- View detailing the signs attached to a Sign Permit Application.
CREATE OR REPLACE VIEW v_sign_details AS
SELECT
    pls.form_id AS sign_permit_form_id,
    s.sign_id,
    s.sign_type,
    s.sign_square_footage,
    s.lettering_height
FROM signs s
JOIN permits_link_signs pls ON s.sign_id = pls.sign_id;

-- ============================================================
-- 🔹 3. ANALYTICAL / REPORTING VIEWS
-- ============================================================

-- General summary of all applications.
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

-- Summary of form submissions, resolutions, and incompletes.
CREATE OR REPLACE VIEW v_form_metrics AS
SELECT 
    COUNT(f.form_id) AS total_forms_submitted,
    SUM(CASE WHEN f.form_datetime_resolved IS NOT NULL THEN 1 ELSE 0 END) AS total_forms_resolved,
    SUM(CASE WHEN icf.form_id IS NOT NULL THEN 1 ELSE 0 END) AS incomplete_client_forms
FROM forms f
LEFT JOIN incomplete_client_forms icf ON f.form_id = icf.form_id;

-- Summary of the workload for each department based on form interactions.
CREATE OR REPLACE VIEW v_department_workload_summary AS
SELECT
    d.department_name,
    COUNT(dfi.form_id) AS total_forms_interacted_with
FROM departments d
JOIN department_form_interactions dfi ON d.department_id = dfi.department_id
GROUP BY d.department_name;

-- Forms submitted or resolved in the last 30 days.
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
-- 🔹 4. FORM-SPECIFIC VIEWS
-- ============================================================

-- **[NEW]** Enhanced Zoning Verification Letter view with full contact names.
CREATE OR REPLACE VIEW v_zoning_verification_details AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    c.client_username,
    CONCAT(zva.zva_applicant_first_name, ' ', zva.zva_applicant_last_name) AS applicant_name,
    CONCAT(zvo.zva_owner_first_name, ' ', zvo.zva_owner_last_name) AS property_owner_name,
    zvl.zva_property_street,
    zvl.zva_zoning_letter_city,
    zvl.zva_letter_content
FROM forms f
JOIN zoning_verification_letter zvl ON f.form_id = zvl.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN zva_applicants zva ON zvl.zva_applicant_id = zva.zva_applicant_id
LEFT JOIN zva_property_owners zvo ON zvl.zva_owner_id = zvo.zva_owner_id;

CREATE OR REPLACE VIEW v_adjacent_property_owner_forms AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username
FROM forms f
JOIN adjacent_property_owner_forms ap ON f.form_id = ap.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_major_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    mspa.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_minor_subdivision_plat_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    mispa.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN minor_subdivision_plat_applications mispa ON f.form_id = mispa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON mispa.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_site_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    s.site_plan_request
FROM forms f
JOIN site_development_plan_applications s ON f.form_id = s.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_general_development_plan_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username
FROM forms f
JOIN general_development_plan_applications g ON f.form_id = g.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_variance_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    v.va_variance_request,
    v.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN variance_applications v ON f.form_id = v.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON v.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_zoning_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    zp.project_type,
    zp.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN zoning_permit_applications zp ON f.form_id = zp.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON zp.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_zoning_map_amendment_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    zma.zoning_map_amendment_request,
    zma.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN zoning_map_amendment_applications zma ON f.form_id = zma.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON zma.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_conditional_use_permit_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    cupa.cupa_permit_request,
    cupa.cupa_proposed_conditions,
    cupa.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN conditional_use_permit_applications cupa ON f.form_id = cupa.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON cupa.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_future_land_use_map_applications AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    fluma.future_land_use_map_amendment_prop,
    fluma.PVA_parcel_number,
    p.property_street_address
FROM forms f
JOIN future_land_use_map_applications fluma ON f.form_id = fluma.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN properties p ON fluma.PVA_parcel_number = p.PVA_parcel_number;

CREATE OR REPLACE VIEW v_administrative_appeal_requests AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    c.client_username,
    aar.aar_hearing_date,
    aar.aar_submit_date,
    aar.aar_street_address,
    aar.aar_city_address,
    aar.state_code,
    aar.aar_zip_code,
    aar.aar_property_location,
    aar.aar_official_decision
FROM forms f
JOIN administrative_appeal_requests aar ON f.form_id = aar.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

CREATE OR REPLACE VIEW v_open_record_requests AS
SELECT 
    f.form_id,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    c.client_username,
    o.orr_received_on_datetime,
    o.orr_request_for_copies,
    o.orr_denied_reasons
FROM forms f
JOIN open_record_requests o ON f.form_id = o.form_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- ============================================================
-- 🔹 5. ADVANCED CROSS-REFERENCE & OPERATIONAL VIEWS
-- ============================================================

-- View: v_all_professionals
-- Purpose: Provides a single consolidated list of all third-party professionals (surveyors, engineers, etc.) involved in applications.
CREATE OR REPLACE VIEW v_all_professionals AS
SELECT surveyor_id AS professional_id, 'Surveyor' AS professional_type, surveyor_first_name AS first_name, surveyor_last_name AS last_name, surveyor_firm AS firm, surveyor_email AS email, surveyor_phone AS phone
FROM surveyors
UNION ALL
SELECT engineer_id, 'Engineer', engineer_first_name, engineer_last_name, engineer_firm, engineer_email, engineer_phone
FROM engineers
UNION ALL
SELECT contractor_id, 'Contractor', contractor_first_name, contractor_last_name, contractor_law_firm, contractor_email, contractor_phone
FROM contractors
UNION ALL
SELECT architect_id, 'Architect', architect_first_name, architect_last_name, architect_law_firm, architect_email, architect_phone
FROM architects
UNION ALL
SELECT land_architect_id, 'Land Architect', land_architect_first_name, land_architect_last_name, land_architect_law_firm, land_architect_email, land_architect_phone
FROM land_architects
UNION ALL
SELECT attorney_id, 'Attorney', attorney_first_name, attorney_last_name, attorney_law_firm, attorney_email, attorney_phone
FROM attorneys;

-- View: v_forms_needing_correction
-- Purpose: Lists all forms that have been marked for correction, detailing the reviewer and the reason.
CREATE OR REPLACE VIEW v_forms_needing_correction AS
SELECT
    f.form_id,
    ft.form_type AS form_type_name,
    f.form_datetime_submitted,
    cb.correction_box_reviewer,
    cb.correction_box_text AS correction_reason,
    cf.correction_form_id
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN correction_forms cf ON f.correction_form_id = cf.correction_form_id
JOIN correction_boxes cb ON cf.correction_form_id = cb.correction_form_id;

-- **[NEW]** View: v_type_one_form_contacts
-- Purpose: Consolidates applicants and owners associated with Type One Forms (often a generic application type).
CREATE OR REPLACE VIEW v_type_one_form_contacts AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    'Applicant' AS contact_role,
    CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) AS full_name,
    t1a.t1_applicant_email AS email,
    t1a.t1_applicant_phone_number AS phone
FROM forms f
JOIN applicants_link_forms alf ON f.form_id = alf.form_id
JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
UNION ALL
SELECT
    f.form_id,
    f.form_datetime_submitted,
    'Owner' AS contact_role,
    CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) AS full_name,
    NULL AS email, -- Email not in this owner table
    NULL AS phone -- Phone not in this owner table
FROM forms f
JOIN owners_link_forms olf ON f.form_id = olf.form_id
JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id;

-- View: v_administrative_appeals_details
-- Purpose: Detailed view of administrative appeal requests, linking to the specific appellants and property owners involved.
CREATE OR REPLACE VIEW v_administrative_appeals_details AS
SELECT
    f.form_id,
    f.form_datetime_submitted,
    aar.aar_hearing_date,
    CONCAT(aa.aar_first_name, ' ', aa.aar_last_name) AS appellant_name,
    apo.aar_property_owner_name AS involved_property_owner,
    aar.aar_property_location,
    aar.aar_official_decision,
    aar.aar_relevant_provisions
FROM forms f
JOIN administrative_appeal_requests aar ON f.form_id = aar.form_id
LEFT JOIN administrative_appellants aapp ON f.form_id = aapp.form_id
LEFT JOIN aar_appellants aa ON aapp.aar_appellant_id = aa.aar_appellant_id
LEFT JOIN administrative_property_owners adpo ON f.form_id = adpo.form_id
LEFT JOIN aar_property_owners apo ON adpo.aar_property_owner_id = apo.aar_property_owner_id;

-- View: v_forms_by_property_cross_reference
-- Purpose: A powerful view to find all relevant application forms associated with a specific PVA parcel number for historical and current tracking.
CREATE OR REPLACE VIEW v_forms_by_property_cross_reference AS
SELECT
    f.form_id,
    ft.form_type AS form_type_name,
    mspa.PVA_parcel_number,
    prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id
JOIN properties prop ON mspa.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, mispa.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN minor_subdivision_plat_applications mispa ON f.form_id = mispa.form_id
JOIN properties prop ON mispa.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, zpa.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN zoning_permit_applications zpa ON f.form_id = zpa.form_id
JOIN properties prop ON zpa.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, zma.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN zoning_map_amendment_applications zma ON f.form_id = zma.form_id
JOIN properties prop ON zma.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, va.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN variance_applications va ON f.form_id = va.form_id
JOIN properties prop ON va.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, fluma.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN future_land_use_map_applications fluma ON f.form_id = fluma.form_id
JOIN properties prop ON fluma.PVA_parcel_number = prop.PVA_parcel_number

UNION ALL
SELECT f.form_id, ft.form_type, cupa.PVA_parcel_number, prop.property_street_address
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN conditional_use_permit_applications cupa ON f.form_id = cupa.form_id
JOIN properties prop ON cupa.PVA_parcel_number = prop.PVA_parcel_number;

-- View: v_hearing_docket
-- Purpose: A consolidated view for staff to see all forms currently scheduled for a hearing, including key dates and attorney details.
CREATE OR REPLACE VIEW v_hearing_docket AS
SELECT
    f.form_id,
    ft.form_type AS form_type_name,
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_preapp_meeting_date,
    CONCAT(a.attorney_first_name, ' ', a.attorney_last_name) AS attorney_name,
    a.attorney_law_firm,
    a.attorney_phone
FROM forms f
JOIN form_types ft ON f.form_type = ft.form_type
JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys a ON hf.attorney_id = a.attorney_id
ORDER BY hf.hearing_date ASC;

-- ============================================================
-- ✅ END OF VERIFIED P&Z DATABASE VIEWS FILE
-- ============================================================