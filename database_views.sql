-- ========================================
-- COMPREHENSIVE DATABASE VIEWS
-- Aligned with stored procedures
-- ========================================

-- 1. Basic Form Summary View
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

-- 2. Administrative Appeal Request - Complete View
CREATE OR REPLACE VIEW vw_administrative_appeal_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Appeal details
    aar.aar_hearing_date,
    aar.aar_submit_date,
    aar.aar_official_decision,
    aar.aar_relevant_provisions,
    -- Address information
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code,
    -- Appellants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(ap.aar_first_name, ' ', ap.aar_last_name) SEPARATOR ', ') AS appellants,
    -- Property owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(po.aar_property_owner_first_name, ' ', po.aar_property_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN administrative_appeal_requests aar ON f.form_id = aar.form_id
LEFT JOIN addresses a ON aar.address_id = a.address_id
LEFT JOIN administrative_appellants aa ON f.form_id = aa.form_id
LEFT JOIN aar_appellants ap ON aa.aar_appellant_id = ap.aar_appellant_id
LEFT JOIN administrative_property_owners apo ON f.form_id = apo.form_id
LEFT JOIN aar_property_owners po ON apo.aar_property_owner_id = po.aar_property_owner_id
WHERE f.form_type = 'Administrative Appeal Request'
GROUP BY f.form_id;
-- 3. Variance Application - Complete View
CREATE OR REPLACE VIEW vw_variance_application_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Variance details
    va.va_variance_request,
    va.va_proposed_conditions,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN variance_applications va ON f.form_id = va.form_id
LEFT JOIN properties p ON va.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Variance Application'
GROUP BY f.form_id;

-- 4. Zoning Verification Letter - Complete View
CREATE OR REPLACE VIEW vw_zoning_verification_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Letter content
    zvl.zva_letter_content,
    -- Property owner
    owner.zva_owner_first_name,
    owner.zva_owner_last_name,
    owner_addr.address_street AS owner_street,
    owner_addr.address_city AS owner_city,
    owner_addr.state_code AS owner_state,
    owner_addr.address_zip_code AS owner_zip,
    -- Applicant
    app.zva_applicant_first_name,
    app.zva_applicant_last_name,
    app.zva_applicant_phone_number,
    app.zva_applicant_fax_number,
    app_addr.address_street AS applicant_street,
    app_addr.address_city AS applicant_city,
    app_addr.state_code AS applicant_state,
    app_addr.address_zip_code AS applicant_zip,
    -- Zoning address
    za.address_street AS zoning_address_street,
    za.address_city AS zoning_address_city,
    za.state_code AS zoning_state_code,
    za.address_zip_code AS zoning_zip_code,
    -- Property address
    pa.address_street AS property_address_street,
    pa.address_city AS property_address_city,
    pa.state_code AS property_state_code,
    pa.address_zip_code AS property_zip_code
FROM forms f
LEFT JOIN zoning_verification_letter zvl ON f.form_id = zvl.form_id
LEFT JOIN zva_property_owners owner ON zvl.zva_owner_id = owner.zva_owner_id
LEFT JOIN addresses owner_addr ON owner.address_id = owner_addr.address_id
LEFT JOIN zva_applicants app ON zvl.zva_applicant_id = app.zva_applicant_id
LEFT JOIN addresses app_addr ON app.address_id = app_addr.address_id
LEFT JOIN addresses za ON zvl.zva_zoning_address_id = za.address_id
LEFT JOIN addresses pa ON zvl.zva_property_address_id = pa.address_id
WHERE f.form_type = 'Zoning Verification Application';

-- 5. Conditional Use Permit Application - Complete View
CREATE OR REPLACE VIEW vw_conditional_use_permit_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- CUP details
    cupa.cupa_permit_request,
    cupa.cupa_proposed_conditions,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN conditional_use_permit_applications cupa ON f.form_id = cupa.form_id
LEFT JOIN properties p ON cupa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Conditional Use Permit Application'
GROUP BY f.form_id, cupa.cupa_permit_request, cupa.cupa_proposed_conditions, p.PVA_parcel_number,
         p.property_acreage, p.property_current_zoning, a.address_street, a.address_city, 
         a.state_code, a.address_zip_code, hf.hearing_docket_number, hf.hearing_date,
         hf.hearing_date_application_filed, hf.hearing_preapp_meeting_date,
         att.attorney_first_name, att.attorney_last_name, att.attorney_law_firm,
         att.attorney_phone, att.attorney_email;

-- 6. Zoning Map Amendment Application - Complete View
CREATE OR REPLACE VIEW vw_zoning_map_amendment_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Amendment details
    zmaa.zoning_map_amendment_request,
    zmaa.zmaa_proposed_conditions,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN zoning_map_amendment_applications zmaa ON f.form_id = zmaa.form_id
LEFT JOIN properties p ON zmaa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Zoning Map Amendment Application'
GROUP BY f.form_id, zmaa.zoning_map_amendment_request, zmaa.zmaa_proposed_conditions, 
         p.PVA_parcel_number, p.property_acreage, p.property_current_zoning, 
         a.address_street, a.address_city, a.state_code, a.address_zip_code,
         hf.hearing_docket_number, hf.hearing_date, hf.hearing_date_application_filed,
         hf.hearing_preapp_meeting_date, att.attorney_first_name, att.attorney_last_name,
         att.attorney_law_firm, att.attorney_phone, att.attorney_email;

-- 7. Major Subdivision Plat Application - Complete View
CREATE OR REPLACE VIEW vw_major_subdivision_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Subdivision details
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
    -- Technical form dates
    tf.technical_app_filing_date,
    tf.technical_review_date,
    tf.technical_prelim_approval_date,
    tf.technical_final_approval_date,
    -- Surveyor
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    s.surveyor_email,
    s.surveyor_phone,
    s.surveyor_cell,
    -- Engineer
    e.engineer_first_name,
    e.engineer_last_name,
    e.engineer_firm,
    e.engineer_email,
    e.engineer_phone,
    e.engineer_cell,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN major_subdivision_plat_applications mspa ON f.form_id = mspa.form_id
LEFT JOIN technical_forms tf ON f.form_id = tf.form_id
LEFT JOIN surveyors s ON mspa.surveyor_id = s.surveyor_id
LEFT JOIN engineers e ON mspa.engineer_id = e.engineer_id
LEFT JOIN properties p ON mspa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Major Subdivision Plat Application'
GROUP BY f.form_id, mspa.mspa_topographic_survey, mspa.mspa_proposed_plot_layout,
         mspa.mspa_plat_restrictions, mspa.mspa_property_owner_convenants,
         mspa.mspa_association_covenants, mspa.mspa_master_deed,
         mspa.mspa_construction_plans, mspa.mspa_traffic_impact_study,
         mspa.mspa_geologic_study, mspa.mspa_drainage_plan, mspa.mspa_pavement_design,
         mspa.mspa_SWPPP_EPSC_plan, mspa.mspa_construction_bond_est,
         tf.technical_app_filing_date, tf.technical_review_date,
         tf.technical_prelim_approval_date, tf.technical_final_approval_date,
         s.surveyor_first_name, s.surveyor_last_name, s.surveyor_firm,
         s.surveyor_email, s.surveyor_phone, s.surveyor_cell,
         e.engineer_first_name, e.engineer_last_name, e.engineer_firm,
         e.engineer_email, e.engineer_phone, e.engineer_cell,
         p.PVA_parcel_number, p.property_acreage, p.property_current_zoning,
         a.address_street, a.address_city, a.state_code, a.address_zip_code;

-- 8. Minor Subdivision Plat Application - Complete View
CREATE OR REPLACE VIEW vw_minor_subdivision_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Subdivision details
    minspa.minspa_topographic_survey,
    minspa.minspa_proposed_plot_layout,
    minspa.minspa_plat_restrictions,
    minspa.minspa_property_owner_covenants,
    minspa.minspa_association_covenants,
    minspa.minspa_master_deed,
    -- Technical form dates
    tf.technical_app_filing_date,
    tf.technical_review_date,
    tf.technical_prelim_approval_date,
    tf.technical_final_approval_date,
    -- Surveyor
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    s.surveyor_email,
    s.surveyor_phone,
    s.surveyor_cell,
    -- Engineer
    e.engineer_first_name,
    e.engineer_last_name,
    e.engineer_firm,
    e.engineer_email,
    e.engineer_phone,
    e.engineer_cell,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN minor_subdivision_plat_applications minspa ON f.form_id = minspa.form_id
LEFT JOIN technical_forms tf ON f.form_id = tf.form_id
LEFT JOIN surveyors s ON minspa.surveyor_id = s.surveyor_id
LEFT JOIN engineers e ON minspa.engineer_id = e.engineer_id
LEFT JOIN properties p ON minspa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Minor Subdivision Plat Application'
GROUP BY f.form_id, minspa.minspa_topographic_survey, minspa.minspa_proposed_plot_layout,
         minspa.minspa_plat_restrictions, minspa.minspa_property_owner_covenants,
         minspa.minspa_association_covenants, minspa.minspa_master_deed,
         tf.technical_app_filing_date, tf.technical_review_date,
         tf.technical_prelim_approval_date, tf.technical_final_approval_date,
         s.surveyor_first_name, s.surveyor_last_name, s.surveyor_firm,
         s.surveyor_email, s.surveyor_phone, s.surveyor_cell,
         e.engineer_first_name, e.engineer_last_name, e.engineer_firm,
         e.engineer_email, e.engineer_phone, e.engineer_cell,
         p.PVA_parcel_number, p.property_acreage, p.property_current_zoning,
         a.address_street, a.address_city, a.state_code, a.address_zip_code;

-- 9. General Development Plan Application - Complete View
CREATE OR REPLACE VIEW vw_general_development_plan_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- GDP details
    gdpa.gdpa_plan_amendment_request,
    gdpa.gdpa_proposed_conditions,
    gdpa.required_findings_type,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN general_development_plan_applications gdpa ON f.form_id = gdpa.form_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Development Plan Application (General)'
GROUP BY f.form_id, gdpa.gdpa_plan_amendment_request, gdpa.gdpa_proposed_conditions,
         gdpa.required_findings_type, hf.hearing_docket_number, hf.hearing_date,
         hf.hearing_date_application_filed, hf.hearing_preapp_meeting_date,
         att.attorney_first_name, att.attorney_last_name, att.attorney_law_firm,
         att.attorney_phone, att.attorney_email;

-- 10. Site Development Plan Application - Complete View
CREATE OR REPLACE VIEW vw_site_development_plan_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- SDP details
    sdpa.site_plan_request,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Surveyor
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    s.surveyor_email,
    s.surveyor_phone,
    -- Engineer
    e.engineer_first_name,
    e.engineer_last_name,
    e.engineer_firm,
    e.engineer_email,
    e.engineer_phone,
    -- Architect
    arch.architect_first_name,
    arch.architect_last_name,
    arch.architect_law_firm AS architect_firm,
    arch.architect_email,
    arch.architect_phone,
    -- Land Architect
    la.land_architect_first_name,
    la.land_architect_last_name,
    la.land_architect_law_firm AS land_architect_firm,
    la.land_architect_email,
    la.land_architect_phone,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN site_development_plan_applications sdpa ON f.form_id = sdpa.form_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN surveyors s ON sdpa.surveyor_id = s.surveyor_id
LEFT JOIN engineers e ON sdpa.engineer_id = e.engineer_id
LEFT JOIN architects arch ON sdpa.architect_id = arch.architect_id
LEFT JOIN land_architects la ON sdpa.land_architect_id = la.land_architect_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Development Plan Application (Site)'
GROUP BY f.form_id, sdpa.site_plan_request, hf.hearing_docket_number, hf.hearing_date,
         hf.hearing_date_application_filed, hf.hearing_preapp_meeting_date,
         att.attorney_first_name, att.attorney_last_name, att.attorney_law_firm,
         att.attorney_phone, att.attorney_email, s.surveyor_first_name, s.surveyor_last_name,
         s.surveyor_firm, s.surveyor_email, s.surveyor_phone, e.engineer_first_name,
         e.engineer_last_name, e.engineer_firm, e.engineer_email, e.engineer_phone,
         arch.architect_first_name, arch.architect_last_name, arch.architect_law_firm,
         arch.architect_email, arch.architect_phone, la.land_architect_first_name,
         la.land_architect_last_name, la.land_architect_law_firm, la.land_architect_email,
         la.land_architect_phone;

-- 11. Future Land Use Map (FLUM) Application - Complete View
CREATE OR REPLACE VIEW vw_flum_application_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- FLUM details
    flum.future_land_use_map_amendment_prop,
    flum.required_findings_type,
    flum.findings_explanation,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Hearing information
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    -- Attorney information
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_phone,
    att.attorney_email,
    -- Applicants (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1a.t1_applicant_first_name, ' ', t1a.t1_applicant_last_name) SEPARATOR ', ') AS applicants,
    -- Owners (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(t1o.t1o_owner_first_name, ' ', t1o.t1o_owner_last_name) SEPARATOR ', ') AS property_owners
