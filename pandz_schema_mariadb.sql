/* MariaDB schema generated from draw.io ERD - inferred types */

DROP TABLE IF EXISTS `zoning_verification_letter`;
CREATE TABLE `zoning_verification_letter` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `zva_letter_content` VARCHAR(255),
  `zva_zoning_letter_street` VARCHAR(255),
  `<div>zva_zoning_letter_state</div>` VARCHAR(255),
  `zva_zoning_letter_city` VARCHAR(255),
  `zva_zoning_letter_zip` VARCHAR(50),
  `zva_property_street` VARCHAR(255),
  `zva_property_state` VARCHAR(255),
  `zva_property_zip` VARCHAR(50),
  `property_city` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `adjacent_neighbors`;
CREATE TABLE `adjacent_neighbors` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `neighbor_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `neighbor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `adjacent_property_owner_forms`;
CREATE TABLE `adjacent_property_owner_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `applicants_link_forms`;
CREATE TABLE `applicants_link_forms` (
  `type_one_applicant_id` INT NOT NULL AUTO_INCREMENT,
  `form_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`type_one_applicant_id`, `form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `major_subdivision_plat_applications`;
CREATE TABLE `major_subdivision_plat_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `surveyor_id` INT,
  `engineer_id` INT,
  `PVA_parcel_number` VARCHAR(255),
  `mspa_topographic_survey` VARCHAR(255),
  `mspa_proposed_plot_layout` VARCHAR(255),
  `mspa_plat_restrictions` VARCHAR(255),
  `mspa_property_owner_convenants` VARCHAR(255),
  `mspa_association_covenants` VARCHAR(255),
  `mspa_master_deed` VARCHAR(255),
  `mspa_construction_plans` VARCHAR(255),
  `mspa_traffic_impact_study` VARCHAR(255),
  `mspa_geologic_study` VARCHAR(255),
  `mspa_drainage_plan` VARCHAR(255),
  `mspa_pavement_design` VARCHAR(255),
  `mspa_SWPPP_EPSC_plan` VARCHAR(255),
  `mspa_construction_bond_est` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `minor_subdivision_plat_applications`;
CREATE TABLE `minor_subdivision_plat_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `surveyor_id` INT,
  `engineer_id` INT,
  `PVA_parcel_number` VARCHAR(255),
  `minspa_topographic_survey` VARCHAR(255),
  `minspa_proposed_plot_layout` VARCHAR(255),
  `minspa_plat_restrictions` VARCHAR(255),
  `minspa_property_owner_covenants` VARCHAR(255),
  `minspa_association_covenants` VARCHAR(255),
  `minspa_master_deed` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `technical_forms`;
CREATE TABLE `technical_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `technical_app_filing_date` DATE,
  `technical_review_date` DATE,
  `technical_prelim_approval_date` DATE,
  `technical_final_approval_date` DATE,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `LDS_plans`;
