SET FOREIGN_KEY_CHECKS = 0;

/*
This file creates the schema for the Database
Please use my link to view the table viewing webpage http://34.29.71.136/html/testing.php
*/

CREATE TABLE form_types (
    form_type VARCHAR(255),
    PRIMARY KEY (form_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE correction_forms(
  correction_form_id INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY(correction_form_id)
);

CREATE TABLE states (
    state_code CHAR(2),
    PRIMARY KEY(state_code)
);

CREATE TABLE addresses(
  address_id INT NOT NULL AUTO_INCREMENT,
  address_street VARCHAR(255),
  address_city VARCHAR(255),
  state_code CHAR(2),
  address_zip_code VARCHAR(255),
  PRIMARY KEY (address_id),
  FOREIGN KEY (state_code) REFERENCES states(state_code) ON DELETE RESTRICT
);

CREATE TABLE correction_boxes(
  correction_box_id INT NOT NULL AUTO_INCREMENT,
  correction_box_reviewer VARCHAR(255),
  correction_box_text TEXT,
  correction_form_id INT NOT NULL,
  PRIMARY KEY(correction_box_id),
  FOREIGN KEY (correction_form_id) REFERENCES correction_forms(correction_form_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE forms (
    form_id INT NOT NULL AUTO_INCREMENT,
    form_type VARCHAR(255),
    form_datetime_submitted DATETIME NOT NULL,
    form_datetime_resolved DATETIME,
    form_paid_bool BOOLEAN,
    correction_form_id INT DEFAULT NULL,
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_type) REFERENCES form_types(form_type) ON DELETE RESTRICT,
    FOREIGN KEY (correction_form_id) REFERENCES correction_forms(correction_form_id) ON DELETE RESTRICT 
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE clients (
    client_id INT NOT NULL AUTO_INCREMENT,
    client_username VARCHAR(255) UNIQUE,
    client_password VARCHAR(255),
    client_email VARCHAR(255),
    client_type VARCHAR(255),
    PRIMARY KEY (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE surveyors (
    surveyor_id INT NOT NULL AUTO_INCREMENT,
    surveyor_first_name VARCHAR(255),
    surveyor_last_name VARCHAR(255),
    surveyor_firm VARCHAR(255),
    surveyor_email VARCHAR(255),
    surveyor_phone VARCHAR(50),
    surveyor_cell VARCHAR(255),
    PRIMARY KEY (surveyor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE surveyor_clients (
    surveyor_id INT,
    client_id INT,
    PRIMARY KEY(surveyor_id, client_id),
    FOREIGN KEY (surveyor_id) REFERENCES surveyors(surveyor_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE engineers (
    engineer_id INT NOT NULL AUTO_INCREMENT,
    engineer_first_name VARCHAR(255),
    engineer_last_name VARCHAR(255),
    engineer_firm VARCHAR(255),
    engineer_email VARCHAR(255),
    engineer_phone VARCHAR(50),
    engineer_cell VARCHAR(255),
    PRIMARY KEY (engineer_id)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE engineer_clients (
    engineer_id INT,
    client_id INT,
    PRIMARY KEY(engineer_id, client_id),
    FOREIGN KEY (engineer_id) REFERENCES engineers(engineer_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE contractors (
    contractor_id INT NOT NULL AUTO_INCREMENT,
    contractor_first_name VARCHAR(255),
    contractor_last_name VARCHAR(255),
    contractor_firm VARCHAR(255),
    contractor_email VARCHAR(255),
    contractor_phone VARCHAR(50),
    contractor_cell VARCHAR(255),
    PRIMARY KEY (contractor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contractor_clients (
    contractor_id INT,
    client_id INT,
    PRIMARY KEY(contractor_id, client_id),
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE architects (
    architect_id INT NOT NULL AUTO_INCREMENT,
    architect_first_name VARCHAR(255),
    architect_last_name VARCHAR(255),
    architect_firm VARCHAR(255),
    architect_email VARCHAR(255),
    architect_phone VARCHAR(50),
    architect_cell VARCHAR(255),
    PRIMARY KEY (architect_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE architect_clients (
    architect_id INT,
    client_id INT,
    PRIMARY KEY(architect_id, client_id),
    FOREIGN KEY (architect_id) REFERENCES architects(architect_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE land_architects (
    land_architect_id INT NOT NULL AUTO_INCREMENT,
    land_architect_first_name VARCHAR(255),
    land_architect_last_name VARCHAR(255),
    land_architect_firm VARCHAR(255),
    land_architect_email VARCHAR(255),
    land_architect_phone VARCHAR(50),
    land_architect_cell VARCHAR(255),
    PRIMARY KEY (land_architect_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE land_architect_clients (
    land_architect_id INT,
    client_id INT,
    PRIMARY KEY(land_architect_id, client_id),
    FOREIGN KEY (land_architect_id) REFERENCES land_architects(land_architect_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE attorneys (
    attorney_id INT NOT NULL AUTO_INCREMENT,
    attorney_first_name VARCHAR( 255),
    attorney_last_name VARCHAR(255),
    attorney_law_firm VARCHAR(255),
    attorney_email VARCHAR(255),
    attorney_phone VARCHAR(50),
    attorney_cell VARCHAR(255),
    PRIMARY KEY (attorney_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attorney_clients (
    attorney_id INT,
    client_id INT,
    PRIMARY KEY(attorney_id, client_id),
    FOREIGN KEY (attorney_id) REFERENCES attorneys(attorney_id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
);

CREATE TABLE properties (
    PVA_parcel_number INT NOT NULL,
    address_id INT,
    property_acreage VARCHAR(255),
    property_current_zoning VARCHAR(255),
    PRIMARY KEY (PVA_parcel_number),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MISSING TABLES FOR FOREIGN KEY REFERENCES HAVE BEEN ADDED/MOVED HERE

CREATE TABLE zva_property_owners (
    zva_owner_id INT NOT NULL AUTO_INCREMENT,
    zva_owner_first_name VARCHAR(255),
    zva_owner_last_name VARCHAR(255),
    address_id INT,
    PRIMARY KEY (zva_owner_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zva_applicants (
    zva_applicant_id INT NOT NULL AUTO_INCREMENT,
    zva_applicant_first_name VARCHAR(255),
    zva_applicant_last_name VARCHAR(255),
    address_id INT,
    zva_applicant_phone_number VARCHAR(50),
    zva_applicant_fax_number VARCHAR(255),
    PRIMARY KEY (zva_applicant_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zoning_verification_letter (
    form_id INT NOT NULL,
    zva_owner_id INT NOT NULL,
    zva_applicant_id INT NOT NULL,
    zva_letter_content TEXT,
    zva_zoning_address_id INT,
    zva_property_address_id INT,
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (zva_zoning_address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT,
    FOREIGN KEY (zva_property_address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT,
    FOREIGN KEY (zva_owner_id) REFERENCES zva_property_owners(zva_owner_id) ON DELETE RESTRICT,
    FOREIGN KEY (zva_applicant_id) REFERENCES zva_applicants(zva_applicant_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE apof_neighbors (
    neighbor_id INT NOT NULL AUTO_INCREMENT,
    PVA_map_code VARCHAR(255),
    apof_neighbor_property_location VARCHAR(255),
    apof_neighbor_property_deed_book VARCHAR(255),
    apof_property_street_pg_number VARCHAR(255),
    PRIMARY KEY (neighbor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE adjacent_neighbors (
    form_id INT NOT NULL,
    neighbor_id INT NOT NULL,
    PRIMARY KEY (form_id, neighbor_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (neighbor_id) REFERENCES apof_neighbors(neighbor_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE adjacent_property_owner_forms (
    form_id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE type_one_applicants (
    t1_applicant_id INT NOT NULL AUTO_INCREMENT,
    t1_applicant_first_name VARCHAR(255),
    t1_applicant_last_name VARCHAR(255),
    address_id INT,
    t1_applicant_phone_number VARCHAR(50),
    t1_applicant_cell_phone VARCHAR(50),
    t1_applicant_email VARCHAR(255),
    PRIMARY KEY (t1_applicant_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE applicants_link_forms (
    t1_applicant_id INT NOT NULL,
    form_id INT NOT NULL,
    PRIMARY KEY (t1_applicant_id, form_id),
    FOREIGN KEY (t1_applicant_id) REFERENCES type_one_applicants(t1_applicant_id) ON DELETE RESTRICT,
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE major_subdivision_plat_applications (
    form_id INT NOT NULL,
    surveyor_id INT,
    engineer_id INT,
    PVA_parcel_number INT,
    mspa_topographic_survey VARCHAR(255),
    mspa_proposed_plot_layout VARCHAR(255),
    mspa_plat_restrictions VARCHAR(255),
    mspa_property_owner_convenants VARCHAR(255),
    mspa_association_covenants VARCHAR(255),
    mspa_master_deed VARCHAR(255),
    mspa_construction_plans VARCHAR(255),
    mspa_traffic_impact_study VARCHAR(255),
    mspa_geologic_study VARCHAR(255),
    mspa_drainage_plan VARCHAR(255),
    mspa_pavement_design VARCHAR(255),
    mspa_SWPPP_EPSC_plan VARCHAR(255),
    mspa_construction_bond_est VARCHAR(255),
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (surveyor_id) REFERENCES surveyors(surveyor_id) ON DELETE RESTRICT,
    FOREIGN KEY (engineer_id) REFERENCES engineers(engineer_id) ON DELETE RESTRICT,
    FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE minor_subdivision_plat_applications (
    form_id INT NOT NULL,
    surveyor_id INT,
    engineer_id INT,
    PVA_parcel_number INT,
    minspa_topographic_survey VARCHAR(255),
    minspa_proposed_plot_layout VARCHAR(255),
    minspa_plat_restrictions VARCHAR(255),
    minspa_property_owner_covenants VARCHAR(255),
    minspa_association_covenants VARCHAR(255),
    minspa_master_deed VARCHAR(255),
    PRIMARY KEY (
    form_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (surveyor_id) REFERENCES surveyors(surveyor_id) ON DELETE RESTRICT,
  FOREIGN KEY (engineer_id) REFERENCES engineers(engineer_id) ON DELETE RESTRICT,
  FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE technical_forms (
    form_id INT NOT NULL,
    technical_app_filing_date DATE,
    technical_review_date DATE,
    technical_prelim_approval_date DATE,
    technical_final_approval_date DATE,
    PRIMARY KEY (form_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE LDS_plans (
    LDS_plan_id INT NOT NULL AUTO_INCREMENT,
    form_id INT NOT NULL,
    LDS_plan_file VARCHAR(255),
    PRIMARY KEY (LDS_plan_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE structures (
    structure_id INT NOT NULL AUTO_INCREMENT,
    form_id INT NOT NULL,
    structure_type VARCHAR(255),
    structure_square_feet DECIMAL(12,2),
    structure_project_value VARCHAR(255),
    structure_notes TEXT,
    PRIMARY KEY (structure_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE WSF_verifications (
    WSF_verification_id INT NOT NULL AUTO_INCREMENT,
    form_id INT NOT NULL,
    WSF_verification_file VARCHAR(255),
    PRIMARY KEY (WSF_verification_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE project_types (
    project_type VARCHAR(255),
    PRIMARY KEY (project_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MISSING TABLES FOR FOREIGN KEY REFERENCES HAVE BEEN ADDED/MOVED HERE

CREATE TABLE sp_property_owners (
    sp_owner_id INT NOT NULL AUTO_INCREMENT,
    sp_owner_first_name VARCHAR(255),
    sp_owner_last_name VARCHAR(255),
    address_id INT,
    PRIMARY KEY (sp_owner_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sp_contractors (
    sp_contractor_id INT NOT NULL AUTO_INCREMENT,
    sp_contractor_first_name VARCHAR(255),
    sp_contractor_last_name VARCHAR(255),
    sp_contractor_phone_number VARCHAR(50),
    PRIMARY KEY (sp_contractor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sp_applicants (
    sp_applicant_id INT NOT NULL AUTO_INCREMENT,
    sp_applicant_name VARCHAR(255),
    address_id INT,
    PRIMARY KEY ( sp_applicant_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sp_businesses (
    sp_business_id INT NOT NULL AUTO_INCREMENT,
    sp_business_name VARCHAR(255),
    address_id INT,
    PRIMARY KEY ( sp_business_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE zoning_permit_applications (
    form_id INT NOT NULL,
    surveyor_id INT,
    architect_id INT,
    land_architect_id INT,
    contractor_id INT,
    PVA_parcel_number INT,
    project_type VARCHAR(255),
    zpa_project_plans VARCHAR(255),
    zpa_preliminary_site_evaluation VARCHAR(255),
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (surveyor_id) REFERENCES surveyors(surveyor_id) ON DELETE RESTRICT,
    FOREIGN KEY (architect_id) REFERENCES architects(architect_id) ON DELETE RESTRICT,
    FOREIGN KEY (land_architect_id) REFERENCES land_architects(land_architect_id) ON DELETE RESTRICT,
    FOREIGN KEY (contractor_id) REFERENCES contractors(contractor_id) ON DELETE RESTRICT,
    FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT,
    FOREIGN KEY (project_type) REFERENCES project_types(project_type) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zoning_map_amendment_applications (
    form_id INT NOT NULL,
    zoning_map_amendment_request VARCHAR(255),
    zmaa_proposed_conditions VARCHAR(255),
    PVA_parcel_number INT NOT NULL,
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gdpa_required_findings (
    required_findings_type VARCHAR(255),
    required_findings_description TEXT,
    PRIMARY KEY (required_findings_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE general_development_plan_applications (
    form_id INT NOT NULL,
    address_id INT,
    gdpa_applicant_phone VARCHAR(50),
    gdpa_plan_amendment_request VARCHAR(255),
    gdpa_proposed_conditions VARCHAR(255),
    required_findings_type VARCHAR(255),
    gdpa_concept_plan VARCHAR(255),
    gdpa_traffic_study VARCHAR(255),
    gdpa_geologic_analysis VARCHAR(255),
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (required_findings_type) REFERENCES gdpa_required_findings(required_findings_type) ON DELETE RESTRICT,
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE variance_applications (
    form_id INT NOT NULL,
    va_variance_request VARCHAR(255),
    va_proposed_conditions VARCHAR(255),
    PVA_parcel_number INT,
    PRIMARY KEY (form_id),
    FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT,
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE flum_required_findings (
    required_findings_type VARCHAR(255),
    required_findings_description TEXT,
    PRIMARY KEY (required_findings_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE future_land_use_map_applications (
    form_id INT NOT NULL,
    future_land_use_map_amendment_prop VARCHAR(255),
    required_findings_type VARCHAR(255),
    findings_explanation TEXT,
    PVA_parcel_number INT NOT NULL,
    PRIMARY KEY (form_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT,
  FOREIGN KEY (required_findings_type) REFERENCES flum_required_findings(required_findings_type) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE conditional_use_permit_applications (
    form_id INT NOT NULL,
    cupa_permit_request VARCHAR(
    255
  ),
    cupa_proposed_conditions VARCHAR(
    255
  ),
    PRIMARY KEY (
    form_id
  ),
  PVA_parcel_number INT NOT NULL,
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,    
  FOREIGN KEY (PVA_parcel_number) REFERENCES properties(PVA_parcel_number) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE hearing_forms (
    form_id INT NOT NULL,
    hearing_docket_number VARCHAR(255),
    hearing_date_application_filed DATE,
    hearing_date DATE,
    hearing_preapp_meeting_date DATE,
    attorney_id INT,
    PRIMARY KEY (form_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (attorney_id) REFERENCES attorneys(attorney_id) ON DELETE RESTRICT 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE type_one_forms (
    form_id INT NOT NULL,
    PRIMARY KEY (
    form_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orr_applicants (
    orr_applicant_id INT NOT NULL AUTO_INCREMENT,
    orr_applicant_first_name VARCHAR(255),
    orr_applicant_last_name VARCHAR(255),
    orr_applicant_telephone VARCHAR(50),
    address_id INT,
    PRIMARY KEY (orr_applicant_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE public_records (
    public_record_id INT NOT NULL AUTO_INCREMENT,
    public_record_description TEXT,
    PRIMARY KEY (public_record_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orr_public_record_names (
    form_id INT NOT NULL,
    public_record_id INT NOT NULL,
    PRIMARY KEY (
    form_id, public_record_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (public_record_id) REFERENCES public_records(public_record_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE open_record_requests (
    form_id INT NOT NULL,
    orr_commercial_purpose VARCHAR(255),
    orr_request_for_copies VARCHAR(255),
    orr_received_on_datetime DATE,
    orr_receivable_datetime DATE,
    orr_denied_reasons TEXT,
    orr_applicant_id INT,
    PRIMARY KEY (form_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (orr_applicant_id) REFERENCES orr_applicants(orr_applicant_id) ON DELETE RESTRICT 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MOVED BEFORE ADMINISTRATIVE_PROPERTY_OWNERS
CREATE TABLE aar_property_owners (
    aar_property_owner_id INT NOT NULL AUTO_INCREMENT,
    aar_property_owner_first_name VARCHAR(255),
    aar_property_owner_last_name VARCHAR(255),
    PRIMARY KEY (aar_property_owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE administrative_property_owners (
    form_id INT NOT NULL,
    aar_property_owner_id INT NOT NULL,
    PRIMARY KEY (form_id, aar_property_owner_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (aar_property_owner_id) REFERENCES aar_property_owners(aar_property_owner_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- MOVED BEFORE ADMINISTRATIVE_APPELLANTS
CREATE TABLE aar_appellants (
    aar_appellant_id INT NOT NULL AUTO_INCREMENT,
    aar_first_name VARCHAR(255),
    aar_last_name VARCHAR(255),
    PRIMARY KEY (aar_appellant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE administrative_appellants (
    form_id INT NOT NULL,
    aar_appellant_id INT,
    PRIMARY KEY (form_id, aar_appellant_id),
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
    FOREIGN KEY (aar_appellant_id) REFERENCES aar_appellants(aar_appellant_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE administrative_appeal_requests (
    form_id INT NOT NULL,
    aar_hearing_date DATE,
    aar_submit_date DATE,
    address_id INT,
    aar_official_decision VARCHAR (255),
    aar_relevant_provisions VARCHAR(255),
    PRIMARY KEY (form_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT,
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sign_permit_applications (
    form_id INT NOT NULL,
    owner_id INT,
    sp_contractor_id INT,
    business_id INT,
    sp_applicant_id INT,
    sp_date DATE,
    sp_permit_number VARCHAR(
    255
  ),
    sp_building_coverage_percent VARCHAR(
    255
  ),
    sp_permit_fee VARCHAR(
    255
  ),
    PRIMARY KEY (
    form_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (owner_id) REFERENCES sp_property_owners(sp_owner_id) ON DELETE RESTRICT,
  FOREIGN KEY (sp_applicant_id) REFERENCES sp_applicants(sp_applicant_id) ON DELETE RESTRICT,
  FOREIGN KEY (sp_contractor_id) REFERENCES sp_contractors(sp_contractor_id) ON DELETE RESTRICT,
  FOREIGN KEY (business_id) REFERENCES sp_businesses(sp_business_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE departments (
    client_id INT NOT NULL PRIMARY KEY,
    department_name VARCHAR(255),
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE department_form_interactions (
    client_id INT NOT NULL,
    form_id INT NOT NULL,
    interaction_status ENUM('ongoing', 'resolved') NOT NULL DEFAULT 'ongoing',
    interaction_started DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    interaction_resolved DATETIME NULL,
    department_form_interaction_description TEXT,
    PRIMARY KEY (client_id, form_id, interaction_started),
    FOREIGN KEY (client_id) REFERENCES departments(client_id) ON DELETE RESTRICT,
    FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS govt_workers (
    client_id INT,
    govt_worker_role VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (client_id),
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE incomplete_client_forms (
    form_id INT NOT NULL,
    client_id INT NOT NULL,
    PRIMARY KEY (
    form_id, client_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT ,
  FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE client_forms (
    form_id INT NOT NULL,
    client_id INT NOT NULL,
    PRIMARY KEY (
    form_id, client_id
  ),
  FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT ,
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE site_development_plan_applications (
    form_id INT NOT NULL,
    surveyor_id INT,
    land_architect_id INT,
    engineer_id INT,
    architect_id INT,
    site_plan_request VARCHAR(255),
    PRIMARY KEY (form_id),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (land_architect_id) REFERENCES land_architects(land_architect_id) ON DELETE RESTRICT,
  FOREIGN KEY (engineer_id) REFERENCES engineers(engineer_id) ON DELETE RESTRICT,
  FOREIGN KEY (architect_id) REFERENCES architects(architect_id) ON DELETE RESTRICT,
  FOREIGN KEY (surveyor_id) REFERENCES surveyors(surveyor_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE type_one_execs (
    t1e_exec_id INT NOT NULL AUTO_INCREMENT,
    t1e_exec_first_name VARCHAR(255),
    t1e_exec_last_name VARCHAR(255),
    PRIMARY KEY (t1e_exec_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE type_one_applicant_execs (
    t1e_exec_id INT NOT NULL,
    t1_applicant_id INT NOT NULL,
    PRIMARY KEY (
    t1e_exec_id, t1_applicant_id
  ),
  FOREIGN KEY (t1e_exec_id) REFERENCES type_one_execs(t1e_exec_id) ON DELETE RESTRICT,
  FOREIGN KEY (t1_applicant_id) REFERENCES type_one_applicants(t1_applicant_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE type_one_owners (
    t1_owner_id INT NOT NULL AUTO_INCREMENT,
    t1o_owner_first_name VARCHAR(255),
    t1o_owner_last_name VARCHAR(255),
    PRIMARY KEY (t1_owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE applicant_owner_link (
    form_id INT NOT NULL,
    t1_applicant_id INT NOT NULL,
    t1_owner_id INT NOT NULL,
    PRIMARY KEY(form_id, t1_applicant_id, t1_owner_id),
    FOREIGN KEY(form_id) REFERENCES forms(form_id),
    FOREIGN KEY(t1_applicant_id) REFERENCES type_one_applicants(t1_applicant_id),
    FOREIGN KEY(t1_owner_id) REFERENCES type_one_owners(t1_owner_id)
);

CREATE TABLE adjacent_property_owners (
    adjacent_property_owner_id INT NOT NULL AUTO_INCREMENT,
    address_id INT,
    adjacent_property_owner_first_name VARCHAR(255),
    adjacent_property_owner_last_name VARCHAR(255),
    PRIMARY KEY (adjacent_property_owner_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE RESTRICT
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE adjacent_neighbor_owners (
    form_id INT NOT NULL,
    adjacent_property_owner_id INT NOT NULL,
    PRIMARY KEY (
    form_id, adjacent_property_owner_id
  ),
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT,
  FOREIGN KEY (adjacent_property_owner_id) REFERENCES adjacent_property_owners(adjacent_property_owner_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE signs (
    sign_id INT NOT NULL AUTO_INCREMENT,
    sp_owner_id INT,
    sign_type VARCHAR(255),
    sign_square_footage DECIMAL(12,2),
    lettering_height VARCHAR(255),
    PRIMARY KEY (sign_id),
    FOREIGN KEY (sp_owner_id) REFERENCES sp_property_owners(sp_owner_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permits_link_signs (
    form_id INT NOT NULL,
    sign_id INT NOT NULL,
    PRIMARY KEY (
    form_id, sign_id),
  FOREIGN KEY (sign_id) REFERENCES signs(sign_id) ON DELETE RESTRICT,
  FOREIGN KEY (form_id) REFERENCES forms(form_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INSERT INTO VALIDATION TABLES
INSERT INTO project_types (project_type) VALUES
    ("Multi-Family Use"),
    ("Commercial Use"),
    ("Industrial Use"),
    ("Temporary Use"),
    ("Parking/ Display"),
    ("Use Change");

INSERT INTO gdpa_required_findings (required_findings_type, required_findings_description) VALUES
    ("significant_change","There have been major changes of an economic, physical or social nature within the area
of the property in question which were not anticipated at the time of the adoption of the
development plan be amended."),
    ("physical_development","There have developed physical conditions which would not permit development of property in
question in accordance with the General Development Plan which is being amended."),
    ("petition_movement","Any application to amend the General Development Plan shall require the signature of 100 percent of
the property owners within the area covered by the General Development Plan and shall be amended
by the same process as the original zoning amendment.");

INSERT INTO flum_required_findings (required_findings_type, required_findings_description) VALUES
    ("public_benefit","A demonstrated over-riding public benefit of the proposed development (this may include the provision
of a major public facility or amenity, the provision of a major source of employment or an economic
development asset that cannot be accommodated in a location consistent with the current FLUM);"),
    ("inconsistency_correction","The request is a correction of inconsistencies or mapping errors contained within the FLUM;"),
    ("clear_compatability","That the proposed use is clearly compatible with existing surrounding development as demonstrated by
the applicant. This review should include a compatibility assessment of the proposed use, which includes,
but is not limited to, location and bulk of buildings and other structures, building height, building
materials, intensity of use, density of development, location of parking and signage within the
surrounding area. In addition, the applicant must prove that the proposed amendment will not result in
development that exceeds the capacity of existing infrastructure (such as roads, water, sewer and
stormwater).");

INSERT INTO form_types (form_type) VALUES
    ("Administrative Appeal Request"),
    ("Adjacent Property Owners Form"),
    ("Conditional Use Permit Application"),
    ("Development Plan Application (General)"),
    ("Development Plan Application (Site)"),
    ("Future Land Use Map (FLUM) Application"),
    ("Open Records Request"),
    ("Sign Permit Application"),
    ("Major Subdivision Plat Application"),
    ("Minor Subdivision Plat Application"),
    ("Variance Application"),
    ("Zoning Map Amendment Application"),
    ("Zoning Permit Application"),
    ("Zoning Verification Application");

INSERT INTO states (state_code) VALUES
('AL'),
('AK'),
('AZ'),
('AR'),
('CA'),
('CO'),
('CT'),
('DE'),
('FL'),
('GA'),
('HI'),
('ID'),
('IL'),
('IN'),
('IA'),
('KS'),
('KY'),
('LA'),
('ME'),
('MD'),
('MA'),
('MI'),
('MN'),
('MS'),
('MO'),
('MT'),
('NE'),
('NV'),
('NH'),
('NJ'),
('NM'),
('NY'),
('NC'),
('ND'),
('OH'),
('OK'),
('OR'),
('PA'),
('RI'),
('SC'),
('SD'),
('TN'),
('TX'),
('UT'),
('VT'),
('VA'),
('WA'),
('WV'),
('WI'),
('WY'),
('DC');

SET FOREIGN_KEY_CHECKS = 1;