FROM forms f
LEFT JOIN future_land_use_map_applications flum ON f.form_id = flum.form_id
LEFT JOIN properties p ON flum.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN hearing_forms hf ON f.form_id = hf.form_id
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id
LEFT JOIN applicants_link_forms alf ON f.form_id = alf.form_id
LEFT JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN owners_link_forms olf ON f.form_id = olf.form_id
LEFT JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id
WHERE f.form_type = 'Future Land Use Map (FLUM) Application'
GROUP BY f.form_id, flum.future_land_use_map_amendment_prop, flum.required_findings_type,
         flum.findings_explanation, p.PVA_parcel_number, p.property_acreage,
         p.property_current_zoning, a.address_street, a.address_city, a.state_code,
         a.address_zip_code, hf.hearing_docket_number, hf.hearing_date,
         hf.hearing_date_application_filed, hf.hearing_preapp_meeting_date,
         att.attorney_first_name, att.attorney_last_name, att.attorney_law_firm,
         att.attorney_phone, att.attorney_email;

-- 12. Department Interactions View (CORRECTED - removed duplicate column)
CREATE OR REPLACE VIEW vw_department_interactions AS
SELECT 
    dfi.client_id,
    dfi.form_id,
    dfi.interaction_started,
    dfi.interaction_status,
    dfi.interaction_resolved,
    d.department_name,
    dfi.department_form_interaction_description
