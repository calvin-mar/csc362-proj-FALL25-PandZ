-- Create views for govt_worker_view_form.php based on actual schema

-- 1. vw_form_summary: Basic form information with calculated fields
CREATE OR REPLACE VIEW vw_form_summary AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    f.correction_form_id,
    EXTRACT(DAY FROM (NOW() - f.form_datetime_submitted)) AS days_since_submission,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved'
        ELSE 'Pending'
    END AS form_status,
    CASE 
        WHEN f.form_paid_bool = true THEN 'Paid'
        ELSE 'Unpaid'
    END AS payment_status,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL 
        THEN EXTRACT(DAY FROM (f.form_datetime_resolved - f.form_datetime_submitted))
        ELSE NULL
    END AS days_to_resolve
FROM forms f;

-- 2. vw_form_clients: Links forms to clients (only actual fields from clients table)
CREATE OR REPLACE VIEW vw_form_clients AS
SELECT 
    cf.form_id,
    c.client_id,
    c.client_username,
    c.client_type
FROM client_forms cf
JOIN clients c ON cf.client_id = c.client_id;

-- 3. vw_administrative_appeal_details: Administrative appeal details
CREATE OR REPLACE VIEW vw_administrative_appeal_details AS
SELECT 
    aar.form_id,
    aar.aar_hearing_date,
    aar.aar_submit_date,
    aar.aar_official_decision,
    aar.aar_relevant_provisions,
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code
FROM administrative_appeal_requests aar
LEFT JOIN addresses a ON aar.address_id = a.address_id;

-- 4. vw_appeal_appellants: Appellants for administrative appeals
CREATE OR REPLACE VIEW vw_appeal_appellants AS
SELECT 
    aa.form_id,
    ap.aar_appellant_id,
    ap.aar_first_name,
    ap.aar_last_name
FROM administrative_appellants aa
JOIN aar_appellants ap ON aa.aar_appellant_id = ap.aar_appellant_id;

-- 5. vw_variance_application_details: Variance application details
CREATE OR REPLACE VIEW vw_variance_application_details AS
SELECT 
    va.form_id,
    va.va_variance_request,
    va.va_proposed_conditions,
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code
FROM variance_applications va
LEFT JOIN properties p ON va.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id;

-- 6. vw_zoning_verification_details: Zoning verification letter details
CREATE OR REPLACE VIEW vw_zoning_verification_details AS
SELECT 
    zvl.form_id,
    zvl.zva_letter_content,
    owner.zva_owner_first_name,
    owner.zva_owner_last_name,
    app.zva_applicant_first_name,
    app.zva_applicant_last_name,
    app.zva_applicant_phone_number,
    app.zva_applicant_fax_number,
    za.address_street AS zoning_address_street,
    za.address_city AS zoning_address_city,
    za.state_code AS zoning_state_code,
    za.address_zip_code AS zoning_zip_code,
    pa.address_street AS property_address_street,
    pa.address_city AS property_address_city,
    pa.state_code AS property_state_code,
    pa.address_zip_code AS property_zip_code
FROM zoning_verification_letter zvl
LEFT JOIN zva_property_owners owner ON zvl.zva_owner_id = owner.zva_owner_id
LEFT JOIN zva_applicants app ON zvl.zva_applicant_id = app.zva_applicant_id
LEFT JOIN addresses za ON zvl.zva_zoning_address_id = za.address_id
LEFT JOIN addresses pa ON zvl.zva_property_address_id = pa.address_id;

-- 7. vw_major_subdivision_details: Major subdivision plat application details
CREATE OR REPLACE VIEW vw_major_subdivision_details AS
SELECT 
    mspa.form_id,
    mspa.mspa_topographic_survey,
    mspa.mspa_proposed_plot_layout,
    mspa.mspa_plat_restrictions,
    mspa.mspa_property_owner_convenants,
    mspa.mspa_association_covenants,
    mspa.mspa_master_deed,
    mspa.mspa_construction_plans,
    mspa.mspa_traffic_impact_study,
    mspa.mspa_geologic_study,
    mspa.mspa_drainage_plan,
    mspa.mspa_pavement_design,
    mspa.mspa_SWPPP_EPSC_plan,
    mspa.mspa_construction_bond_est,
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    s.surveyor_email,
    s.surveyor_phone,
    s.surveyor_cell,
    e.engineer_first_name,
    e.engineer_last_name,
    e.engineer_firm,
    e.engineer_email,
    e.engineer_phone,
    e.engineer_cell,
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code
FROM major_subdivision_plat_applications mspa
LEFT JOIN surveyors s ON mspa.surveyor_id = s.surveyor_id
LEFT JOIN engineers e ON mspa.engineer_id = e.engineer_id
LEFT JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id;

-- 8. vw_zoning_permit_details: Zoning permit application details
CREATE OR REPLACE VIEW vw_zoning_permit_details AS
SELECT 
    zpa.form_id,
    zpa.project_type,
    zpa.zpa_project_plans,
    zpa.zpa_preliminary_site_evaluation,
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    arch.architect_first_name,
    arch.architect_last_name,
    arch.architect_law_firm,
    la.land_architect_first_name,
    la.land_architect_last_name,
    la.land_architect_law_firm,
    c.contractor_first_name,
    c.contractor_last_name,
    c.contractor_law_firm,
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code
FROM zoning_permit_applications zpa
LEFT JOIN surveyors s ON zpa.surveyor_id = s.surveyor_id
LEFT JOIN architects arch ON zpa.architect_id = arch.architect_id
LEFT JOIN land_architects la ON zpa.land_architect_id = la.land_architect_id
LEFT JOIN contractors c ON zpa.contractor_id = c.contractor_id
LEFT JOIN properties p ON zpa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id;

-- 9. vw_department_interactions: Department interactions (only actual fields)
CREATE OR REPLACE VIEW vw_department_interactions AS
SELECT 
    dfi.form_id,
    dfi.client_id,
    d.department_name,
    dfi.department_form_interaction_description
FROM department_form_interactions dfi
LEFT JOIN departments d ON dfi.client_id = d.client_id;

-- 10. vw_form_corrections: Corrections linked to forms (only actual fields)
CREATE OR REPLACE VIEW vw_form_corrections AS
SELECT 
    f.form_id,
    cb.correction_box_id,
    cb.correction_box_reviewer,
    cb.correction_box_text,
    f.correction_form_id
FROM forms f
JOIN correction_forms cf ON f.correction_form_id = cf.correction_form_id
JOIN correction_boxes cb ON cf.correction_form_id = cb.correction_form_id;

-- 11. v_form_metrics: Key performance indicators for forms
CREATE OR REPLACE VIEW v_form_metrics AS
SELECT
    -- Total forms ever submitted
    COUNT(*) AS total_forms_submitted,

    -- Total forms resolved (those with a non-null resolution date)
    SUM(CASE WHEN f.form_datetime_resolved IS NOT NULL THEN 1 ELSE 0 END) AS total_forms_resolved,

    -- Total forms flagged for correction (exist in vw_form_corrections)
    (
        SELECT COUNT(DISTINCT vc.form_id)
        FROM vw_form_corrections vc
    ) AS incomplete_client_forms
FROM vw_form_summary f;