CREATE TABLE `LDS_plans` (
  `LDS_plan_id` INT NOT NULL AUTO_INCREMENT,
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `LDS_plan_file` VARCHAR(255),
  PRIMARY KEY (`LDS_plan_id`, `form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `structures`;
CREATE TABLE `structures` (
  `structure_id` INT NOT NULL AUTO_INCREMENT,
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `structure_type` VARCHAR(255),
  `structure_square_feet` DECIMAL(12,2),
  `structure_project_value` VARCHAR(255),
  `structrure_notes` TEXT,
  PRIMARY KEY (`structure_id`, `form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `WSF_verifications`;
CREATE TABLE `WSF_verifications` (
  `WSF_verification_id` INT NOT NULL AUTO_INCREMENT,
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `WSF_verification_file` VARCHAR(255),
  PRIMARY KEY (`WSF_verification_id`, `form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `land_architects`;
CREATE TABLE `land_architects` (
  `land_architect_id` INT NOT NULL AUTO_INCREMENT,
  `land_architect_first_name` VARCHAR(255),
  `land_architect_last_name` VARCHAR(255),
  `land_architect_law_firm` VARCHAR(255),
  `land_architect_email` VARCHAR(255),
  `land_architect_phone` VARCHAR(50),
  `land_architect_cell` VARCHAR(255),
  PRIMARY KEY (`land_architect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `zoning_permit_applications`;
CREATE TABLE `zoning_permit_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `surveyor_id` INT,
  `architect_id` INT,
  `land_architect_id` INT,
  `contractor_id` INT,
  `PVA_parcel_number` VARCHAR(255),
  `project_type` VARCHAR(255),
  `zpa_project_plans` VARCHAR(255),
  `zpa_preliminary_site_evaluation` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `zoning_map_amendment_applications`;
CREATE TABLE `zoning_map_amendment_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `zoning_map_amendment_request` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `gdpa_required_findings`;
CREATE TABLE `gdpa_required_findings` (
  `required_findings_type` INT NOT NULL AUTO_INCREMENT,
  `required_findings_description` TEXT,
  PRIMARY KEY (`required_findings_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `general_development_plan_applications`;
CREATE TABLE `general_development_plan_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `gdpa_applicant_state` VARCHAR(255),
  `gdpa_applicant_zip` VARCHAR(50),
  `gdpa_applicant_phone` VARCHAR(50),
  `gdpa_plan_amendment_request` VARCHAR(255),
  `gdpa_proposed_conditions` VARCHAR(255),
  `required_finding_type` VARCHAR(255),
  `gdpa_concept_plan` VARCHAR(255),
  `gdpa_traffic_study` VARCHAR(255),
  `gdpa_geologic_analysis` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `attorneys`;
CREATE TABLE `attorneys` (
  `attorney_id` INT NOT NULL AUTO_INCREMENT,
  `attorney_first_name` VARCHAR(255),
  `attorney_last_name` VARCHAR(255),
  `attorney_law_firm` VARCHAR(255),
  `attorney_email` VARCHAR(255),
  `attorney_phone` VARCHAR(50),
  `attorney_cell` VARCHAR(255),
  PRIMARY KEY (`attorney_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `variance_applications`;
CREATE TABLE `variance_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `va_variance_request` VARCHAR(255),
  `va_proposed_conditions` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `future_land_use_map_applications`;
CREATE TABLE `future_land_use_map_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `future_land_use_map_amendment_prop` VARCHAR(255),
  `PVA_parcel_number` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `conditional_use_permit_applications`;
CREATE TABLE `conditional_use_permit_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `cupa_permit_request` VARCHAR(255),
  `cupa_proposed_conditions` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `hearing_forms`;
CREATE TABLE `hearing_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `hearing_docket_number` VARCHAR(255),
  `hearing_date_application_filed` DATE,
  `hearing_date` DATE,
  `hearing_preapp_meeting_date` DATE,
  `attorney_id` INT,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `type_one_forms`;
CREATE TABLE `type_one_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `orr_applicants`;
CREATE TABLE `orr_applicants` (
  `orr_applicant_id` INT NOT NULL AUTO_INCREMENT,
  `orr_applicant_first_name` VARCHAR(255),
  `orr_applicant_last_name` VARCHAR(255),
  `orr_applicant_telephone` VARCHAR(50),
  `orr_applicant_street` VARCHAR(255),
  `orr_applicant_city` VARCHAR(255),
  `orr_applicant_state` VARCHAR(255),
  `orr_applicant_zip_code` VARCHAR(50),
  PRIMARY KEY (`orr_applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `orr_public_record_names`;
CREATE TABLE `orr_public_record_names` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `public_record_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `public_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `open_record_requests`;
CREATE TABLE `open_record_requests` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `orr_commercial_purpose` VARCHAR(255),
  `orr_request_for_copies` VARCHAR(255),
  `orr_received_on_datetime` DATE,
  `orr_receievable_datetime` DATE,
  `orr_denied_reasons` TEXT,
  `orr_applicant_id` INT,
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `administrative_appellants`;
CREATE TABLE `administrative_appellants` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `aar_appellant_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `aar_appellant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `administrative_appeal_requests`;
CREATE TABLE `administrative_appeal_requests` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `aar_hearing_date` DATE,
  `aar_submit_date` DATE,
  `aar_street_address` VARCHAR(255),
  `aar_city_address` VARCHAR(255),
  `aar_state_address` VARCHAR(255),
  `aar_zip_code` VARCHAR(50),
  `aar_property_location` VARCHAR(255),
  `aar_official_decision` VARCHAR(255),
  `aar_relevant_provisions` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `form_types`;
CREATE TABLE `form_types` (
  `form_type` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sign_permit_applications`;
CREATE TABLE `sign_permit_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `sp_owner_id` INT,
  `contractor_id` INT,
  `sp_business_id` INT,
  `sp_date` DATE,
  `sp_permit_number` VARCHAR(255),
  `sp_building_coverage_percent` VARCHAR(255),
  `sp_permit_fee` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `forms`;
CREATE TABLE `forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `form_type` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `department_form_interactions`;
CREATE TABLE `department_form_interactions` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `department_form_interaction_description` TEXT,
  PRIMARY KEY (`department_id`, `form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `department_name` VARCHAR(255),
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `client_id` INT NOT NULL AUTO_INCREMENT,
  `Row 1` VARCHAR(255),
  `Row 2` VARCHAR(255),
  `Row 3` VARCHAR(255),
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `incomplete_client_forms`;
CREATE TABLE `incomplete_client_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `client_id` INT NOT NULL AUTO_INCREMENT,
  `Row 1` VARCHAR(255),
  `Row 2` VARCHAR(255),
  PRIMARY KEY (`form_id`, `client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `client_forms`;
CREATE TABLE `client_forms` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `client_id` INT NOT NULL AUTO_INCREMENT,
  `Row 1` VARCHAR(255),
  `Row 2` VARCHAR(255),
  PRIMARY KEY (`form_id`, `client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `site_development_plan_applications`;
CREATE TABLE `site_development_plan_applications` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `surveryor_id` INT,
  `land_architect_id` INT,
  `<div>engineer_id</div>` VARCHAR(255),
  `<div>architect_id</div>` VARCHAR(255),
  `site_plan_request` VARCHAR(255),
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `type_one_applicant_execs`;
CREATE TABLE `type_one_applicant_execs` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `t1_applicant_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`department_id`, `t1_applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `type_one_applicants`;
CREATE TABLE `type_one_applicants` (
  `t1_applicant_id` INT NOT NULL AUTO_INCREMENT,
  `t1_applicant_first_name` VARCHAR(255),
  `t1_applicant_last_name` VARCHAR(255),
  `t1_applicant_street_address` VARCHAR(255),
  `t1_applicant_city` VARCHAR(255),
  `t1_applicant_state` VARCHAR(255),
  `t1_applicant_zip_code` VARCHAR(50),
  `t1_applicant_phone_number` VARCHAR(50),
  `t1_applicant_cell_phone` VARCHAR(50),
  `t1_applicant_email` VARCHAR(255),
  PRIMARY KEY (`t1_applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `apof_neighbors`;
CREATE TABLE `apof_neighbors` (
  `neighbor_id` INT NOT NULL AUTO_INCREMENT,
  `PVA_map_code` VARCHAR(255),
  `apof_neighbor_property_location` VARCHAR(255),
  `<span style="color: rgb(0, 0, 0); font-family: Helvetica; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-align: left; text-indent: 0px; text-transform: none; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; white-space: normal; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial; float: none; display: inline !important;">apof_neighbor_property_street</span>` DECIMAL(12,2),
  `apof_neighbor_property_city` VARCHAR(255),
  `apof_neighbor_property_state` VARCHAR(255),
  `<span style="color: rgb(0, 0, 0); font-family: Helvetica; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-align: left; text-indent: 0px; text-transform: none; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; white-space: normal; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial; float: none; display: inline !important;">apof_neighbor_property_zip</span>` VARCHAR(50),
  `<span style="color: rgb(0, 0, 0); font-family: Helvetica; font-size: 12px; font-style: normal; font-variant-ligatures: normal; font-variant-caps: normal; font-weight: 400; letter-spacing: normal; orphans: 2; text-align: left; text-indent: 0px; text-transform: none; widows: 2; word-spacing: 0px; -webkit-text-stroke-width: 0px; white-space: normal; text-decoration-thickness: initial; text-decoration-style: initial; text-decoration-color: initial; float: none; display: inline !important;">apof_neighbor_property_deed_book</span>` DECIMAL(12,2),
  `apof_property_street_pg_number` VARCHAR(255),
  PRIMARY KEY (`neighbor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `aar_appellants`;
CREATE TABLE `aar_appellants` (
  `aar_appellant_id` INT NOT NULL AUTO_INCREMENT,
  `aar_first_name` VARCHAR(255),
  `aar_last_name` VARCHAR(255),
  PRIMARY KEY (`aar_appellant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `administrative_property_owners`;
CREATE TABLE `administrative_property_owners` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `aar_propert_owner_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `aar_propert_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `aar_property_owners`;
CREATE TABLE `aar_property_owners` (
  `aar_property_owner_id` INT NOT NULL AUTO_INCREMENT,
  `aar_property_owner_name` VARCHAR(255),
  PRIMARY KEY (`aar_property_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `type_one_owners`;
CREATE TABLE `type_one_owners` (
  `t1_applicant_id` INT NOT NULL AUTO_INCREMENT,
  `t1o_owner_first_name` VARCHAR(255),
  `t1o_owner_last_name` VARCHAR(255),
  PRIMARY KEY (`t1_applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `type_one_execs`;
CREATE TABLE `type_one_execs` (
  `department_id` INT NOT NULL AUTO_INCREMENT,
  `t1e_exec_first_name` VARCHAR(255),
  `t1e_last_name` VARCHAR(255),
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `surveyors`;
CREATE TABLE `surveyors` (
  `surveyor_id` INT NOT NULL AUTO_INCREMENT,
  `surveyor_first_name` VARCHAR(255),
  `surveyor_last_name` VARCHAR(255),
  `surveyor_firm` VARCHAR(255),
  `surveyor_email` VARCHAR(255),
  `surveyor_phone` VARCHAR(50),
  `surveyor_cell` VARCHAR(255),
  PRIMARY KEY (`surveyor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `contractors`;
CREATE TABLE `contractors` (
  `contractor_id` INT NOT NULL AUTO_INCREMENT,
  `contractor_first_name` VARCHAR(255),
  `contractor_last_name` VARCHAR(255),
  `contractor_law_firm` VARCHAR(255),
  `contractor_email` VARCHAR(255),
  `contractor_phone` VARCHAR(50),
  `contractor_cell` VARCHAR(255),
  PRIMARY KEY (`contractor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `architects`;
CREATE TABLE `architects` (
  `architect_id` INT NOT NULL AUTO_INCREMENT,
  `architect_first_name` VARCHAR(255),
  `architect_last_name` VARCHAR(255),
  `architect_law_firm` VARCHAR(255),
  `architect_email` VARCHAR(255),
  `architect_phone` VARCHAR(50),
  `architect_cell` VARCHAR(255),
  PRIMARY KEY (`architect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `PVA_parcel_number` INT NOT NULL AUTO_INCREMENT,
  `property_street_address` VARCHAR(255),
  `property_city` VARCHAR(255),
  `property_state` VARCHAR(255),
  `property_zip_code` VARCHAR(50),
  `property_acreage` VARCHAR(255),
  `property_current_zoning` VARCHAR(255),
  PRIMARY KEY (`PVA_parcel_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `project_types`;
CREATE TABLE `project_types` (
  `project_type` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`project_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `engineers`;
CREATE TABLE `engineers` (
  `engineer_id` INT NOT NULL AUTO_INCREMENT,
  `engineer_first_name` VARCHAR(255),
  `engineer_last_name` VARCHAR(255),
  `engineer_firm` VARCHAR(255),
  `engineer_email` VARCHAR(255),
  `engineer_phone` VARCHAR(50),
  `engineer_cell` VARCHAR(255),
  PRIMARY KEY (`engineer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `adjacent_neighbor_owners`;
CREATE TABLE `adjacent_neighbor_owners` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `adjacent_property_owner_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `adjacent_property_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `adjacent_property_owners`;
CREATE TABLE `adjacent_property_owners` (
  `adjacent_property_owner_id` INT NOT NULL AUTO_INCREMENT,
  `adjacent_property_owner_street` VARCHAR(255),
  `adjacent_property_owner_city` VARCHAR(255),
  `adjacent_property_owner_state` VARCHAR(255),
  `adjacent_property_owner_zip` VARCHAR(50),
  PRIMARY KEY (`adjacent_property_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `signs`;
CREATE TABLE `signs` (
  `PK` VARCHAR(255),
  `sp_owner_id` INT NOT NULL AUTO_INCREMENT,
  `sign_type` VARCHAR(255),
  `sign_square_footage` DECIMAL(12,2),
  `lettering_height` VARCHAR(255),
  PRIMARY KEY (`sp_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `public_records`;
CREATE TABLE `public_records` (
  `public_record_id` INT NOT NULL AUTO_INCREMENT,
  `public_reocrd_description` TEXT,
  PRIMARY KEY (`public_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `zva_property_owners`;
CREATE TABLE `zva_property_owners` (
  `zva_owner_id` INT NOT NULL AUTO_INCREMENT,
  `zva_owner_first_name` VARCHAR(255),
  `zva_owner_last_name` VARCHAR(255),
  `zva_owner_street` VARCHAR(255),
  `zva_owner_city` VARCHAR(255),
  `zva_owner_state` VARCHAR(255),
  `zva_owner_zip_code` VARCHAR(50),
  PRIMARY KEY (`zva_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `zva_applicants`;
CREATE TABLE `zva_applicants` (
  `zva_applicant_id` INT NOT NULL AUTO_INCREMENT,
  `zva_applicant_first_name` VARCHAR(255),
  `zva_applicant_last_name` VARCHAR(255),
  `zva_applicant_street` VARCHAR(255),
  `zva_applicant_city` VARCHAR(255),
  `zva_applicant_state` VARCHAR(255),
  `zva_applicant_zip_code` VARCHAR(50),
  `zva_applicant_phone_number` VARCHAR(50),
  `zva_applicant_fax_number` VARCHAR(255),
  PRIMARY KEY (`zva_applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sp_property_owners`;
CREATE TABLE `sp_property_owners` (
  `sp_owner_id` INT NOT NULL AUTO_INCREMENT,
  `sp_owner_first_name` VARCHAR(255),
  `sp_owner_last_name` VARCHAR(255),
  `sp_owner_street` VARCHAR(255),
  `sp_owner_city` VARCHAR(255),
  `sp_owner_state` VARCHAR(255),
  `sp_owner_zip_code` VARCHAR(50),
  PRIMARY KEY (`sp_owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sp_businesses`;
CREATE TABLE `sp_businesses` (
  `sp_business_id` INT NOT NULL AUTO_INCREMENT,
  `sp_business_name` VARCHAR(255),
  `sp_business_street` VARCHAR(255),
  `sp_business_city` VARCHAR(255),
  `sp_business_state` VARCHAR(255),
  `sp_business_zip_code` VARCHAR(50),
  PRIMARY KEY (`sp_business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `permits_link_signs`;
CREATE TABLE `permits_link_signs` (
  `form_id` INT NOT NULL AUTO_INCREMENT,
  `sign_id` INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`form_id`, `sign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sp_contractors`;
CREATE TABLE `sp_contractors` (
  `sp_contractor_id` INT NOT NULL AUTO_INCREMENT,
  `sp_contractor_first_name` VARCHAR(255),
  `sp_contractor_last_name` VARCHAR(255),
  `sp_contractor_phone_number` VARCHAR(50),
  PRIMARY KEY (`sp_contractor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_department_id_0` FOREIGN KEY (`department_id`) REFERENCES `department_form_interactions`(`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `incomplete_client_forms`
  ADD CONSTRAINT `fk_incomplete_client_forms_form_id_1` FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `incomplete_client_forms`
  ADD CONSTRAINT `fk_incomplete_client_forms_client_id_2` FOREIGN KEY (`client_id`) REFERENCES `clients`(`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `client_forms`
  ADD CONSTRAINT `fk_client_forms_client_id_3` FOREIGN KEY (`client_id`) REFERENCES `clients`(`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `client_forms`
  ADD CONSTRAINT `fk_client_forms_form_id_4` FOREIGN KEY (`form_id`) REFERENCES `forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_5` FOREIGN KEY (`form_id`) REFERENCES `type_one_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_forms`
  ADD CONSTRAINT `fk_type_one_forms_form_id_6` FOREIGN KEY (`form_id`) REFERENCES `hearing_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_forms`
  ADD CONSTRAINT `fk_type_one_forms_form_id_7` FOREIGN KEY (`form_id`) REFERENCES `technical_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_form_id_8` FOREIGN KEY (`form_id`) REFERENCES `variance_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_form_id_9` FOREIGN KEY (`form_id`) REFERENCES `general_development_plan_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_form_id_10` FOREIGN KEY (`form_id`) REFERENCES `future_land_use_map_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_form_id_11` FOREIGN KEY (`form_id`) REFERENCES `conditional_use_permit_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_form_id_12` FOREIGN KEY (`form_id`) REFERENCES `zoning_map_amendment_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `technical_forms`
  ADD CONSTRAINT `fk_technical_forms_form_id_13` FOREIGN KEY (`form_id`) REFERENCES `minor_subdivision_plat_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `technical_forms`
  ADD CONSTRAINT `fk_technical_forms_form_id_14` FOREIGN KEY (`form_id`) REFERENCES `major_subdivision_plat_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_forms`
  ADD CONSTRAINT `fk_type_one_forms_form_id_15` FOREIGN KEY (`form_id`) REFERENCES `applicants_link_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_applicants`
  ADD CONSTRAINT `fk_type_one_applicants_t1_applicant_id_16` FOREIGN KEY (`t1_applicant_id`) REFERENCES `applicants_link_forms`(`type_one_applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_17` FOREIGN KEY (`form_id`) REFERENCES `adjacent_property_owner_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_18` FOREIGN KEY (`form_id`) REFERENCES `administrative_appeal_requests`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_19` FOREIGN KEY (`form_id`) REFERENCES `open_record_requests`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_20` FOREIGN KEY (`form_id`) REFERENCES `sign_permit_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_id_21` FOREIGN KEY (`form_id`) REFERENCES `zoning_verification_letter`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `apof_neighbors`
  ADD CONSTRAINT `fk_apof_neighbors_neighbor_id_22` FOREIGN KEY (`neighbor_id`) REFERENCES `adjacent_neighbors`(`neighbor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `aar_appellants`
  ADD CONSTRAINT `fk_aar_appellants_aar_appellant_id_23` FOREIGN KEY (`aar_appellant_id`) REFERENCES `administrative_appellants`(`aar_appellant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `aar_property_owners`
  ADD CONSTRAINT `fk_aar_property_owners_aar_property_owner_id_24` FOREIGN KEY (`aar_property_owner_id`) REFERENCES `administrative_property_owners`(`aar_propert_owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `administrative_appeal_requests`
  ADD CONSTRAINT `fk_administrative_appeal_requests_aar_hearing_date_25` FOREIGN KEY (`aar_hearing_date`) REFERENCES `administrative_appellants`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_forms`
  ADD CONSTRAINT `fk_type_one_forms_form_id_26` FOREIGN KEY (`form_id`) REFERENCES `zoning_permit_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_owners`
  ADD CONSTRAINT `fk_type_one_owners_t1_applicant_id_27` FOREIGN KEY (`t1_applicant_id`) REFERENCES `applicants_link_forms`(`type_one_applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_applicants`
  ADD CONSTRAINT `fk_type_one_applicants_t1_applicant_id_28` FOREIGN KEY (`t1_applicant_id`) REFERENCES `type_one_applicant_execs`(`t1_applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_execs`
  ADD CONSTRAINT `fk_type_one_execs_department_id_29` FOREIGN KEY (`department_id`) REFERENCES `type_one_applicant_execs`(`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `type_one_forms`
  ADD CONSTRAINT `fk_type_one_forms_form_id_30` FOREIGN KEY (`form_id`) REFERENCES `applicants_link_forms`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hearing_forms`
  ADD CONSTRAINT `fk_hearing_forms_attorney_id_31` FOREIGN KEY (`attorney_id`) REFERENCES `attorneys`(`attorney_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_PVA_parcel_number_32` FOREIGN KEY (`PVA_parcel_number`) REFERENCES `zoning_permit_applications`(`PVA_parcel_number`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zoning_permit_applications`
  ADD CONSTRAINT `fk_zoning_permit_applications_land_architect_id_33` FOREIGN KEY (`land_architect_id`) REFERENCES `land_architects`(`land_architect_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contractors`
  ADD CONSTRAINT `fk_contractors_contractor_id_34` FOREIGN KEY (`contractor_id`) REFERENCES `zoning_permit_applications`(`contractor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `architects`
  ADD CONSTRAINT `fk_architects_architect_id_35` FOREIGN KEY (`architect_id`) REFERENCES `zoning_permit_applications`(`architect_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `surveyors`
  ADD CONSTRAINT `fk_surveyors_surveyor_id_36` FOREIGN KEY (`surveyor_id`) REFERENCES `zoning_permit_applications`(`surveyor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `project_types`
  ADD CONSTRAINT `fk_project_types_project_type_37` FOREIGN KEY (`project_type`) REFERENCES `zoning_permit_applications`(`project_type`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zoning_permit_applications`
  ADD CONSTRAINT `fk_zoning_permit_applications_form_id_38` FOREIGN KEY (`form_id`) REFERENCES `LDS_plans`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zoning_permit_applications`
  ADD CONSTRAINT `fk_zoning_permit_applications_form_id_39` FOREIGN KEY (`form_id`) REFERENCES `WSF_verifications`(`WSF_verification_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zoning_permit_applications`
  ADD CONSTRAINT `fk_zoning_permit_applications_form_id_40` FOREIGN KEY (`form_id`) REFERENCES `structures`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_PVA_parcel_number_41` FOREIGN KEY (`PVA_parcel_number`) REFERENCES `general_development_plan_applications`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `general_development_plan_applications`
  ADD CONSTRAINT `fk_general_development_plan_applications_required_finding_type_42` FOREIGN KEY (`required_finding_type`) REFERENCES `gdpa_required_findings`(`required_findings_type`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `general_development_plan_applications`
  ADD CONSTRAINT `fk_general_development_plan_applications_form_id_43` FOREIGN KEY (`form_id`) REFERENCES `WSF_verifications`(`WSF_verification_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `surveyors`
  ADD CONSTRAINT `fk_surveyors_surveyor_id_44` FOREIGN KEY (`surveyor_id`) REFERENCES `major_subdivision_plat_applications`(`surveyor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `engineers`
  ADD CONSTRAINT `fk_engineers_engineer_first_name_45` FOREIGN KEY (`engineer_first_name`) REFERENCES `major_subdivision_plat_applications`(`engineer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_PVA_parcel_number_46` FOREIGN KEY (`PVA_parcel_number`) REFERENCES `major_subdivision_plat_applications`(`PVA_parcel_number`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `surveyors`
  ADD CONSTRAINT `fk_surveyors_surveyor_id_47` FOREIGN KEY (`surveyor_id`) REFERENCES `minor_subdivision_plat_applications`(`surveyor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `forms`
  ADD CONSTRAINT `fk_forms_form_type_48` FOREIGN KEY (`form_type`) REFERENCES `form_types`(`form_type`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `adjacent_property_owner_forms`
  ADD CONSTRAINT `fk_adjacent_property_owner_forms_form_id_49` FOREIGN KEY (`form_id`) REFERENCES `adjacent_neighbors`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `adjacent_property_owners`
  ADD CONSTRAINT `fk_adjacent_property_owners_adjacent_property_owner_id_50` FOREIGN KEY (`adjacent_property_owner_id`) REFERENCES `adjacent_neighbor_owners`(`adjacent_property_owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `public_records`
  ADD CONSTRAINT `fk_public_records_public_record_id_51` FOREIGN KEY (`public_record_id`) REFERENCES `orr_public_record_names`(`public_record_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_record_requests`
  ADD CONSTRAINT `fk_open_record_requests_orr_commercial_purpose_52` FOREIGN KEY (`orr_commercial_purpose`) REFERENCES `orr_public_record_names`(`form_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_record_requests`
  ADD CONSTRAINT `fk_open_record_requests_orr_applicant_id_53` FOREIGN KEY (`orr_applicant_id`) REFERENCES `orr_applicants`(`orr_applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zva_property_owners`
  ADD CONSTRAINT `fk_zva_property_owners_zva_owner_id_54` FOREIGN KEY (`zva_owner_id`) REFERENCES `zoning_verification_letter`(`zva_letter_content`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `zva_applicants`
  ADD CONSTRAINT `fk_zva_applicants_zva_applicant_id_55` FOREIGN KEY (`zva_applicant_id`) REFERENCES `zoning_verification_letter`(`zva_letter_content`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `permits_link_signs`
  ADD CONSTRAINT `fk_permits_link_signs_sign_id_56` FOREIGN KEY (`sign_id`) REFERENCES `signs`(`PK`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sp_property_owners`
  ADD CONSTRAINT `fk_sp_property_owners_sp_owner_id_57` FOREIGN KEY (`sp_owner_id`) REFERENCES `sign_permit_applications`(`sp_owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sp_businesses`
  ADD CONSTRAINT `fk_sp_businesses_sp_business_id_58` FOREIGN KEY (`sp_business_id`) REFERENCES `sign_permit_applications`(`sp_business_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sp_contractors`
  ADD CONSTRAINT `fk_sp_contractors_sp_contractor_id_59` FOREIGN KEY (`sp_contractor_id`) REFERENCES `sign_permit_applications`(`contractor_id`) ON DELETE CASCADE ON UPDATE CASCADE;