FROM department_form_interactions dfi
LEFT JOIN departments d ON dfi.client_id = d.client_id
ORDER BY dfi.interaction_started DESC;

-- 13. Adjacent Property Owners Form - Complete View (CORRECTED)
CREATE OR REPLACE VIEW vw_adjacent_property_owners_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Neighbor information
    n.PVA_map_code,
    n.apof_neighbor_property_location,
    n.apof_neighbor_property_deed_book,
    -- Adjacent property owner
    GROUP_CONCAT(apo.adjacent_property_owner_first_name) AS "Property Owner First Names",
    GROUP_CONCAT(apo.adjacent_property_owner_last_name) AS "Property Owner Last Names",
    GROUP_CONCAT(apo_addr.address_street) AS adjacent_property_owner_streets,
    GROUP_CONCAT(apo_addr.address_city) AS adjacent_property_owner_cities,
    GROUP_CONCAT(apo_addr.state_code) AS owner_state_codes,
    GROUP_CONCAT(apo_addr.address_zip_code) AS adjacent_property_owner_zips
FROM forms f
LEFT JOIN adjacent_property_owner_forms apof ON f.form_id = apof.form_id
LEFT JOIN adjacent_neighbors an ON f.form_id = an.form_id
LEFT JOIN apof_neighbors n ON an.neighbor_id = n.neighbor_id
LEFT JOIN adjacent_neighbor_owners ano ON f.form_id = ano.form_id
LEFT JOIN adjacent_property_owners apo ON ano.adjacent_property_owner_id = apo.adjacent_property_owner_id
LEFT JOIN addresses apo_addr ON apo.address_id = apo_addr.address_id
WHERE f.form_type = 'Adjacent Property Owners Form'
GROUP BY f.form_id;

