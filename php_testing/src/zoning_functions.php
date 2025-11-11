<?php
/**
 * Zoning Map Amendment Application Functions
 * Contains all business logic for handling zoning map amendment applications
 */

/**
 * Process zoning map amendment form submission
 * 
 * @param array $postData The $_POST data
 * @param array $filesData The $_FILES data
 * @param mysqli $conn Database connection
 * @return array Result array with 'success' (bool) and 'message' (string)
 */
function processZoningMapAmendment($postData, $filesData, $conn) {
    try {
        // Extract and validate all POST data
        $formData = extractZoningFormData($postData, $filesData);
        
        // Validate required fields
        $validation = validateZoningFormData($formData);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Insert into database
        $result = insertZoningMapAmendment($formData, $conn);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error processing zoning map amendment: " . $e->getMessage());
        return ['success' => false, 'message' => 'An error occurred while processing your application.'];
    }
}

/**
 * Extract all form data from POST and FILES arrays
 * 
 * @param array $postData The $_POST data
 * @param array $filesData The $_FILES data
 * @return array Structured form data
 */
function extractZoningFormData($postData, $filesData) {
    $formData = [];
    
    // Header fields
    $formData['p_docket_number'] = getPostValue($postData, 'p_docket_number');
    $formData['p_public_hearing_date'] = getPostValue($postData, 'p_public_hearing_date');
    $formData['p_date_application_filed'] = getPostValue($postData, 'p_date_application_filed');
    $formData['p_application_meeting_date'] = getPostValue($postData, 'p_application_meeting_date');
    
    // Primary applicant fields
    $formData['applicant_name'] = getPostValue($postData, 'applicant_name');
    $formData['officers_names'] = getPostArrayAsJson($postData, 'officers_names');
    $formData['applicant_street'] = getPostValue($postData, 'applicant_street');
    $formData['applicant_phone'] = getPostValue($postData, 'applicant_phone');
    $formData['applicant_cell'] = getPostValue($postData, 'applicant_cell');
    $formData['applicant_city'] = getPostValue($postData, 'applicant_city');
    $formData['applicant_state'] = getPostValue($postData, 'applicant_state');
    $formData['applicant_zip_code'] = getPostValue($postData, 'applicant_zip_code');
    $formData['applicant_other_address'] = getPostValue($postData, 'applicant_other_address');
    $formData['applicant_email'] = getPostValue($postData, 'applicant_email');
    
    // Additional applicants
    $formData['additional_applicant_names'] = getPostArrayAsJson($postData, 'additional_applicant_names');
    $formData['additional_applicant_officers'] = extractAdditionalOfficers($postData);
    $formData['additional_applicant_streets'] = getPostArrayAsJson($postData, 'additional_applicant_streets');
    $formData['additional_applicant_phones'] = getPostArrayAsJson($postData, 'additional_applicant_phones');
    $formData['additional_applicant_cells'] = getPostArrayAsJson($postData, 'additional_applicant_cells');
    $formData['additional_applicant_cities'] = getPostArrayAsJson($postData, 'additional_applicant_cities');
    $formData['additional_applicant_states'] = getPostArrayAsJson($postData, 'additional_applicant_states');
    $formData['additional_applicant_zip_codes'] = getPostArrayAsJson($postData, 'additional_applicant_zip_codes');
    $formData['additional_applicant_other_addresses'] = getPostArrayAsJson($postData, 'additional_applicant_other_addresses');
    $formData['additional_applicant_emails'] = getPostArrayAsJson($postData, 'additional_applicant_emails');
    
    // Property owner fields
    $formData['applicant_first_name'] = getPostValue($postData, 'applicant_first_name');
    $formData['applicant_last_name'] = getPostValue($postData, 'applicant_last_name');
    $formData['owner_street'] = getPostValue($postData, 'owner_street');
    $formData['owner_phone'] = getPostValue($postData, 'owner_phone');
    $formData['owner_cell'] = getPostValue($postData, 'owner_cell');
    $formData['owner_city'] = getPostValue($postData, 'owner_city');
    $formData['owner_state'] = getPostValue($postData, 'owner_state');
    $formData['owner_zip_code'] = getPostValue($postData, 'owner_zip_code');
    $formData['owner_other_address'] = getPostValue($postData, 'owner_other_address');
    $formData['owner_email'] = getPostValue($postData, 'owner_email');
    
    // Additional property owners
    $formData['additional_owner_names'] = getPostArrayAsJson($postData, 'additional_owner_names');
    $formData['additional_owner_streets'] = getPostArrayAsJson($postData, 'additional_owner_streets');
    $formData['additional_owner_phones'] = getPostArrayAsJson($postData, 'additional_owner_phones');
    $formData['additional_owner_cells'] = getPostArrayAsJson($postData, 'additional_owner_cells');
    $formData['additional_owner_cities'] = getPostArrayAsJson($postData, 'additional_owner_cities');
    $formData['additional_owner_states'] = getPostArrayAsJson($postData, 'additional_owner_states');
    $formData['additional_owner_zip_codes'] = getPostArrayAsJson($postData, 'additional_owner_zip_codes');
    $formData['additional_owner_other_addresses'] = getPostArrayAsJson($postData, 'additional_owner_other_addresses');
    $formData['additional_owner_emails'] = getPostArrayAsJson($postData, 'additional_owner_emails');
    
    // Attorney fields
    $formData['attorney_first_name'] = getPostValue($postData, 'attorney_first_name');
    $formData['attorney_last_name'] = getPostValue($postData, 'attorney_last_name');
    $formData['law_firm'] = getPostValue($postData, 'law_firm');
    $formData['attorney_phone'] = getPostValue($postData, 'attorney_phone');
    $formData['attorney_cell'] = getPostValue($postData, 'attorney_cell');
    $formData['attorney_email'] = getPostValue($postData, 'attorney_email');
    
    // Property information
    $formData['property_street'] = getPostValue($postData, 'property_street');
    $formData['property_city'] = getPostValue($postData, 'property_city');
    $formData['property_state'] = getPostValue($postData, 'property_state');
    $formData['property_zip_code'] = getPostValue($postData, 'property_zip_code');
    $formData['property_other_address'] = getPostValue($postData, 'property_other_address');
    $formData['parcel_number'] = getPostValue($postData, 'parcel_number');
    $formData['acreage'] = getPostValue($postData, 'acreage');
    $formData['current_zoning'] = getPostValue($postData, 'current_zoning');
    
    // Request details
    $formData['p_zoning_map_amendment_request'] = getPostValue($postData, 'p_zoning_map_amendment_request');
    $formData['zone_change_conditions'] = getPostValue($postData, 'zone_change_conditions');
    $formData['finding_type'] = getPostValue($postData, 'finding_type');
    $formData['findings_explanation'] = getPostValue($postData, 'findings_explanation');
    
    // Checklist items
    $formData['checklist_application'] = isset($postData['checklist_application']) ? 1 : 0;
    $formData['checklist_exhibit'] = isset($postData['checklist_exhibit']) ? 1 : 0;
    $formData['checklist_adjacent'] = isset($postData['checklist_adjacent']) ? 1 : 0;
    $formData['checklist_verification'] = isset($postData['checklist_verification']) ? 1 : 0;
    $formData['checklist_fees'] = isset($postData['checklist_fees']) ? 1 : 0;
    $formData['checklist_conditions'] = isset($postData['checklist_conditions']) ? 1 : 0;
    $formData['checklist_concept'] = isset($postData['checklist_concept']) ? 1 : 0;
    $formData['checklist_traffic'] = isset($postData['checklist_traffic']) ? 1 : 0;
    $formData['checklist_geologic'] = isset($postData['checklist_geologic']) ? 1 : 0;
    
    // File uploads
    $formData['file_exhibit'] = extractFileData($filesData, 'file_exhibit');
    $formData['file_adjacent'] = extractFileData($filesData, 'file_adjacent');
    $formData['file_verification'] = extractFileData($filesData, 'file_verification');
    $formData['file_conditions'] = extractFileData($filesData, 'file_conditions');
    $formData['file_concept'] = extractFileData($filesData, 'file_concept');
    $formData['file_traffic'] = extractFileData($filesData, 'file_traffic');
    $formData['file_geologic'] = extractFileData($filesData, 'file_geologic');
    
    // Signature fields
    $formData['signature_date_1'] = getPostValue($postData, 'signature_date_1');
    $formData['signature_name_1'] = getPostValue($postData, 'signature_name_1');
    $formData['signature_date_2'] = getPostValue($postData, 'signature_date_2');
    $formData['signature_name_2'] = getPostValue($postData, 'signature_name_2');
    
    // Admin fields
    $formData['application_fee'] = getPostValue($postData, 'application_fee');
    $formData['certificate_fee'] = getPostValue($postData, 'certificate_fee');
    $formData['p_form_datetime_resolved'] = getPostValue($postData, 'p_form_datetime_resolved');
    $formData['p_form_paid_bool'] = isset($postData['p_form_paid_bool']) ? 1 : 0;
    $formData['p_correction_form_id'] = getPostValue($postData, 'p_correction_form_id');
    
    return $formData;
}

