<?php
/**
 * Zoning Application Update Functions
 * Functions for updating existing form submissions
 */

/**
 * Updates Zoning Verification Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateZoningVerificationApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_zoning_verification_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isiisssssssssssssssssssss';
    
    $params = [
        $formId,
        $formData['p_form_datetime_resolved'],
        $formData['p_form_paid_bool'],
        $formData['p_correction_form_id'],
        $formData['p_zva_letter_content'],
        $formData['p_zva_zoning_letter_street'],
        $formData['p_zva_zoning_letter_city'],
        $formData['p_zva_state_code'],
        $formData['p_zva_zoning_letter_zip'],
        $formData['p_zva_property_street'],
        $formData['p_property_city'],
        $formData['p_zva_property_state_code'],
        $formData['p_zva_property_zip'],
        $formData['p_zva_applicant_first_name'],
        $formData['p_zva_applicant_last_name'],
        $formData['p_zva_applicant_street'],
        $formData['p_zva_applicant_city'],
        $formData['p_zva_applicant_state_code'],
        $formData['p_zva_applicant_zip_code'],
        $formData['p_zva_applicant_phone_number'],
        $formData['p_zva_applicant_fax_number'],
        $formData['p_zva_owner_first_name'],
        $formData['p_zva_owner_last_name'],
        $formData['p_zva_owner_street'],
        $formData['p_zva_owner_city'],
        $formData['p_zva_owner_state_code'],
        $formData['p_zva_owner_zip_code'],
    ];
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Zoning Map Amendment Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateZoningMapAmendmentApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_zoning_map_amendment_application(" . str_repeat("?,", 81) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isiisssssssssssssssssssssssssssssssssssissssssssssssisssssssssssiiiiiiiiissssssss';
    
    $params = [
        $formId,
        $formData['p_form_datetime_resolved'],
        $formData['p_form_paid_bool'],
        $formData['p_correction_form_id'],
        $formData['p_docket_number'],
        $formData['p_public_hearing_date'],
        $formData['p_date_application_filed'],
        $formData['p_preapp_meeting_date'],
        $formData['p_applicant_name'],
        $formData['p_officers_names'],
        $formData['p_applicant_street'],
        $formData['p_applicant_phone'],
        $formData['p_applicant_cell'],
        $formData['p_applicant_city'],
        $formData['p_applicant_state'],
        $formData['p_applicant_zip_code'],
        $formData['p_applicant_other_address'],
        $formData['p_applicant_email'],
        $formData['p_additional_applicant_names'],
        $formData['p_additional_applicant_officers'],
        $formData['p_additional_applicant_streets'],
        $formData['p_additional_applicant_phones'],
        $formData['p_additional_applicant_cells'],
        $formData['p_additional_applicant_cities'],
        $formData['p_additional_applicant_states'],
        $formData['p_additional_applicant_zip_codes'],
        $formData['p_additional_applicant_other_addresses'],
        $formData['p_additional_applicant_emails'],
        $formData['p_owner_first_name'],
        $formData['p_owner_last_name'],
        $formData['p_owner_street'],
        $formData['p_owner_phone'],
        $formData['p_owner_cell'],
        $formData['p_owner_city'],
        $formData['p_owner_state'],
        $formData['p_owner_zip_code'],
        $formData['p_owner_other_address'],
        $formData['p_owner_email'],
        $formData['p_additional_owner_names'],
        $formData['p_additional_owner_streets'],
        $formData['p_additional_owner_phones'],
        $formData['p_additional_owner_cells'],
        $formData['p_additional_owner_cities'],
        $formData['p_additional_owner_states'],
        $formData['p_additional_owner_zip_codes'],
        $formData['p_additional_owner_other_addresses'],
        $formData['p_additional_owner_emails'],
        $formData['p_attorney_first_name'],
        $formData['p_attorney_last_name'],
        $formData['p_law_firm'],
        $formData['p_attorney_phone'],
        $formData['p_attorney_cell'],
        $formData['p_attorney_email'],
        $formData['p_property_street'],
        $formData['p_property_city'],
        $formData['p_property_state'],
        $formData['p_property_zip_code'],
        $formData['p_property_other_address'],
        $formData['p_parcel_number'],
        $formData['p_acreage'],
        $formData['p_current_zoning'],
        $formData['p_zoning_map_amendment_request'],
        $formData['p_zmaa_proposed_conditions'],
        $formData['p_finding_type'],
        $formData['p_findings_explanation'],
        $formData['p_checklist_application'],
        $formData['p_checklist_exhibit'],
        $formData['p_checklist_adjacent'],
        $formData['p_checklist_verification'],
        $formData['p_checklist_fees'],
        $formData['p_checklist_conditions'],
        $formData['p_checklist_concept'],
        $formData['p_checklist_traffic'],
        $formData['p_checklist_geologic'],
        $formData['p_file_exhibit'],
        $formData['p_file_adjacent'],
        $formData['p_file_verification'],
        $formData['p_file_conditions'],
        $formData['p_file_concept'],
        $formData['p_file_traffic'],
        $formData['p_file_geologic'],
        $formData['p_signature_date_1'],
        $formData['p_signature_name_1'],
        $formData['p_signature_date_2'],
        $formData['p_signature_name_2'],
        $formData['p_application_fee'],
        $formData['p_certificate_fee'],
    ];
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Variance Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateVarianceApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_variance_application(" . str_repeat("?,", 67) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isiisssssssssssssssssssssssssssssssssssisssssssssssisssssssiiissssss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Site Development Plan Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateSiteDevelopmentPlanApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_site_development_plan_application_comprehensive(" . str_repeat("?,", 83) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isiissssssssssssssssssssssssssssssssssssssssssssssssssssisssssssssssiiiiiiiiisssss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates General Development Plan Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateGeneralDevelopmentPlanApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_general_development_plan_application_comprehensive(" . str_repeat("?,", 68) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'iidddssssssssssssssssssssssssssssssssssisssssssssssssssssssssssssss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Future Land Use Map Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateFutureLandUseMapApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_future_land_use_map_application_comprehensive(" . str_repeat("?,", 59) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isiisssssssssssssssssssssssssssisssssssssssssssssssssssssss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Conditional Use Permit Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateConditionalUsePermitApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_conditional_use_permit_application(" . str_repeat("?,", 43) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'issssssssssssssssssssssssssssssissssissss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Administrative Appeal Request Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateAdministrativeAppealApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_administrative_appeal_request(" . str_repeat("?,", 19) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'iissssssssssssssssss';
    
    $params = [
        $formId,
        $formData['form_paid_bool'],
        $formData['hearing_date'],
        $formData['submit_date'],
        $formData['street_address'],
        $formData['city_address'],
        $formData['state_code'],
        $formData['zip_code'],
        $formData['property_location'],
        $formData['official_decision'],
        $formData['relevant_provisions'],
        $formData['appellant_first_name'],
        $formData['appellant_last_name'],
        $formData['additional_appellants'],
        $formData['adjacent_property_owner_street'],
        $formData['adjacent_property_owner_city'],
        $formData['adjacent_property_owner_state_code'],
        $formData['adjacent_property_owner_zip'],
        $formData['property_owner_first_name'],
        $formData['property_owner_last_name'],
        $formData['additional_property_owners'],
    ];
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Adjacent Property Owners Form Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateAdjacentPropertyOwnersFormApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isssssssss';
    
    $params = [
        $formId,
        $formData['pva_map_codes'],
        $formData['neighbor_property_locations'],
        $formData['neighbor_property_deed_books'],
        $formData['property_street_pg_numbers'],
        $formData['property_owner_names'],
        $formData['property_owner_streets'],
        $formData['property_owner_cities'],
        $formData['property_owner_state_codes'],
        $formData['property_owner_zips'],
    ];
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Minor Subdivision Plat Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateMinorSubdivisionPlatApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_minor_subdivision_plat_application(" . str_repeat("?,", 76) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isssssssssssssssssssssssssssssssissssssssssssisssssssssssiiiiiii';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}

/**
 * Updates Major Subdivision Plat Application
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID to update
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function updateMajorSubdivisionPlatApplication($conn, int $formId, array $formData): array
{
    $sql = "CALL sp_update_major_subdivision_plat_application(" . str_repeat("?,", 92) . "?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $types = 'isssssssssssssssssssssssssssssssissssssssssssisssssssssssiiiiiiiiiiiiiiissssssssssss';
    
    $params = array_merge([$formId], array_values($formData));
    
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form updated successfully!'
    ];
}