-- 14. Open Records Request - Complete View (CORRECTED)
CREATE OR REPLACE VIEW vw_open_records_request_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Request details
    orr.orr_commercial_purpose,
    orr.orr_request_for_copies,
    orr.orr_received_on_datetime,
    orr.orr_receivable_datetime,
    orr.orr_denied_reasons,
    -- Applicant information
    app.orr_applicant_first_name,
    app.orr_applicant_last_name,
    app.orr_applicant_telephone,
    appadd.address_street AS orr_applicant_street,
    appadd.address_city AS orr_applicant_city,
    appadd.state_code AS applicant_state,
    appadd.address_zip_code AS orr_applicant_zip_code,
    -- Public records (concatenated)
    GROUP_CONCAT(DISTINCT pr.public_record_description SEPARATOR '; ') AS requested_records
FROM forms f
LEFT JOIN open_record_requests orr ON f.form_id = orr.form_id
LEFT JOIN orr_applicants app ON orr.orr_applicant_id = app.orr_applicant_id
LEFT JOIN addresses appadd ON app.address_id = appadd.address_id
LEFT JOIN orr_public_record_names oprn ON f.form_id = oprn.form_id
LEFT JOIN public_records pr ON oprn.public_record_id = pr.public_record_id
WHERE f.form_type = 'Open Records Request'
GROUP BY f.form_id, orr.orr_commercial_purpose, orr.orr_request_for_copies,
         orr.orr_received_on_datetime, orr.orr_receivable_datetime, orr.orr_denied_reasons,
         app.orr_applicant_first_name, app.orr_applicant_last_name, app.orr_applicant_telephone,
         appadd.address_street, appadd.address_city, appadd.state_code, appadd.address_zip_code;


-- 15. Sign Permit Application - Complete View (CORRECTED)
CREATE OR REPLACE VIEW vw_sign_permit_application_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Permit details
    spa.sp_date,
    spa.sp_permit_number,
    spa.sp_building_coverage_percent,
    spa.sp_permit_fee,
    -- Property owner
    owner.sp_owner_first_name,
    owner.sp_owner_last_name,
    ownadd.address_street AS sp_owner_street,
    ownadd.address_city AS sp_owner_city,
    ownadd.state_code AS owner_state,
    ownadd.address_zip_code AS sp_owner_zip_code,
    -- Business
    bus.sp_business_name,
    busadd.address_street AS sp_business_street,
    busadd.address_city AS sp_business_city,
    busadd.state_code AS business_state,
    busadd.address_zip_code AS sp_business_zip_code,
    -- Contractor
    con.sp_contractor_first_name,
    con.sp_contractor_last_name,
    con.sp_contractor_phone_number,
    -- Signs (concatenated)
    GROUP_CONCAT(DISTINCT CONCAT(s.sign_type, ' (', s.sign_square_footage, ' sq ft, ', s.lettering_height, ' height)') SEPARATOR '; ') AS signs
FROM forms f
LEFT JOIN sign_permit_applications spa ON f.form_id = spa.form_id
LEFT JOIN sp_property_owners owner ON spa.owner_id = owner.sp_owner_id
LEFT JOIN addresses ownadd ON owner.address_id = ownadd.address_id
LEFT JOIN sp_businesses bus ON spa.business_id = bus.sp_business_id
LEFT JOIN addresses busadd ON bus.address_id = busadd.address_id
LEFT JOIN sp_contractors con ON spa.sp_contractor_id = con.sp_contractor_id
LEFT JOIN permits_link_signs pls ON f.form_id = pls.form_id
LEFT JOIN signs s ON pls.sign_id = s.sign_id
WHERE f.form_type = 'Sign Permit Appplication'
GROUP BY f.form_id, spa.sp_date, spa.sp_permit_number, spa.sp_building_coverage_percent,
         spa.sp_permit_fee, owner.sp_owner_first_name, owner.sp_owner_last_name,
         ownadd.address_street, ownadd.address_city, ownadd.state_code, ownadd.address_zip_code,
         bus.sp_business_name, busadd.address_street, busadd.address_city, busadd.state_code,
         busadd.address_zip_code, con.sp_contractor_first_name, con.sp_contractor_last_name,
         con.sp_contractor_phone_number;