/**
 * Helper function to get POST value or null
 */
function getPostValue($postData, $key) {
    return isset($postData[$key]) && $postData[$key] !== '' ? $postData[$key] : null;
}

/**
 * Helper function to get POST array and convert to JSON
 */
function getPostArrayAsJson($postData, $key) {
    return isset($postData[$key]) && is_array($postData[$key]) ? json_encode($postData[$key]) : null;
}

/**
 * Extract additional applicant officers from dynamic fields
 */
function extractAdditionalOfficers($postData) {
    $additionalOfficers = [];
    foreach ($postData as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value)) {
                $additionalOfficers[$matches[1]] = $value;
            }
        }
    }
    return !empty($additionalOfficers) ? json_encode($additionalOfficers) : null;
}

/**
 * Extract file data and convert to blob
 */
function extractFileData($filesData, $key) {
    if (isset($filesData[$key]) && $filesData[$key]['error'] === UPLOAD_ERR_OK) {
        return file_get_contents($filesData[$key]['tmp_name']);
    }
    return null;
}

/**
 * Validate zoning form data
 * 
 * @param array $formData The extracted form data
 * @return array Validation result with 'valid' (bool) and 'message' (string)
 */
function validateZoningFormData($formData) {
    $errors = [];
    
    // Validate required fields
    if (empty($formData['applicant_name'])) {
        $errors[] = "Applicant name is required.";
    }
    
    if (empty($formData['p_zoning_map_amendment_request'])) {
        $errors[] = "Zoning map amendment request description is required.";
    }
    
    // Validate email format if provided
    if (!empty($formData['applicant_email']) && !filter_var($formData['applicant_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid applicant email address.";
    }
    
    if (!empty($formData['owner_email']) && !filter_var($formData['owner_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid property owner email address.";
    }
    
    if (!empty($formData['attorney_email']) && !filter_var($formData['attorney_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid attorney email address.";
    }
    
    if (!empty($errors)) {
        return ['valid' => false, 'message' => implode(' ', $errors)];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Insert zoning map amendment application into database
 * 
 * @param array $formData The validated form data
 * @param mysqli $conn Database connection
 * @return array Result with 'success' (bool) and 'message' (string)
 */
function insertZoningMapAmendment($formData, $conn) {
    $sql = "CALL sp_insert_zoning_map_amendment_application(?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return ['success' => false, 'message' => 'Database prepare failed: ' . $conn->error];
    }
    
    $types = 'siis';
    $bind_names = array();
    $bind_names[] = &$formData['p_form_datetime_resolved'];
    $bind_names[] = &$formData['p_form_paid_bool'];
    $bind_names[] = &$formData['p_correction_form_id'];
    $bind_names[] = &$formData['p_zoning_map_amendment_request'];
    array_unshift($bind_names, $types);
    
    $bindResult = call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    
    if ($bindResult === false) {
        $stmt->close();
        return ['success' => false, 'message' => 'Database bind failed: ' . $stmt->error];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Database execute failed: ' . $error];
    }
    
    $stmt->close();
    return ['success' => true, 'message' => 'Form submitted successfully!'];
}