-- 16. Zoning Permit Application - Complete View
CREATE OR REPLACE VIEW vw_zoning_permit_application_complete AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    -- Applicants
    GROUP_CONCAT(toa.t1_applicant_first_name) AS applicant_first_names,
    GROUP_CONCAT(toa.t1_applicant_last_name) AS applicant_last_names,
    -- Permit details
    zpa.project_type,
    zpa.zpa_project_plans,
    zpa.zpa_preliminary_site_evaluation,
    -- Property information
    p.PVA_parcel_number,
    p.property_acreage,
    p.property_current_zoning,
    a.address_street AS property_street,
    a.address_city AS property_city,
    a.state_code AS property_state,
    a.address_zip_code AS property_zip,
    -- Surveyor
    s.surveyor_first_name,
    s.surveyor_last_name,
    s.surveyor_firm,
    s.surveyor_email,
    s.surveyor_phone,
    s.surveyor_cell,
    -- Architect
    arch.architect_first_name,
    arch.architect_last_name,
    arch.architect_law_firm AS architect_firm,
    arch.architect_email,
    arch.architect_phone,
    arch.architect_cell,
    -- Land Architect
    la.land_architect_first_name,
    la.land_architect_last_name,
    la.land_architect_law_firm AS land_architect_firm,
    la.land_architect_email,
    la.land_architect_phone,
    la.land_architect_cell,
    -- Contractor
    c.contractor_first_name,
    c.contractor_last_name,
    c.contractor_law_firm AS contractor_firm,
    c.contractor_email,
    c.contractor_phone,
    c.contractor_cell
FROM forms f
LEFT JOIN zoning_permit_applications zpa ON f.form_id = zpa.form_id
LEFT JOIN properties p ON zpa.PVA_parcel_number = p.PVA_parcel_number
LEFT JOIN addresses a ON p.address_id = a.address_id
LEFT JOIN surveyors s ON zpa.surveyor_id = s.surveyor_id
LEFT JOIN architects arch ON zpa.architect_id = arch.architect_id
LEFT JOIN land_architects la ON zpa.land_architect_id = la.land_architect_id
LEFT JOIN contractors c ON zpa.contractor_id = c.contractor_id
LEFT JOIN applicants_link_forms alf ON alf.form_id = f.form_id
LEFT JOIN type_one_applicants toa ON toa.t1_applicant_id = alf.t1_applicant_id
WHERE f.form_type = 'Zoning Permit Application';

-- 17. Master View - All Forms Summary with Client Information
CREATE OR REPLACE VIEW vw_all_forms_summary AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    f.correction_form_id,
    -- Client information
    cf.client_id,
    c.client_username,
    c.client_type,
    -- Calculated fields
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
    END AS days_to_resolve,
    -- Count of department interactions
    (SELECT COUNT(*) FROM department_form_interactions dfi WHERE dfi.form_id = f.form_id) AS interaction_count
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- 18. Helper View - All Applicants by Form
CREATE OR REPLACE VIEW vw_form_applicants AS
SELECT 
    alf.form_id,
    t1a.t1_applicant_id,
    t1a.t1_applicant_first_name,
    t1a.t1_applicant_last_name,
    t1a.t1_applicant_phone_number,
    t1a.t1_applicant_cell_phone,
    t1a.t1_applicant_email,
    a.address_street,
    a.address_city,
    a.state_code,
    a.address_zip_code,
    -- Executives/Officers for this applicant
    GROUP_CONCAT(DISTINCT CONCAT(exec.t1e_exec_first_name, ' ', exec.t1e_exec_last_name) SEPARATOR ', ') AS officers
FROM applicants_link_forms alf
JOIN type_one_applicants t1a ON alf.t1_applicant_id = t1a.t1_applicant_id
LEFT JOIN addresses a ON t1a.address_id = a.address_id
LEFT JOIN type_one_applicant_execs t1ae ON t1a.t1_applicant_id = t1ae.t1_applicant_id
LEFT JOIN type_one_execs exec ON t1ae.t1e_exec_id = exec.t1e_exec_id
GROUP BY alf.form_id, t1a.t1_applicant_id, t1a.t1_applicant_first_name, t1a.t1_applicant_last_name,
         t1a.t1_applicant_phone_number, t1a.t1_applicant_cell_phone, t1a.t1_applicant_email,
         a.address_street, a.address_city, a.state_code, a.address_zip_code;

-- 19. Helper View - All Owners by Form
CREATE OR REPLACE VIEW vw_form_owners AS
SELECT 
    olf.form_id,
    t1o.t1_owner_id,
    t1o.t1o_owner_first_name,
    t1o.t1o_owner_last_name
FROM owners_link_forms olf
JOIN type_one_owners t1o ON olf.t1_owner_id = t1o.t1_owner_id;

-- 20. Helper View - Hearing Forms with Attorney Details
CREATE OR REPLACE VIEW vw_hearing_forms_detail AS
SELECT 
    hf.form_id,
    hf.hearing_docket_number,
    hf.hearing_date,
    hf.hearing_date_application_filed,
    hf.hearing_preapp_meeting_date,
    att.attorney_id,
    att.attorney_first_name,
    att.attorney_last_name,
    att.attorney_law_firm,
    att.attorney_email,
    att.attorney_phone,
    att.attorney_cell
FROM hearing_forms hf
LEFT JOIN attorneys att ON hf.attorney_id = att.attorney_id;

-- 21. Helper View - Technical Forms Detail
CREATE OR REPLACE VIEW vw_technical_forms_detail AS
SELECT 
    tf.form_id,
    tf.technical_app_filing_date,
    tf.technical_review_date,
    tf.technical_prelim_approval_date,
    tf.technical_final_approval_date,
    CASE 
        WHEN tf.technical_final_approval_date IS NOT NULL THEN 'Final Approved'
        WHEN tf.technical_prelim_approval_date IS NOT NULL THEN 'Preliminarily Approved'
        WHEN tf.technical_review_date IS NOT NULL THEN 'Under Review'
        WHEN tf.technical_app_filing_date IS NOT NULL THEN 'Filed'
        ELSE 'Not Started'
    END AS technical_status
FROM technical_forms tf;

-- 22. Correction Forms View
CREATE OR REPLACE VIEW vw_correction_forms_detail AS
SELECT 
    f.form_id,
    f.correction_form_id,
    cb.correction_box_id,
    cb.correction_box_reviewer,
    cb.correction_box_text
FROM forms f
JOIN correction_forms cf ON f.correction_form_id = cf.correction_form_id
JOIN correction_boxes cb ON cf.correction_form_id = cb.correction_form_id
WHERE f.correction_form_id IS NOT NULL;

-- 23. Enhanced Form Metrics View (for KPIs and Reports)
CREATE OR REPLACE VIEW v_form_metrics AS
SELECT
    -- Total forms ever submitted
    COUNT(*) AS total_forms_submitted,

    -- Total forms resolved (those with a non-null resolution date)
    SUM(CASE WHEN form_datetime_resolved IS NOT NULL THEN 1 ELSE 0 END) AS total_forms_resolved,

    -- Total forms flagged for correction (have a correction_form_id)
    SUM(CASE WHEN correction_form_id IS NOT NULL THEN 1 ELSE 0 END) AS incomplete_client_forms,
    
    -- Additional useful metrics
    SUM(CASE WHEN form_paid_bool = 1 THEN 1 ELSE 0 END) AS total_forms_paid,
    SUM(CASE WHEN form_paid_bool = 0 OR form_paid_bool IS NULL THEN 1 ELSE 0 END) AS total_forms_unpaid,
    SUM(CASE WHEN form_datetime_resolved IS NULL THEN 1 ELSE 0 END) AS total_forms_pending
FROM forms;

-- 24. Enhanced Corrections View (for Reports)
CREATE OR REPLACE VIEW vw_form_corrections AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    f.correction_form_id,
    cb.correction_box_id,
    cb.correction_box_reviewer,
    cb.correction_box_text,
    -- Additional helpful fields
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved'
        ELSE 'Pending Correction'
    END AS correction_status,
    EXTRACT(DAY FROM (NOW() - f.form_datetime_submitted)) AS days_since_submission
FROM forms f
JOIN correction_forms cf ON f.correction_form_id = cf.correction_form_id
JOIN correction_boxes cb ON cf.correction_form_id = cb.correction_form_id
WHERE f.correction_form_id IS NOT NULL;

-- 25. Forms Summary with Client Info (Enhanced for Reports)
CREATE OR REPLACE VIEW vw_form_summary_with_client AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    f.form_paid_bool,
    f.correction_form_id,
    -- Client information
    cf.client_id,
    c.client_username,
    c.client_type,
    -- Calculated fields
    EXTRACT(DAY FROM (NOW() - f.form_datetime_submitted)) AS days_since_submission,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved'
        WHEN f.correction_form_id IS NOT NULL THEN 'Needs Correction'
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
    END AS days_to_resolve,
    -- Has corrections flag
    CASE WHEN f.correction_form_id IS NOT NULL THEN 1 ELSE 0 END AS has_corrections
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id;

-- 26. Department Workload Summary (CORRECTED)
CREATE OR REPLACE VIEW vw_department_workload_summary AS
SELECT 
    d.client_id,
    d.department_name,
    COUNT(DISTINCT dfi.form_id) AS total_forms_interacted_with,
    COUNT(dfi.client_id) AS total_interactions,
    COUNT(DISTINCT CASE 
        WHEN f.form_datetime_resolved IS NULL 
        THEN dfi.form_id 
    END) AS pending_forms_with_interactions,
    COUNT(DISTINCT CASE 
        WHEN f.form_datetime_resolved IS NOT NULL 
        THEN dfi.form_id 
    END) AS resolved_forms_with_interactions
FROM departments d
LEFT JOIN department_form_interactions dfi ON d.client_id = dfi.client_id
LEFT JOIN forms f ON dfi.form_id = f.form_id
GROUP BY d.client_id, d.department_name;

-- 27. Form Type Usage Summary (for Reports)
CREATE OR REPLACE VIEW vw_form_type_usage_summary AS
SELECT 
    form_type AS form_type_name,
    COUNT(*) AS total_submissions,
    SUM(CASE WHEN form_datetime_resolved IS NOT NULL THEN 1 ELSE 0 END) AS total_resolved,
    SUM(CASE WHEN form_datetime_resolved IS NULL THEN 1 ELSE 0 END) AS total_pending,
    SUM(CASE WHEN form_paid_bool = 1 THEN 1 ELSE 0 END) AS total_paid,
    SUM(CASE WHEN form_paid_bool = 0 OR form_paid_bool IS NULL THEN 1 ELSE 0 END) AS total_unpaid,
    SUM(CASE WHEN correction_form_id IS NOT NULL THEN 1 ELSE 0 END) AS total_needing_correction,
    ROUND(AVG(CASE 
        WHEN form_datetime_resolved IS NOT NULL 
        THEN EXTRACT(DAY FROM (form_datetime_resolved - form_datetime_submitted))
        ELSE NULL 
    END), 1) AS avg_resolution_days
FROM forms
GROUP BY form_type
ORDER BY total_submissions DESC;

-- 28. Unpaid Forms Detail (for Reports)
CREATE OR REPLACE VIEW vw_unpaid_forms_detail AS
SELECT 
    f.form_id,
    f.form_type AS form_type_name,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    EXTRACT(DAY FROM (NOW() - f.form_datetime_submitted)) AS days_since_submission,
    cf.client_id,
    c.client_username,
    c.client_type,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved (Unpaid)'
        WHEN f.correction_form_id IS NOT NULL THEN 'Needs Correction (Unpaid)'
        ELSE 'Pending (Unpaid)'
    END AS status
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
WHERE f.form_paid_bool = 0 OR f.form_paid_bool IS NULL
ORDER BY f.form_datetime_submitted DESC;

-- 29. Resolution Time Statistics (for SLA Reports)
CREATE OR REPLACE VIEW vw_resolution_time_stats AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    EXTRACT(DAY FROM (f.form_datetime_resolved - f.form_datetime_submitted)) AS resolution_days,
    EXTRACT(HOUR FROM (f.form_datetime_resolved - f.form_datetime_submitted)) AS resolution_hours,
    CASE 
        WHEN EXTRACT(DAY FROM (f.form_datetime_resolved - f.form_datetime_submitted)) <= 7 THEN 'Fast (0-7 days)'
        WHEN EXTRACT(DAY FROM (f.form_datetime_resolved - f.form_datetime_submitted)) <= 30 THEN 'Medium (8-30 days)'
        WHEN EXTRACT(DAY FROM (f.form_datetime_resolved - f.form_datetime_submitted)) <= 90 THEN 'Slow (31-90 days)'
        ELSE 'Very Slow (90+ days)'
    END AS resolution_speed_category
FROM forms f
WHERE f.form_datetime_resolved IS NOT NULL;

-- 30. Recent Activity Summary (for Dashboard)
CREATE OR REPLACE VIEW vw_recent_activity_summary AS
SELECT 
    'form_submitted' AS activity_type,
    f.form_id,
    f.form_type,
    f.form_datetime_submitted AS activity_datetime,
    cf.client_id,
    c.client_username,
    NULL AS department_name,
    NULL AS description
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
WHERE f.form_datetime_submitted >= (NOW() - INTERVAL 30 DAY)

UNION ALL

SELECT 
    'form_resolved' AS activity_type,
    f.form_id,
    f.form_type,
    f.form_datetime_resolved AS activity_datetime,
    cf.client_id,
    c.client_username,
    NULL AS department_name,
    NULL AS description
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
WHERE f.form_datetime_resolved >= (NOW() - INTERVAL 30 DAY)
  AND f.form_datetime_resolved IS NOT NULL

UNION ALL

SELECT 
    'department_interaction' AS activity_type,
    dfi.form_id,
    f.form_type,
    NOW() AS activity_datetime, -- interactions don't have timestamp, using NOW as placeholder
    dfi.client_id,
    c.client_username,
    d.department_name,
    LEFT(dfi.department_form_interaction_description, 100) AS description
FROM department_form_interactions dfi
LEFT JOIN departments d ON dfi.client_id = d.client_id
LEFT JOIN forms f ON dfi.form_id = f.form_id
LEFT JOIN clients c ON dfi.client_id = c.client_id

ORDER BY activity_datetime DESC
LIMIT 100;

-- ========================================
-- DEPARTMENT-SPECIFIC VIEWS
-- ========================================

-- 1. Department Activity Summary (for KPIs)
CREATE OR REPLACE VIEW vw_department_activity_summary AS
SELECT 
    dfi.client_id as department_id,
    d.department_name,
    COUNT(*) as total_interactions,
    COUNT(DISTINCT dfi.form_id) as forms_interacted,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NULL THEN dfi.form_id END) as pending_forms,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NOT NULL THEN dfi.form_id END) as resolved_forms
FROM department_form_interactions dfi
LEFT JOIN departments d ON dfi.client_id = d.client_id
LEFT JOIN forms f ON dfi.form_id = f.form_id
GROUP BY dfi.client_id, d.department_name;

-- 2. Department Recent Interactions
CREATE OR REPLACE VIEW vw_department_recent_interactions AS
SELECT 
    dfi.client_id as department_id,
    dfi.form_id,
    dfi.interaction_started,
    dfi.interaction_status,
    dfi.interaction_resolved,
    dfi.department_form_interaction_description,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    d.department_name,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved'
        ELSE 'Pending'
    END as form_status
FROM department_form_interactions dfi
JOIN forms f ON dfi.form_id = f.form_id
LEFT JOIN departments d ON dfi.client_id = d.client_id
ORDER BY dfi.interaction_started DESC;

-- 3. Department Form Type Breakdown
CREATE OR REPLACE VIEW vw_department_form_type_breakdown AS
SELECT 
    dfi.client_id as department_id,
    d.department_name,
    f.form_type,
    COUNT(DISTINCT dfi.form_id) as forms_count,
    COUNT(*) as interaction_count,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NULL THEN dfi.form_id END) as pending_count,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NOT NULL THEN dfi.form_id END) as resolved_count,
    CASE 
        WHEN COUNT(DISTINCT dfi.form_id) > 0 
        THEN ROUND(COUNT(*) / COUNT(DISTINCT dfi.form_id), 1)
        ELSE 0 
    END as avg_interactions_per_form
FROM department_form_interactions dfi
JOIN forms f ON dfi.form_id = f.form_id
LEFT JOIN departments d ON dfi.client_id = d.client_id
GROUP BY dfi.client_id, d.department_name, f.form_type;

-- 4. All Pending Forms with Department Interaction Counts
CREATE OR REPLACE VIEW vw_pending_forms_with_dept_interactions AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    DATEDIFF(NOW(), f.form_datetime_submitted) as days_pending,
    GROUP_CONCAT(DISTINCT c.client_username SEPARATOR ', ') as clients,
    COUNT(DISTINCT CONCAT(dfi.client_id, '-', dfi.form_id, '-', dfi.interaction_started)) as total_interaction_count,
    f.form_paid_bool,
    f.correction_form_id
FROM forms f
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
LEFT JOIN department_form_interactions dfi ON f.form_id = dfi.form_id
WHERE f.form_datetime_resolved IS NULL
GROUP BY f.form_id, f.form_type, f.form_datetime_submitted, f.form_paid_bool, f.correction_form_id
ORDER BY days_pending DESC;

-- 5. Department Monthly Activity Trend
CREATE OR REPLACE VIEW vw_department_monthly_activity AS
SELECT 
    dfi.client_id as department_id,
    d.department_name,
    DATE_FORMAT(f.form_datetime_submitted, '%Y-%m') as month,
    COUNT(DISTINCT dfi.form_id) as forms_interacted,
    COUNT(*) as total_interactions
FROM department_form_interactions dfi
JOIN forms f ON dfi.form_id = f.form_id
LEFT JOIN departments d ON dfi.client_id = d.client_id
WHERE f.form_datetime_submitted >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY dfi.client_id, d.department_name, month
ORDER BY month ASC;

-- 6. Department Comparison (All Departments)
CREATE OR REPLACE VIEW vw_department_comparison AS
SELECT 
    d.client_id as department_id,
    d.department_name,
    COUNT(DISTINCT dfi.form_id) as forms_handled,
    COUNT(*) as total_interactions,
    CASE 
        WHEN COUNT(DISTINCT dfi.form_id) > 0 
        THEN ROUND(COUNT(*) / COUNT(DISTINCT dfi.form_id), 1)
        ELSE 0 
    END as avg_interactions_per_form
FROM departments d
LEFT JOIN department_form_interactions dfi ON d.client_id = dfi.client_id
GROUP BY d.client_id, d.department_name
ORDER BY forms_handled DESC;

-- 7. Department Forms Needing Attention
CREATE OR REPLACE VIEW vw_forms_needing_dept_attention AS
SELECT 
    f.form_id,
    f.form_type,
    f.form_datetime_submitted,
    f.form_datetime_resolved,
    DATEDIFF(NOW(), f.form_datetime_submitted) as days_since_submission,
    COUNT(*) as total_interactions,
    GROUP_CONCAT(DISTINCT d.department_name SEPARATOR ', ') as departments_involved,
    GROUP_CONCAT(DISTINCT c.client_username SEPARATOR ', ') as clients,
    CASE 
        WHEN f.form_datetime_resolved IS NOT NULL THEN 'Resolved'
        WHEN f.correction_form_id IS NOT NULL THEN 'Needs Correction'
        ELSE 'Pending'
    END as form_status
FROM forms f
LEFT JOIN department_form_interactions dfi ON f.form_id = dfi.form_id
LEFT JOIN departments d ON dfi.client_id = d.client_id
LEFT JOIN client_forms cf ON f.form_id = cf.form_id
LEFT JOIN clients c ON cf.client_id = c.client_id
GROUP BY f.form_id, f.form_type, f.form_datetime_submitted, f.form_datetime_resolved, 
         f.correction_form_id
ORDER BY f.form_datetime_submitted DESC;

-- 8. Department Performance Metrics (Enhanced)
CREATE OR REPLACE VIEW vw_department_performance_metrics AS
SELECT 
    d.client_id as department_id,
    d.department_name,
    COUNT(DISTINCT dfi.form_id) as total_forms_handled,
    COUNT(*) as total_interactions,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NOT NULL THEN dfi.form_id END) as resolved_forms_handled,
    COUNT(DISTINCT CASE WHEN f.form_datetime_resolved IS NULL THEN dfi.form_id END) as pending_forms_handled,
    CASE 
        WHEN COUNT(DISTINCT dfi.form_id) > 0 
        THEN ROUND(COUNT(*) / COUNT(DISTINCT dfi.form_id), 1)
        ELSE 0 
    END as avg_interactions_per_form,
    AVG(
        CASE 
            WHEN f.form_datetime_resolved IS NOT NULL 
            THEN DATEDIFF(f.form_datetime_resolved, f.form_datetime_submitted)
            ELSE NULL 
        END
    ) as avg_resolution_days,
    COUNT(DISTINCT CASE 
        WHEN f.form_datetime_submitted >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
        THEN dfi.form_id 
    END) as forms_last_30_days,
    COUNT(DISTINCT CASE 
        WHEN f.form_datetime_submitted >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        THEN dfi.form_id 
    END) as forms_last_7_days
FROM departments d
LEFT JOIN department_form_interactions dfi ON d.client_id = dfi.client_id
LEFT JOIN forms f ON dfi.form_id = f.form_id
GROUP BY d.client_id, d.department_name;