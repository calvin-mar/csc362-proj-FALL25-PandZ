<?php
/**
 * Zoning Application Functions
 * Extracted and refactored for testability
 */

/**
 * Sanitizes and extracts form data from POST request for Zoning Verification Application
 * 
 * @param array $post The $_POST array
 * @return array Sanitized form data with all expected fields
 */
function extractZoningFormData(array $post): array
{
    return [
        'p_form_datetime_resolved' => isset($post['p_form_datetime_resolved']) && $post['p_form_datetime_resolved'] !== '' 
            ? $post['p_form_datetime_resolved'] : null,
        'p_form_paid_bool' => 0,
        'p_correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? $post['p_correction_form_id'] : null,
        'p_zva_letter_content' => isset($post['p_zva_letter_content']) && $post['p_zva_letter_content'] !== '' 
            ? $post['p_zva_letter_content'] : null,
        'p_zva_zoning_letter_street' => isset($post['p_zva_zoning_letter_street']) && $post['p_zva_zoning_letter_street'] !== '' 
            ? $post['p_zva_zoning_letter_street'] : null,
        'p_zva_zoning_letter_city' => isset($post['p_zva_zoning_letter_city']) && $post['p_zva_zoning_letter_city'] !== '' 
            ? $post['p_zva_zoning_letter_city'] : null,
        'p_zva_state_code' => isset($post['p_zva_state_code']) && $post['p_zva_state_code'] !== '' 
            ? $post['p_zva_state_code'] : null,
        'p_zva_zoning_letter_zip' => isset($post['p_zva_zoning_letter_zip']) && $post['p_zva_zoning_letter_zip'] !== '' 
            ? $post['p_zva_zoning_letter_zip'] : null,
        'p_zva_property_street' => isset($post['p_zva_property_street']) && $post['p_zva_property_street'] !== '' 
            ? $post['p_zva_property_street'] : null,
        'p_property_city' => isset($post['p_property_city']) && $post['p_property_city'] !== '' 
            ? $post['p_property_city'] : null,
        'p_zva_property_state_code' => isset($post['p_zva_property_state_code']) && $post['p_zva_property_state_code'] !== '' 
            ? $post['p_zva_property_state_code'] : null,
        'p_zva_property_zip' => isset($post['p_zva_property_zip']) && $post['p_zva_property_zip'] !== '' 
            ? $post['p_zva_property_zip'] : null,
        'p_zva_applicant_first_name' => isset($post['p_zva_applicant_first_name']) && $post['p_zva_applicant_first_name'] !== '' 
            ? $post['p_zva_applicant_first_name'] : null,
        'p_zva_applicant_last_name' => isset($post['p_zva_applicant_last_name']) && $post['p_zva_applicant_last_name'] !== '' 
            ? $post['p_zva_applicant_last_name'] : null,
        'p_zva_applicant_street' => isset($post['p_zva_applicant_street']) && $post['p_zva_applicant_street'] !== '' 
            ? $post['p_zva_applicant_street'] : null,
        'p_zva_applicant_city' => isset($post['p_zva_applicant_city']) && $post['p_zva_applicant_city'] !== '' 
            ? $post['p_zva_applicant_city'] : null,
        'p_zva_applicant_state_code' => isset($post['p_zva_applicant_state_code']) && $post['p_zva_applicant_state_code'] !== '' 
            ? $post['p_zva_applicant_state_code'] : null,
        'p_zva_applicant_zip_code' => isset($post['p_zva_applicant_zip_code']) && $post['p_zva_applicant_zip_code'] !== '' 
            ? $post['p_zva_applicant_zip_code'] : null,
        'p_zva_applicant_phone_number' => isset($post['p_zva_applicant_phone_number']) && $post['p_zva_applicant_phone_number'] !== '' 
            ? $post['p_zva_applicant_phone_number'] : null,
        'p_zva_applicant_fax_number' => isset($post['p_zva_applicant_fax_number']) && $post['p_zva_applicant_fax_number'] !== '' 
            ? $post['p_zva_applicant_fax_number'] : null,
        'p_zva_owner_first_name' => isset($post['p_zva_owner_first_name']) && $post['p_zva_owner_first_name'] !== '' 
            ? $post['p_zva_owner_first_name'] : null,
        'p_zva_owner_last_name' => isset($post['p_zva_owner_last_name']) && $post['p_zva_owner_last_name'] !== '' 
            ? $post['p_zva_owner_last_name'] : null,
        'p_zva_owner_street' => isset($post['p_zva_owner_street']) && $post['p_zva_owner_street'] !== '' 
            ? $post['p_zva_owner_street'] : null,
        'p_zva_owner_city' => isset($post['p_zva_owner_city']) && $post['p_zva_owner_city'] !== '' 
            ? $post['p_zva_owner_city'] : null,
        'p_zva_owner_state_code' => isset($post['p_zva_owner_state_code']) && $post['p_zva_owner_state_code'] !== '' 
            ? $post['p_zva_owner_state_code'] : null,
        'p_zva_owner_zip_code' => isset($post['p_zva_owner_zip_code']) && $post['p_zva_owner_zip_code'] !== '' 
            ? $post['p_zva_owner_zip_code'] : null,
    ];
}

/**
 * Extracts form data for Zoning Map Amendment Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractZoningMapAmendmentFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    return [
        // Form metadata
        'p_form_datetime_resolved' => $post['p_form_datetime_resolved'] ?? null,
        'p_form_paid_bool' => isset($post['p_form_paid_bool']) ? 1 : 0,
        'p_correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? (int)$post['p_correction_form_id'] : null,
        
        // Hearing information
        'p_docket_number' => $post['p_docket_number'] ?? null,
        'p_public_hearing_date' => $post['p_public_hearing_date'] ?? null,
        'p_date_application_filed' => $post['p_date_application_filed'] ?? null,
        'p_preapp_meeting_date' => $post['p_application_meeting_date'] ?? null,
        
        // Primary applicant
        'p_applicant_name' => $post['applicant_name'] ?? null,
        'p_officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'p_applicant_street' => $post['applicant_street'] ?? null,
        'p_applicant_phone' => $post['applicant_phone'] ?? null,
        'p_applicant_cell' => $post['applicant_cell'] ?? null,
        'p_applicant_city' => $post['applicant_city'] ?? null,
        'p_applicant_state' => $post['applicant_state'] ?? null,
        'p_applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'p_applicant_other_address' => $post['applicant_other_address'] ?? null,
        'p_applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants
        'p_additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'p_additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'p_additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'p_additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'p_additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'p_additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'p_additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'p_additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'p_additional_applicant_other_addresses' => convertArrayToJson($post['additional_applicant_other_addresses'] ?? [], false),
        'p_additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner
        'p_owner_first_name' => $post['applicant_first_name'] ?? null,
        'p_owner_last_name' => $post['applicant_last_name'] ?? null,
        'p_owner_street' => $post['owner_street'] ?? null,
        'p_owner_phone' => $post['owner_phone'] ?? null,
        'p_owner_cell' => $post['owner_cell'] ?? null,
        'p_owner_city' => $post['owner_city'] ?? null,
        'p_owner_state' => $post['owner_state'] ?? null,
        'p_owner_zip_code' => $post['owner_zip_code'] ?? null,
        'p_owner_other_address' => $post['owner_other_address'] ?? null,
        'p_owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners
        'p_additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'p_additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'p_additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'p_additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'p_additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'p_additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'p_additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'p_additional_owner_other_addresses' => convertArrayToJson($post['additional_owner_other_addresses'] ?? [], false),
        'p_additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Attorney
        'p_attorney_first_name' => $post['attorney_first_name'] ?? null,
        'p_attorney_last_name' => $post['attorney_last_name'] ?? null,
        'p_law_firm' => $post['law_firm'] ?? null,
        'p_attorney_phone' => $post['attorney_phone'] ?? null,
        'p_attorney_cell' => $post['attorney_cell'] ?? null,
        'p_attorney_email' => $post['attorney_email'] ?? null,
        
        // Property information
        'p_property_street' => $post['property_street'] ?? null,
        'p_property_city' => $post['property_city'] ?? null,
        'p_property_state' => $post['property_state'] ?? null,
        'p_property_zip_code' => $post['property_zip_code'] ?? null,
        'p_property_other_address' => $post['property_other_address'] ?? null,
        'p_parcel_number' => isset($post['parcel_number']) && $post['parcel_number'] !== '' 
            ? (int)$post['parcel_number'] : null,
        'p_acreage' => $post['acreage'] ?? null,
        'p_current_zoning' => $post['current_zoning'] ?? null,
        
        // Amendment request
        'p_zoning_map_amendment_request' => $post['p_zoning_map_amendment_request'] ?? null,
        'p_zmaa_proposed_conditions' => $post['zone_change_conditions'] ?? null,
        
        // Findings
        'p_finding_type' => $post['finding_type'] ?? null,
        'p_findings_explanation' => $post['findings_explanation'] ?? null,
        
        // Checklist items
        'p_checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'p_checklist_exhibit' => isset($post['checklist_exhibit']) ? 1 : 0,
        'p_checklist_adjacent' => isset($post['checklist_adjacent']) ? 1 : 0,
        'p_checklist_verification' => isset($post['checklist_verification']) ? 1 : 0,
        'p_checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        'p_checklist_conditions' => isset($post['checklist_conditions']) ? 1 : 0,
        'p_checklist_concept' => isset($post['checklist_concept']) ? 1 : 0,
        'p_checklist_traffic' => isset($post['checklist_traffic']) ? 1 : 0,
        'p_checklist_geologic' => isset($post['checklist_geologic']) ? 1 : 0,
        
        // File uploads
        'p_file_exhibit' => extractUploadedFileName($files, 'file_exhibit'),
        'p_file_adjacent' => extractUploadedFileName($files, 'file_adjacent'),
        'p_file_verification' => extractUploadedFileName($files, 'file_verification'),
        'p_file_conditions' => extractUploadedFileName($files, 'file_conditions'),
        'p_file_concept' => extractUploadedFileName($files, 'file_concept'),
        'p_file_traffic' => extractUploadedFileName($files, 'file_traffic'),
        'p_file_geologic' => extractUploadedFileName($files, 'file_geologic'),
        
        // Signatures
        'p_signature_date_1' => $post['signature_date_1'] ?? null,
        'p_signature_name_1' => $post['signature_name_1'] ?? null,
        'p_signature_date_2' => $post['signature_date_2'] ?? null,
        'p_signature_name_2' => $post['signature_name_2'] ?? null,
        
        // Admin/fees
        'p_application_fee' => $post['application_fee'] ?? null,
        'p_certificate_fee' => $post['certificate_fee'] ?? null,
    ];
}

/**
 * Converts an array to JSON string, optionally filtering empty values
 * 
 * @param array $array The array to convert
 * @param bool $filterEmpty Whether to filter out empty values
 * @return string|null JSON string or null if array is empty
 */
function convertArrayToJson(array $array, bool $filterEmpty = true): ?string
{
    if (!is_array($array)) {
        return null;
    }
    
    $data = $filterEmpty ? array_filter($array) : $array;
    
    return !empty($data) ? json_encode($data) : null;
}

/**
 * Extracts uploaded file name from $_FILES array
 * 
 * @param array $files The $_FILES array
 * @param string $fieldName The field name
 * @return string|null The uploaded filename or null
 */
function extractUploadedFileName(array $files, string $fieldName): ?string
{
    if (isset($files[$fieldName]) && $files[$fieldName]['error'] === UPLOAD_ERR_OK) {
        return $files[$fieldName]['name'];
    }
    return null;
}

/**
 * Validates required fields for zoning verification application
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateZoningFormData(array $formData): array
{
    $errors = [];
    
    // Define required fields (adjust based on your business rules)
    $requiredFields = [
        'p_zva_property_street' => 'Property street address',
        'p_property_city' => 'Property city',
        'p_zva_property_state_code' => 'Property state',
        'p_zva_property_zip' => 'Property ZIP code',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP code format if provided
    $zipFields = [
        'p_zva_property_zip' => 'Property ZIP code',
        'p_zva_zoning_letter_zip' => 'Zoning letter ZIP code',
        'p_zva_applicant_zip_code' => 'Applicant ZIP code',
        'p_zva_owner_zip_code' => 'Owner ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone number format if provided
    if (!empty($formData['p_zva_applicant_phone_number']) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData['p_zva_applicant_phone_number'])) {
        $errors[] = "Applicant phone number format is invalid";
    }
    
    return $errors;
}

/**
 * Validates Zoning Map Amendment Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateZoningMapAmendmentData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'p_property_street' => 'Property street address',
        'p_property_city' => 'Property city',
        'p_property_state' => 'Property state',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'p_property_zip_code' => 'Property ZIP code',
        'p_applicant_zip_code' => 'Applicant ZIP code',
        'p_owner_zip_code' => 'Owner ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers
    $phoneFields = [
        'p_applicant_phone' => 'Applicant phone',
        'p_applicant_cell' => 'Applicant cell',
        'p_owner_phone' => 'Owner phone',
        'p_owner_cell' => 'Owner cell',
        'p_attorney_phone' => 'Attorney phone',
        'p_attorney_cell' => 'Attorney cell',
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData[$field])) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate email addresses
    $emailFields = [
        'p_applicant_email' => 'Applicant email',
        'p_owner_email' => 'Owner email',
        'p_attorney_email' => 'Attorney email',
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    return $errors;
}

/**
 * Executes the stored procedure to insert zoning verification application
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string]
 */
function insertZoningVerificationApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_zoning_verification_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    // Build bind parameters
    $types = 'siisssssssssssssssssssssss';
    $params = array_values($formData);
    
    // Create array of references for bind_param
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
        'message' => 'Form submitted successfully!'
    ];
}

/**
 * Inserts Zoning Map Amendment Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertZoningMapAmendmentApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_zoning_map_amendment_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (82 parameters)
    $types = 'siisssssssssssssssssssssssssssssssssssssssssssssssssisssiiiiiiiiissssss';
    
    // Extract values in order
    $params = array_values($formData);
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Links a form to a client in the database
 * 
 * @param mysqli $conn Database connection
 * @param int $formId The form ID
 * @param int $clientId The client ID
 * @return array ['success' => bool, 'message' => string]
 */
function linkFormToClient($conn, int $formId, int $clientId): array
{
    $sql = "INSERT INTO client_forms(form_id, client_id) VALUES(?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error
        ];
    }
    
    $stmt->bind_param('ii', $formId, $clientId);
    
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
        'message' => 'Form linked to client successfully'
    ];
}

/**
 * Fetches state codes from database
 * 
 * @param mysqli $conn Database connection
 * @return array Array of state codes
 */
function fetchStateCodes($conn): array
{
    $states = [];
    $result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $states[] = $row['state_code'];
        }
    }
    
    return $states;
}

/**
 * Formats address components into a single line
 * 
 * @param string|null $street Street address
 * @param string|null $city City
 * @param string|null $state State code
 * @param string|null $zip ZIP code
 * @return string Formatted address or empty string
 */
function formatAddress(?string $street, ?string $city, ?string $state, ?string $zip): string
{
    $parts = array_filter([$street, $city, $state, $zip]);
    return implode(', ', $parts);
}

/**
 * Extracts form data for Variance Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractVarianceApplicationFormData(array $post, array $files = []): array
{
    return [
        // Form metadata
        'p_form_datetime_resolved' => $post['p_form_datetime_resolved'] ?? null,
        'p_form_paid_bool' => 0, // Always 0 when submitted by client
        'p_correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? (int)$post['p_correction_form_id'] : null,
        
        // Hearing information
        'p_docket_number' => $post['p_docket_number'] ?? null,
        'p_public_hearing_date' => $post['p_public_hearing_date'] ?? null,
        'p_date_application_filed' => $post['p_date_application_filed'] ?? null,
        'p_preapp_meeting_date' => $post['p_preapp_meeting_date'] ?? null,
        
        // Primary applicant
        'p_applicant_name' => $post['p_applicant_name'] ?? null,
        'p_officers_names' => convertArrayToJson($post['p_officers_names'] ?? []),
        'p_applicant_street' => $post['p_applicant_street'] ?? null,
        'p_applicant_phone' => $post['p_applicant_phone'] ?? null,
        'p_applicant_cell' => $post['p_applicant_cell'] ?? null,
        'p_applicant_city' => $post['p_applicant_city'] ?? null,
        'p_applicant_state' => $post['p_applicant_state'] ?? null,
        'p_applicant_zip_code' => $post['p_applicant_zip_code'] ?? null,
        'p_applicant_other_address' => $post['p_applicant_other_address'] ?? null,
        'p_applicant_email' => $post['p_applicant_email'] ?? null,
        
        // Additional applicants
        'p_additional_applicant_names' => convertArrayToJson($post['p_additional_applicant_names'] ?? []),
        'p_additional_applicant_officers' => convertNestedArrayToJson($post['p_additional_applicant_officers'] ?? []),
        'p_additional_applicant_streets' => convertArrayToJson($post['p_additional_applicant_streets'] ?? [], false),
        'p_additional_applicant_phones' => convertArrayToJson($post['p_additional_applicant_phones'] ?? [], false),
        'p_additional_applicant_cells' => convertArrayToJson($post['p_additional_applicant_cells'] ?? [], false),
        'p_additional_applicant_cities' => convertArrayToJson($post['p_additional_applicant_cities'] ?? [], false),
        'p_additional_applicant_states' => convertArrayToJson($post['p_additional_applicant_states'] ?? [], false),
        'p_additional_applicant_zip_codes' => convertArrayToJson($post['p_additional_applicant_zip_codes'] ?? [], false),
        'p_additional_applicant_other_addresses' => convertArrayToJson($post['p_additional_applicant_other_addresses'] ?? [], false),
        'p_additional_applicant_emails' => convertArrayToJson($post['p_additional_applicant_emails'] ?? [], false),
        
        // Property owner
        'p_owner_first_name' => $post['p_owner_first_name'] ?? null,
        'p_owner_last_name' => $post['p_owner_last_name'] ?? null,
        'p_owner_street' => $post['p_owner_street'] ?? null,
        'p_owner_phone' => $post['p_owner_phone'] ?? null,
        'p_owner_cell' => $post['p_owner_cell'] ?? null,
        'p_owner_city' => $post['p_owner_city'] ?? null,
        'p_owner_state' => $post['p_owner_state'] ?? null,
        'p_owner_zip_code' => $post['p_owner_zip_code'] ?? null,
        'p_owner_other_address' => $post['p_owner_other_address'] ?? null,
        'p_owner_email' => $post['p_owner_email'] ?? null,
        
        // Additional owners
        'p_additional_owner_names' => convertArrayToJson($post['p_additional_owner_names'] ?? []),
        'p_additional_owner_streets' => convertArrayToJson($post['p_additional_owner_streets'] ?? [], false),
        'p_additional_owner_phones' => convertArrayToJson($post['p_additional_owner_phones'] ?? [], false),
        'p_additional_owner_cells' => convertArrayToJson($post['p_additional_owner_cells'] ?? [], false),
        'p_additional_owner_cities' => convertArrayToJson($post['p_additional_owner_cities'] ?? [], false),
        'p_additional_owner_states' => convertArrayToJson($post['p_additional_owner_states'] ?? [], false),
        'p_additional_owner_zip_codes' => convertArrayToJson($post['p_additional_owner_zip_codes'] ?? [], false),
        'p_additional_owner_other_addresses' => convertArrayToJson($post['p_additional_owner_other_addresses'] ?? [], false),
        'p_additional_owner_emails' => convertArrayToJson($post['p_additional_owner_emails'] ?? [], false),
        
        // Attorney
        'p_attorney_first_name' => $post['p_attorney_first_name'] ?? null,
        'p_attorney_last_name' => $post['p_attorney_last_name'] ?? null,
        'p_law_firm' => $post['p_law_firm'] ?? null,
        'p_attorney_phone' => $post['p_attorney_phone'] ?? null,
        'p_attorney_cell' => $post['p_attorney_cell'] ?? null,
        'p_attorney_email' => $post['p_attorney_email'] ?? null,
        
        // Property information
        'p_property_street' => $post['p_property_street'] ?? null,
        'p_property_city' => $post['p_property_city'] ?? null,
        'p_property_state' => $post['p_property_state'] ?? null,
        'p_property_zip_code' => $post['p_property_zip_code'] ?? null,
        'p_property_other_address' => $post['p_property_other_address'] ?? null,
        'p_parcel_number' => $post['p_parcel_number'] ?? null,
        'p_acreage' => $post['p_acreage'] ?? null,
        'p_current_zoning' => $post['p_current_zoning'] ?? null,
        
        // Variance request
        'p_variance_request' => $post['p_variance_request'] ?? null,
        'p_proposed_conditions' => $post['p_proposed_conditions'] ?? null,
        'p_findings_explanation' => $post['p_findings_explanation'] ?? null,
        
        // Checklist items
        'p_checklist_application' => isset($post['p_checklist_application']) ? 1 : 0,
        'p_checklist_exhibit' => isset($post['p_checklist_exhibit']) ? 1 : 0,
        'p_checklist_adjacent' => isset($post['p_checklist_adjacent']) ? 1 : 0,
        'p_checklist_fees' => isset($post['p_checklist_fees']) ? 1 : 0,
        
        // File uploads
        'p_file_exhibit' => extractUploadedFileName($files, 'p_file_exhibit'),
        'p_file_adjacent' => extractUploadedFileName($files, 'p_file_adjacent'),
        
        // Signatures
        'p_signature_date_1' => $post['p_signature_date_1'] ?? null,
        'p_signature_name_1' => $post['p_signature_name_1'] ?? null,
        'p_signature_date_2' => $post['p_signature_date_2'] ?? null,
        'p_signature_name_2' => $post['p_signature_name_2'] ?? null,
        
        // Admin/fees
        'p_application_fee' => $post['p_application_fee'] ?? null,
        'p_certificate_fee' => $post['p_certificate_fee'] ?? null,
    ];
}

/**
 * Converts nested array to JSON (for additional applicant officers)
 * Filters out empty sub-arrays
 * 
 * @param array $array Nested array
 * @return string|null JSON string or null
 */
function convertNestedArrayToJson(array $array): ?string
{
    if (!is_array($array) || empty($array)) {
        return null;
    }
    
    $filtered = array_filter($array, function($item) {
        return !empty($item) && is_array($item) && !empty(array_filter($item));
    });
    
    return !empty($filtered) ? json_encode($filtered) : null;
}

/**
 * Validates Variance Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateVarianceApplicationData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'p_applicant_name' => 'Applicant name',
        'p_variance_request' => 'Variance request description',
        'p_signature_name_1' => 'At least one signature',
        'p_signature_date_1' => 'At least one signature date',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'p_property_zip_code' => 'Property ZIP code',
        'p_applicant_zip_code' => 'Applicant ZIP code',
        'p_owner_zip_code' => 'Owner ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers
    $phoneFields = [
        'p_applicant_phone' => 'Applicant phone',
        'p_applicant_cell' => 'Applicant cell',
        'p_owner_phone' => 'Owner phone',
        'p_owner_cell' => 'Owner cell',
        'p_attorney_phone' => 'Attorney phone',
        'p_attorney_cell' => 'Attorney cell',
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData[$field])) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate email addresses
    $emailFields = [
        'p_applicant_email' => 'Applicant email',
        'p_owner_email' => 'Owner email',
        'p_attorney_email' => 'Attorney email',
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    return $errors;
}

/**
 * Inserts Variance Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertVarianceApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_variance_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (68 parameters)
    $types = 'siisssssssssssssssssssssssssssssssssssssssssssssssisssssiiiissssss';
    
    // Extract values in order
    $params = array_values($formData);
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $stmt->next_result();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Sanitizes phone number to a standard format
 * 
 * @param string|null $phone Phone number
 * @return string|null Sanitized phone number or null
 */
function sanitizePhoneNumber(?string $phone): ?string
{
    if (empty($phone)) {
        return null;
    }
    
    // Remove all non-numeric characters except +
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    
    return $cleaned ?: null;
}

/**
 * Extracts form data for Site Development Plan Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractSiteDevelopmentPlanFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    return [
        // Form metadata
        'form_datetime_resolved' => null,
        'form_paid_bool' => isset($post['form_paid_bool']) ? 1 : 0,
        'correction_form_id' => $post['correction_form_id'] ?? null,
        
        // Hearing information
        'docket_number' => $post['docket_number'] ?? null,
        'public_hearing_date' => $post['public_hearing_date'] ?? null,
        'date_application_filed' => $post['date_application_filed'] ?? null,
        'pre_application_meeting_date' => $post['pre_application_meeting_date'] ?? null,
        
        // Primary applicant
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_street' => $post['applicant_street'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_city' => $post['applicant_city'] ?? null,
        'applicant_state' => $post['applicant_state'] ?? null,
        'applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner
        'owner_first_name' => $post['applicant_first_name'] ?? null,
        'owner_last_name' => $post['applicant_last_name'] ?? null,
        'owner_street' => $post['owner_street'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_city' => $post['owner_city'] ?? null,
        'owner_state' => $post['owner_state'] ?? null,
        'owner_zip_code' => $post['owner_zip_code'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Attorney
        'attorney_first_name' => $post['attorney_first_name'] ?? null,
        'attorney_last_name' => $post['attorney_last_name'] ?? null,
        'law_firm' => $post['law_firm'] ?? null,
        'attorney_phone' => $post['attorney_phone'] ?? null,
        'attorney_cell' => $post['attorney_cell'] ?? null,
        'attorney_email' => $post['attorney_email'] ?? null,
        
        // Professionals (split names)
        'surveyor_first_name' => splitFirstName($post['surveyor_name'] ?? null),
        'surveyor_last_name' => splitLastName($post['surveyor_name'] ?? null),
        'surveyor_firm' => $post['surveyor_firm'] ?? null,
        'surveyor_phone' => $post['surveyor_phone'] ?? null,
        'surveyor_cell' => $post['surveyor_cell'] ?? null,
        'surveyor_email' => $post['surveyor_email'] ?? null,
        
        'engineer_first_name' => splitFirstName($post['engineer_name'] ?? null),
        'engineer_last_name' => splitLastName($post['engineer_name'] ?? null),
        'engineer_firm' => $post['engineer_firm'] ?? null,
        'engineer_phone' => $post['engineer_phone'] ?? null,
        'engineer_cell' => $post['engineer_cell'] ?? null,
        'engineer_email' => $post['engineer_email'] ?? null,
        
        'architect_first_name' => splitFirstName($post['architect_name'] ?? null),
        'architect_last_name' => splitLastName($post['architect_name'] ?? null),
        'architect_firm' => $post['architect_firm'] ?? null,
        'architect_phone' => $post['architect_phone'] ?? null,
        'architect_cell' => $post['architect_cell'] ?? null,
        'architect_email' => $post['architect_email'] ?? null,
        
        'landscape_architect_first_name' => splitFirstName($post['landscape_architect_name'] ?? null),
        'landscape_architect_last_name' => splitLastName($post['landscape_architect_name'] ?? null),
        'landscape_architect_firm' => $post['landscape_architect_firm'] ?? null,
        'landscape_architect_phone' => $post['landscape_architect_phone'] ?? null,
        'landscape_architect_cell' => $post['landscape_architect_cell'] ?? null,
        'landscape_architect_email' => $post['landscape_architect_email'] ?? null,
        
        // Application details
        'application_type' => $post['application_type'] ?? null,
        'site_plan_request' => $post['site_plan_request'] ?? null,
        
        // Checklist items
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_verification' => isset($post['checklist_verification']) ? 1 : 0,
        'checklist_project_plans' => isset($post['checklist_project_plans']) ? 1 : 0,
        'checklist_landscape' => isset($post['checklist_landscape']) ? 1 : 0,
        'checklist_topographic' => isset($post['checklist_topographic']) ? 1 : 0,
        'checklist_traffic' => isset($post['checklist_traffic']) ? 1 : 0,
        'checklist_architectural' => isset($post['checklist_architectural']) ? 1 : 0,
        'checklist_covenants' => isset($post['checklist_covenants']) ? 1 : 0,
        'checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        
        // File uploads (using uploaded filenames with timestamp)
        'file_verification' => uploadFileWithTimestamp($files, 'file_verification', 'verification'),
        'file_project_plans' => uploadFileWithTimestamp($files, 'file_project_plans', 'project_plans'),
        'file_landscape' => uploadFileWithTimestamp($files, 'file_landscape', 'landscape'),
        'file_topographic' => uploadFileWithTimestamp($files, 'file_topographic', 'topographic'),
        'file_traffic' => uploadFileWithTimestamp($files, 'file_traffic', 'traffic'),
        'file_architectural' => uploadFileWithTimestamp($files, 'file_architectural', 'architectural'),
        'file_covenants' => uploadFileWithTimestamp($files, 'file_covenants', 'covenants'),
        
        // Signatures
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
    ];
}

/**
 * Splits full name into first name (first word)
 * 
 * @param string|null $fullName Full name string
 * @return string|null First name or null
 */
function splitFirstName(?string $fullName): ?string
{
    if (empty($fullName)) {
        return null;
    }
    
    $parts = explode(' ', trim($fullName), 2);
    return $parts[0] ?? null;
}

/**
 * Splits full name into last name (everything after first word)
 * 
 * @param string|null $fullName Full name string
 * @return string|null Last name or null
 */
function splitLastName(?string $fullName): ?string
{
    if (empty($fullName)) {
        return null;
    }
    
    $parts = explode(' ', trim($fullName), 2);
    return $parts[1] ?? '';
}

/**
 * Uploads file with timestamp prefix and returns the stored filename
 * 
 * @param array $files The $_FILES array
 * @param string $fieldName Field name in $_FILES
 * @param string $prefix Prefix for the filename
 * @return string|null Stored filename or null
 */
function uploadFileWithTimestamp(array $files, string $fieldName, string $prefix): ?string
{
    if (!isset($files[$fieldName]) || $files[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = $prefix . '_' . time() . '_' . basename($files[$fieldName]['name']);
    
    if (move_uploaded_file($files[$fieldName]['tmp_name'], $upload_dir . $filename)) {
        return $filename;
    }
    
    return null;
}

/**
 * Validates Site Development Plan Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateSiteDevelopmentPlanData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'applicant_name' => 'Applicant name',
        'signature_name_1' => 'At least one signature',
        'signature_date_1' => 'At least one signature date',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'applicant_zip_code' => 'Applicant ZIP code',
        'owner_zip_code' => 'Owner ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell',
        'attorney_phone' => 'Attorney phone',
        'attorney_cell' => 'Attorney cell',
        'surveyor_phone' => 'Surveyor phone',
        'surveyor_cell' => 'Surveyor cell',
        'engineer_phone' => 'Engineer phone',
        'engineer_cell' => 'Engineer cell',
        'architect_phone' => 'Architect phone',
        'architect_cell' => 'Architect cell',
        'landscape_architect_phone' => 'Landscape architect phone',
        'landscape_architect_cell' => 'Landscape architect cell',
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData[$field])) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate email addresses
    $emailFields = [
        'applicant_email' => 'Applicant email',
        'owner_email' => 'Owner email',
        'attorney_email' => 'Attorney email',
        'surveyor_email' => 'Surveyor email',
        'engineer_email' => 'Engineer email',
        'architect_email' => 'Architect email',
        'landscape_architect_email' => 'Landscape architect email',
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    return $errors;
}

/**
 * Inserts Site Development Plan Application
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertSiteDevelopmentPlanApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_site_development_plan_application_comprehensive(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (84 parameters)
    $types = 'siisssssssssssssssssssssssssssssssssssssssssssssssssssssiiiiiiiiiisssssss';
    
    // Extract values in order
    $params = array_values($formData);
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => null // This SP doesn't return form_id, adjust if needed
    ];
}

// APPEND THIS TO THE END OF zoning_form_functions.php

// ==================== Sign Permit Application Functions ====================

/**
 * Extracts form data for Sign Permit Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractSignPermitFormData(array $post, array $files = []): array
{
    // Parse property owner name
    $ownerParts = parseFullName($post['property_owner'] ?? '');
    
    // Parse contractor name
    $contractorParts = parseFullName($post['contractor'] ?? '');
    
    // Determine sign types based on checkboxes
    $signTypes = [];
    if (isset($post['sign_type_freestanding']) && $post['sign_type_freestanding'] == '1') {
        $signTypes[] = 'Free-Standing';
    }
    if (isset($post['sign_type_wall_mounted']) && $post['sign_type_wall_mounted'] == '1') {
        $signTypes[] = 'Wall-Mounted';
    }
    if (isset($post['sign_type_temporary']) && $post['sign_type_temporary'] == '1') {
        $signTypes[] = 'Temporary';
    }
    if (isset($post['sign_type_directional']) && $post['sign_type_directional'] == '1') {
        $signTypes[] = 'Directional';
    }
    
    return [
        // Form metadata
        'p_form_datetime_resolved' => $post['p_form_datetime_resolved'] ?? null,
        'p_form_paid_bool' => isset($post['p_form_paid_bool']) && $post['p_form_paid_bool'] == '1' ? 1 : 0,
        'p_sp_date' => $post['p_date'] ?? date('Y-m-d'),
        'p_sp_permit_number' => $post['p_permit_number'] ?? null,
        
        // Building and permit info
        'p_sp_building_coverage_percent' => $post['building_coverage'] ?? null,
        'p_sp_permit_fee' => sanitizeMoneyValue($post['total_permit_fee'] ?? null),
        
        // Property owner
        'p_sp_owner_first_name' => $ownerParts['first_name'],
        'p_sp_owner_last_name' => $ownerParts['last_name'],
        'p_sp_owner_street' => $post['property_owner_address'] ?? null,
        'p_sp_owner_city' => $post['property_owner_city'] ?? null,
        'p_sp_owner_state_code' => $post['property_owner_state_code'] ?? null,
        'p_sp_owner_zip_code' => $post['property_owner_zip_code'] ?? null,
        
        // Business
        'p_sp_business_name' => $post['business_name'] ?? null,
        'p_sp_business_street' => $post['business_address'] ?? null,
        'p_sp_business_city' => $post['business_city'] ?? null,
        'p_sp_business_state_code' => $post['business_state_code'] ?? null,
        'p_sp_business_zip_code' => $post['business_zip_code'] ?? null,
        
        // Applicant
        'p_sp_applicant_name' => $post['agent_applicant'] ?? null,
        'p_sp_applicant_street' => $post['applicant_address'] ?? null,
        'p_sp_applicant_city' => $post['applicant_city'] ?? null,
        'p_sp_applicant_state_code' => $post['applicant_state_code'] ?? null,
        'p_sp_applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        
        // Contractor
        'p_sp_contractor_first_name' => $contractorParts['first_name'],
        'p_sp_contractor_last_name' => $contractorParts['last_name'],
        'p_sp_contractor_phone_number' => sanitizePhoneNumber($post['contractor_phone'] ?? null),
        
        // Sign details
        'p_sign_type' => !empty($signTypes) ? implode(', ', $signTypes) : null,
        'p_sign_square_footage' => !empty($post['square_footage']) ? floatval($post['square_footage']) : null,
        'p_lettering_height' => $post['lettering_height'] ?? null,
        
        // Sign numbers
        'p_sign_number_freestanding' => isset($post['sign_number_freestanding']) ? (int)$post['sign_number_freestanding'] : 0,
        'p_sign_number_wall_mounted' => isset($post['sign_number_wall_mounted']) ? (int)$post['sign_number_wall_mounted'] : 0,
        'p_sign_number_temporary' => isset($post['sign_number_temporary']) ? (int)$post['sign_number_temporary'] : 0,
        'p_sign_number_directional' => isset($post['sign_number_directional']) ? (int)$post['sign_number_directional'] : 0,
        
        // Checklist items
        'p_checklist_sign_specs' => isset($post['checklist_sign_specs']) ? 1 : 0,
        'p_checklist_location_drawing' => isset($post['checklist_location_drawing']) ? 1 : 0,
        
        // File uploads
        'p_file_sign_specs' => extractUploadedFileName($files, 'file_sign_specs'),
        'p_file_location_drawing' => extractUploadedFileName($files, 'file_location_drawing'),
        'p_file_building_facia' => extractUploadedFileName($files, 'file_building_facia'),
        
        // Fee breakdown
        'p_fee_freestanding_count' => isset($post['fee_freestanding_count']) ? (int)$post['fee_freestanding_count'] : 0,
        'p_fee_wall_mounted_count' => isset($post['fee_wall_mounted_count']) ? (int)$post['fee_wall_mounted_count'] : 0,
        'p_fee_temporary_count' => isset($post['fee_temporary_count']) ? (int)$post['fee_temporary_count'] : 0,
        'p_fee_directional_count' => isset($post['fee_directional_count']) ? (int)$post['fee_directional_count'] : 0,
        
        // Signature
        'p_applicant_signature_date' => $post['applicant_signature_date'] ?? null,
    ];
}

/**
 * Validates Sign Permit Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateSignPermitData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'p_sp_business_name' => 'Business name',
        'p_sp_owner_first_name' => 'Property owner name',
        'p_sp_business_street' => 'Business street address',
        'p_sp_business_city' => 'Business city',
        'p_sp_business_state_code' => 'Business state',
        'p_sp_owner_street' => 'Property owner address',
        'p_sp_owner_city' => 'Property owner city',
        'p_sp_owner_state_code' => 'Property owner state',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'p_sp_owner_zip_code' => 'Property owner ZIP code',
        'p_sp_business_zip_code' => 'Business ZIP code',
        'p_sp_applicant_zip_code' => 'Applicant ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone number
    if (!empty($formData['p_sp_contractor_phone_number']) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData['p_sp_contractor_phone_number'])) {
        $errors[] = "Contractor phone number format is invalid";
    }
    
    return $errors;
}

/**
 * Inserts Sign Permit Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertSignPermitApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_sign_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (26 parameters)
    // s = string, d = decimal/double
    $types = 'ssssssssssssssssssssssssds';
    
    // Extract values in order matching the stored procedure
    $params = [
        $formData['p_sp_date'],
        $formData['p_sp_permit_number'],
        $formData['p_sp_building_coverage_percent'],
        $formData['p_sp_permit_fee'],
        $formData['p_sp_owner_first_name'],
        $formData['p_sp_owner_last_name'],
        $formData['p_sp_owner_street'],
        $formData['p_sp_owner_city'],
        $formData['p_sp_owner_state_code'],
        $formData['p_sp_owner_zip_code'],
        $formData['p_sp_business_name'],
        $formData['p_sp_business_street'],
        $formData['p_sp_business_city'],
        $formData['p_sp_business_state_code'],
        $formData['p_sp_business_zip_code'],
        $formData['p_sp_applicant_name'],
        $formData['p_sp_applicant_street'],
        $formData['p_sp_applicant_city'],
        $formData['p_sp_applicant_state_code'],
        $formData['p_sp_applicant_zip_code'],
        $formData['p_sp_contractor_first_name'],
        $formData['p_sp_contractor_last_name'],
        $formData['p_sp_contractor_phone_number'],
        $formData['p_sign_type'],
        $formData['p_sign_square_footage'],
        $formData['p_lettering_height'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Parse a full name into first and last name
 * 
 * @param string|null $fullName Full name string
 * @return array Array with 'first_name' and 'last_name' keys
 */
function parseFullName(?string $fullName): array
{
    $fullName = trim($fullName ?? '');
    if (empty($fullName)) {
        return ['first_name' => null, 'last_name' => null];
    }

    $parts = explode(' ', $fullName, 2);
    return [
        'first_name' => trim($parts[0] ?? ''),
        'last_name' => isset($parts[1]) ? trim($parts[1]) : null
    ];
}

/**
 * Sanitizes a money value (removes $ and commas)
 * 
 * @param string|null $value Money value string
 * @return string|null Sanitized money value or null
 */
function sanitizeMoneyValue(?string $value): ?string
{
    if (empty($value)) {
        return null;
    }
    
    // Remove dollar sign and commas
    $cleaned = str_replace(['$', ','], '', $value);
    
    return $cleaned ?: null;
}

// ==================== Open Records Request Functions ====================

/**
 * Extracts form data for Open Records Request
 * 
 * @param array $post The $_POST array
 * @return array Sanitized form data
 */
function extractOpenRecordsRequestFormData(array $post): array
{
    // Parse applicant name
    $nameParts = parseFullName($post['p_orr_applicant_name'] ?? '');
    
    return [
        // Form metadata
        'p_form_datetime_resolved' => $post['p_form_datetime_resolved'] ?? null,
        'p_form_paid_bool' => 0, // Always 0 for client submissions
        'p_correction_form_id' => null,
        
        // Request details
        'p_orr_commercial_purpose' => $post['p_orr_commercial_purpose'] ?? null,
        'p_orr_request_for_copies' => $post['p_orr_request_for_copies'] ?? null,
        'p_orr_received_on_datetime' => $post['p_orr_received_on_datetime'] ?? null,
        'p_orr_receivable_datetime' => $post['p_orr_receivable_datetime'] ?? null,
        'p_orr_denied_reasons' => $post['p_orr_denied_reasons'] ?? null,
        
        // Applicant information
        'p_orr_applicant_first_name' => $nameParts['first_name'],
        'p_orr_applicant_last_name' => $nameParts['last_name'],
        'p_orr_applicant_telephone' => sanitizePhoneNumber($post['p_orr_applicant_telephone'] ?? null),
        'p_orr_applicant_street' => $post['p_orr_applicant_street'] ?? null,
        'p_orr_applicant_city' => $post['p_orr_applicant_city'] ?? null,
        'p_orr_state_code' => $post['p_orr_state_code'] ?? null,
        'p_orr_applicant_zip_code' => $post['p_orr_applicant_zip_code'] ?? null,
        
        // Records requested
        'p_orr_records_requested' => $post['p_orr_records_requested'] ?? null,
    ];
}

/**
 * Validates Open Records Request data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateOpenRecordsRequestData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'p_orr_applicant_first_name' => 'Applicant name',
        'p_orr_records_requested' => 'Records requested description',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP code
    if (!empty($formData['p_orr_applicant_zip_code']) && !preg_match('/^\d{5}(-\d{4})?$/', $formData['p_orr_applicant_zip_code'])) {
        $errors[] = "ZIP code must be in format 12345 or 12345-6789";
    }
    
    // Validate phone number
    if (!empty($formData['p_orr_applicant_telephone']) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData['p_orr_applicant_telephone'])) {
        $errors[] = "Telephone number format is invalid";
    }
    
    // Validate commercial purpose selection
    if (!empty($formData['p_orr_commercial_purpose']) && !in_array($formData['p_orr_commercial_purpose'], ['YES', 'NO'])) {
        $errors[] = "Commercial purpose must be YES or NO";
    }
    
    // Validate request for copies selection
    if (!empty($formData['p_orr_request_for_copies']) && !in_array($formData['p_orr_request_for_copies'], ['YES', 'NO'])) {
        $errors[] = "Request for copies must be YES or NO";
    }
    
    return $errors;
}

/**
 * Inserts Open Records Request and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertOpenRecordsRequest($conn, array $formData): array
{
    $sql = "CALL sp_insert_open_records_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (13 parameters)
    $types = 'sssssssssssss';
    
    // Extract values in order matching the stored procedure
    $params = [
        $formData['p_orr_commercial_purpose'],
        $formData['p_orr_request_for_copies'],
        $formData['p_orr_received_on_datetime'],
        $formData['p_orr_receivable_datetime'],
        $formData['p_orr_denied_reasons'],
        $formData['p_orr_applicant_first_name'],
        $formData['p_orr_applicant_last_name'],
        $formData['p_orr_applicant_telephone'],
        $formData['p_orr_applicant_street'],
        $formData['p_orr_applicant_city'],
        $formData['p_orr_state_code'],
        $formData['p_orr_applicant_zip_code'],
        $formData['p_orr_records_requested'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id if available
    $form_id = null;
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $form_id = $row['form_id'] ?? null;
    }
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

// ==================== Minor Subdivision Plat Application Functions ====================

/**
 * Extracts form data for Minor Subdivision Plat Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractMinorSubdivisionPlatFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    // Parse owner name
    $ownerParts = parseFullName($post['owner_name'] ?? '');
    
    // Parse surveyor name
    $surveyorParts = parseFullName($post['surveyor_name'] ?? '');
    
    // Parse engineer name
    $engineerParts = parseFullName($post['engineer_name'] ?? '');
    
    return [
        // Technical dates
        'application_filing_date' => $post['application_filing_date'] ?? null,
        'technical_review_date' => $post['technical_review_date'] ?? null,
        'preliminary_approval_date' => $post['preliminary_approval_date'] ?? null,
        'final_approval_date' => $post['final_approval_date'] ?? null,
        
        // Primary applicant
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_street' => $post['applicant_street'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_city' => $post['applicant_city'] ?? null,
        'applicant_state' => $post['applicant_state'] ?? null,
        'applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'applicant_other_address' => $post['applicant_other_address'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'additional_applicant_other_addresses' => convertArrayToJson($post['additional_applicant_other_addresses'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner
        'owner_first_name' => $ownerParts['first_name'],
        'owner_last_name' => $ownerParts['last_name'],
        'owner_street' => $post['owner_street'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_city' => $post['owner_city'] ?? null,
        'owner_state' => $post['owner_state'] ?? null,
        'owner_zip_code' => $post['owner_zip_code'] ?? null,
        'owner_other_address' => $post['owner_other_address'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'additional_owner_other_addresses' => convertArrayToJson($post['additional_owner_other_addresses'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Surveyor
        'surveyor_id' => null, // Always create new surveyor
        'surveyor_first_name' => $surveyorParts['first_name'],
        'surveyor_last_name' => $surveyorParts['last_name'],
        'surveyor_firm' => $post['surveyor_firm'] ?? null,
        'surveyor_email' => $post['surveyor_email'] ?? null,
        'surveyor_phone' => $post['surveyor_phone'] ?? null,
        'surveyor_cell' => $post['surveyor_cell'] ?? null,
        
        // Engineer
        'engineer_id' => null, // Always create new engineer
        'engineer_first_name' => $engineerParts['first_name'],
        'engineer_last_name' => $engineerParts['last_name'],
        'engineer_firm' => $post['engineer_firm'] ?? null,
        'engineer_email' => $post['engineer_email'] ?? null,
        'engineer_phone' => $post['engineer_phone'] ?? null,
        'engineer_cell' => $post['engineer_cell'] ?? null,
        
        // Property information
        'property_street' => $post['property_address'] ?? null,
        'property_city' => null,
        'property_state' => null,
        'property_zip_code' => null,
        'property_other_address' => null,
        'parcel_number' => isset($post['parcel_number']) && $post['parcel_number'] !== '' ? (int)$post['parcel_number'] : null,
        'acreage' => $post['lot_acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        
        // Subdivision plat details
        'minspa_proposed_plot_layout' => isset($files['file_lot_layout']) && $files['file_lot_layout']['error'] === UPLOAD_ERR_OK ? 'Uploaded' : null,
        'minspa_topographic_survey' => isset($files['file_topographic']) && $files['file_topographic']['error'] === UPLOAD_ERR_OK ? 'Uploaded' : null,
        'minspa_plat_restrictions' => isset($files['file_restrictions']) && $files['file_restrictions']['error'] === UPLOAD_ERR_OK ? 'Uploaded' : null,
        'minspa_property_owner_covenants' => null,
        'minspa_association_covenants' => null,
        'minspa_master_deed' => null,
        
        // Checklist items
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_agency_signatures' => isset($post['checklist_agency_signatures']) ? 1 : 0,
        'checklist_lot_layout' => isset($post['checklist_lot_layout']) ? 1 : 0,
        'checklist_topographic' => isset($post['checklist_topographic']) ? 1 : 0,
        'checklist_restrictions' => isset($post['checklist_restrictions']) ? 1 : 0,
        'checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        
        // File uploads - store file paths
        'file_agency_signatures' => uploadFileToDirectory($files, 'file_agency_signatures', 'uploads/subdivision_agency_signatures/'),
        'file_lot_layout' => uploadFileToDirectory($files, 'file_lot_layout', 'uploads/subdivision_lot_layout/'),
        'file_topographic' => uploadFileToDirectory($files, 'file_topographic', 'uploads/subdivision_topographic/'),
        'file_restrictions' => uploadFileToDirectory($files, 'file_restrictions', 'uploads/subdivision_restrictions/'),
        
        // Signatures
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
        
        // Admin fields
        'application_fee' => $post['application_fee'] ?? null,
        'recording_fee' => $post['recording_fee'] ?? null,
        'form_paid_bool' => isset($post['form_paid_bool']) ? 1 : 0,
        'correction_form_id' => isset($post['correction_form_id']) && $post['correction_form_id'] !== '' ? (int)$post['correction_form_id'] : null,
    ];
}

/**
 * Uploads file to specified directory with unique filename
 * 
 * @param array $files The $_FILES array
 * @param string $fieldName Field name in $_FILES
 * @param string $uploadDir Upload directory path
 * @return string|null Full file path or null
 */
function uploadFileToDirectory(array $files, string $fieldName, string $uploadDir): ?string
{
    if (!isset($files[$fieldName]) || $files[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = $uploadDir . uniqid() . '_' . basename($files[$fieldName]['name']);
    
    if (move_uploaded_file($files[$fieldName]['tmp_name'], $filename)) {
        return $filename;
    }
    
    return null;
}

/**
 * Validates Minor Subdivision Plat Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateMinorSubdivisionPlatData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'applicant_name' => 'Applicant name',
        'signature_name_1' => 'At least one signature',
        'signature_date_1' => 'At least one signature date',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'applicant_zip_code' => 'Applicant ZIP code',
        'owner_zip_code' => 'Owner ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell',
        'surveyor_phone' => 'Surveyor phone',
        'surveyor_cell' => 'Surveyor cell',
        'engineer_phone' => 'Engineer phone',
        'engineer_cell' => 'Engineer cell',
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData[$field])) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate email addresses
    $emailFields = [
        'applicant_email' => 'Applicant email',
        'owner_email' => 'Owner email',
        'surveyor_email' => 'Surveyor email',
        'engineer_email' => 'Engineer email',
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    return $errors;
}

/**
 * Inserts Minor Subdivision Plat Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertMinorSubdivisionPlatApplication($conn, array $formData): array
{
    $sql = "CALL sp_insert_minor_subdivision_plat_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (81 parameters)
    $types = 'sssssssssssssssssssssssssssssssissssssssssssissssssssssssisssssssssssiiiiiii';
    
    // Build params array in correct order
    $params = [
        $formData['application_filing_date'],
        $formData['technical_review_date'],
        $formData['preliminary_approval_date'],
        $formData['final_approval_date'],
        $formData['applicant_name'],
        $formData['officers_names'],
        $formData['applicant_street'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_city'],
        $formData['applicant_state'],
        $formData['applicant_zip_code'],
        $formData['applicant_other_address'],
        $formData['applicant_email'],
        $formData['additional_applicant_names'],
        $formData['additional_applicant_officers'],
        $formData['additional_applicant_streets'],
        $formData['additional_applicant_phones'],
        $formData['additional_applicant_cells'],
        $formData['additional_applicant_cities'],
        $formData['additional_applicant_states'],
        $formData['additional_applicant_zip_codes'],
        $formData['additional_applicant_other_addresses'],
        $formData['additional_applicant_emails'],
        $formData['owner_first_name'],
        $formData['owner_last_name'],
        $formData['owner_street'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_city'],
        $formData['owner_state'],
        $formData['owner_zip_code'],
        $formData['owner_other_address'],
        $formData['owner_email'],
        $formData['additional_owner_names'],
        $formData['additional_owner_streets'],
        $formData['additional_owner_phones'],
        $formData['additional_owner_cells'],
        $formData['additional_owner_cities'],
        $formData['additional_owner_states'],
        $formData['additional_owner_zip_codes'],
        $formData['additional_owner_other_addresses'],
        $formData['additional_owner_emails'],
        $formData['surveyor_id'],
        $formData['surveyor_first_name'],
        $formData['surveyor_last_name'],
        $formData['surveyor_firm'],
        $formData['surveyor_email'],
        $formData['surveyor_phone'],
        $formData['surveyor_cell'],
        $formData['engineer_id'],
        $formData['engineer_first_name'],
        $formData['engineer_last_name'],
        $formData['engineer_firm'],
        $formData['engineer_email'],
        $formData['engineer_phone'],
        $formData['engineer_cell'],
        $formData['property_street'],
        $formData['property_city'],
        $formData['property_state'],
        $formData['property_zip_code'],
        $formData['property_other_address'],
        $formData['parcel_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        $formData['minspa_topographic_survey'],
        $formData['minspa_proposed_plot_layout'],
        $formData['minspa_plat_restrictions'],
        $formData['minspa_property_owner_covenants'],
        $formData['minspa_association_covenants'],
        $formData['minspa_master_deed'],
        $formData['checklist_application'],
        $formData['checklist_agency_signatures'],
        $formData['checklist_lot_layout'],
        $formData['checklist_topographic'],
        $formData['checklist_restrictions'],
        $formData['checklist_fees'],
        $formData['file_agency_signatures'],
        $formData['file_lot_layout'],
        $formData['file_topographic'],
        $formData['file_restrictions'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

// ==================== Major Subdivision Plat Application Functions ====================

/**
 * Extracts form data for Major Subdivision Plat Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractMajorSubdivisionPlatFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    // Parse surveyor name
    $surveyorParts = parseFullName($post['surveyor_name'] ?? '');
    
    // Parse engineer name
    $engineerParts = parseFullName($post['engineer_name'] ?? '');
    
    return [
        // Technical dates
        'application_filing_date' => $post['application_filing_date'] ?? null,
        'technical_review_date' => $post['technical_review_date'] ?? null,
        'preliminary_approval_date' => $post['preliminary_approval_date'] ?? null,
        'final_approval_date' => $post['final_approval_date'] ?? null,
        
        // Primary applicant (reusing existing fields)
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_street' => $post['applicant_street'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_city' => $post['applicant_city'] ?? null,
        'applicant_state' => $post['applicant_state'] ?? null,
        'applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'applicant_other_address' => $post['applicant_other_address'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'additional_applicant_other_addresses' => convertArrayToJson($post['additional_applicant_other_addresses'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner (uses different field names from form)
        'owner_first_name' => $post['owner_first_name'] ?? null,
        'owner_last_name' => $post['owner_last_name'] ?? null,
        'owner_street' => $post['owner_street'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_city' => $post['owner_city'] ?? null,
        'owner_state' => $post['owner_state'] ?? null,
        'owner_zip_code' => $post['owner_zip_code'] ?? null,
        'owner_other_address' => $post['owner_other_address'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'additional_owner_other_addresses' => convertArrayToJson($post['additional_owner_other_addresses'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Surveyor
        'surveyor_id' => null, // Always create new surveyor
        'surveyor_first_name' => $surveyorParts['first_name'],
        'surveyor_last_name' => $surveyorParts['last_name'],
        'surveyor_firm' => $post['surveyor_firm'] ?? null,
        'surveyor_email' => $post['surveyor_email'] ?? null,
        'surveyor_phone' => $post['surveyor_phone'] ?? null,
        'surveyor_cell' => $post['surveyor_cell'] ?? null,
        
        // Engineer
        'engineer_id' => null, // Always create new engineer
        'engineer_first_name' => $engineerParts['first_name'],
        'engineer_last_name' => $engineerParts['last_name'],
        'engineer_firm' => $post['engineer_firm'] ?? null,
        'engineer_email' => $post['engineer_email'] ?? null,
        'engineer_phone' => $post['engineer_phone'] ?? null,
        'engineer_cell' => $post['engineer_cell'] ?? null,
        
        // Property information
        'property_street' => $post['property_street'] ?? null,
        'property_city' => $post['property_city'] ?? null,
        'property_state' => $post['property_state'] ?? null,
        'property_zip_code' => $post['property_zip_code'] ?? null,
        'property_other_address' => $post['property_other_address'] ?? null,
        'parcel_number' => isset($post['parcel_number']) && $post['parcel_number'] !== '' ? (int)$post['parcel_number'] : null,
        'acreage' => $post['acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        
        // Subdivision plat details (files converted to paths)
        'mspa_topographic_survey' => uploadFileToDirectory($files, 'file_topographic', 'uploads/major_subdivision/topographic/'),
        'mspa_proposed_plot_layout' => uploadFileToDirectory($files, 'file_lot_layout', 'uploads/major_subdivision/lot_layout/'),
        'mspa_plat_restrictions' => uploadFileToDirectory($files, 'file_restrictions', 'uploads/major_subdivision/restrictions/'),
        'mspa_property_owner_convenants' => null,
        'mspa_association_covenants' => null,
        'mspa_master_deed' => null,
        'mspa_construction_plans' => uploadFileToDirectory($files, 'file_construction_plans', 'uploads/major_subdivision/construction_plans/'),
        'mspa_traffic_impact_study' => uploadFileToDirectory($files, 'file_traffic_study', 'uploads/major_subdivision/traffic_study/'),
        'mspa_geologic_study' => null,
        'mspa_drainage_plan' => uploadFileToDirectory($files, 'file_drainage', 'uploads/major_subdivision/drainage/'),
        'mspa_pavement_design' => uploadFileToDirectory($files, 'file_pavement', 'uploads/major_subdivision/pavement/'),
        'mspa_SWPPP_EPSC_plan' => uploadFileToDirectory($files, 'file_swppp', 'uploads/major_subdivision/swppp/'),
        'mspa_construction_bond_est' => uploadFileToDirectory($files, 'file_bond_estimate', 'uploads/major_subdivision/bond_estimate/'),
        
        // Checklist items (15 total for major subdivision)
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_agency_signatures' => isset($post['checklist_agency_signatures']) ? 1 : 0,
        'checklist_lot_layout' => isset($post['checklist_lot_layout']) ? 1 : 0,
        'checklist_topographic' => isset($post['checklist_topographic']) ? 1 : 0,
        'checklist_restrictions' => isset($post['checklist_restrictions']) ? 1 : 0,
        'checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        'checklist_construction_plans' => isset($post['checklist_construction_plans']) ? 1 : 0,
        'checklist_traffic_study' => isset($post['checklist_traffic_study']) ? 1 : 0,
        'checklist_drainage' => isset($post['checklist_drainage']) ? 1 : 0,
        'checklist_pavement' => isset($post['checklist_pavement']) ? 1 : 0,
        'checklist_swppp' => isset($post['checklist_swppp']) ? 1 : 0,
        'checklist_bond_estimate' => isset($post['checklist_bond_estimate']) ? 1 : 0,
        'checklist_construction_contract' => isset($post['checklist_construction_contract']) ? 1 : 0,
        'checklist_construction_bond' => isset($post['checklist_construction_bond']) ? 1 : 0,
        'checklist_notice_proceed' => isset($post['checklist_notice_proceed']) ? 1 : 0,
        
        // File uploads (12 files for major subdivision)
        'file_agency_signatures' => uploadFileToDirectory($files, 'file_agency_signatures', 'uploads/major_subdivision/agency_signatures/'),
        'file_lot_layout' => uploadFileToDirectory($files, 'file_lot_layout', 'uploads/major_subdivision/lot_layout/'),
        'file_topographic' => uploadFileToDirectory($files, 'file_topographic', 'uploads/major_subdivision/topographic/'),
        'file_restrictions' => uploadFileToDirectory($files, 'file_restrictions', 'uploads/major_subdivision/restrictions/'),
        'file_construction_plans' => uploadFileToDirectory($files, 'file_construction_plans', 'uploads/major_subdivision/construction_plans/'),
        'file_traffic_study' => uploadFileToDirectory($files, 'file_traffic_study', 'uploads/major_subdivision/traffic_study/'),
        'file_drainage' => uploadFileToDirectory($files, 'file_drainage', 'uploads/major_subdivision/drainage/'),
        'file_pavement' => uploadFileToDirectory($files, 'file_pavement', 'uploads/major_subdivision/pavement/'),
        'file_swppp' => uploadFileToDirectory($files, 'file_swppp', 'uploads/major_subdivision/swppp/'),
        'file_bond_estimate' => uploadFileToDirectory($files, 'file_bond_estimate', 'uploads/major_subdivision/bond_estimate/'),
        'file_construction_contract' => uploadFileToDirectory($files, 'file_construction_contract', 'uploads/major_subdivision/construction_contract/'),
        'file_construction_bond' => uploadFileToDirectory($files, 'file_construction_bond', 'uploads/major_subdivision/construction_bond/'),
        
        // Signatures
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
        
        // Admin fields
        'application_fee' => $post['application_fee'] ?? null,
        'recording_fee' => $post['recording_fee'] ?? null,
        'form_paid_bool' => isset($post['form_paid_bool']) ? 1 : 0,
        'correction_form_id' => isset($post['correction_form_id']) && $post['correction_form_id'] !== '' ? (int)$post['correction_form_id'] : null,
    ];
}

/**
 * Validates Major Subdivision Plat Application data
 * Note: Uses the same validation as Minor Subdivision since requirements are similar
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateMajorSubdivisionPlatData(array $formData): array
{
    // Reuse the minor subdivision validation since fields are the same
    return validateMinorSubdivisionPlatData($formData);
}

/**
 * Inserts Major Subdivision Plat Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertMajorSubdivisionPlatApplication($conn, array $formData): array
{
    // Build SQL with 105 parameters
    $sql = "CALL sp_insert_major_subdivision_plat_application(" . str_repeat("?,", 104) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (105 parameters)
    // s = string, i = integer, d = decimal/double
    $types = 'sssssssssssssssssssssssssssssssssssissssssssssssssssssssssssssssssssssiiiiiiiiiiiiiiissssssssssssssssssss';
    
    // Build params array in correct order (105 parameters)
    $params = [
        // Technical dates (4)
        $formData['application_filing_date'],
        $formData['technical_review_date'],
        $formData['preliminary_approval_date'],
        $formData['final_approval_date'],
        
        // Primary applicant (10)
        $formData['applicant_name'],
        $formData['officers_names'],
        $formData['applicant_street'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_city'],
        $formData['applicant_state'],
        $formData['applicant_zip_code'],
        $formData['applicant_other_address'],
        $formData['applicant_email'],
        
        // Additional applicants (10)
        $formData['additional_applicant_names'],
        $formData['additional_applicant_officers'],
        $formData['additional_applicant_streets'],
        $formData['additional_applicant_phones'],
        $formData['additional_applicant_cells'],
        $formData['additional_applicant_cities'],
        $formData['additional_applicant_states'],
        $formData['additional_applicant_zip_codes'],
        $formData['additional_applicant_other_addresses'],
        $formData['additional_applicant_emails'],
        
        // Property owner (10)
        $formData['owner_first_name'],
        $formData['owner_last_name'],
        $formData['owner_street'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_city'],
        $formData['owner_state'],
        $formData['owner_zip_code'],
        $formData['owner_other_address'],
        $formData['owner_email'],
        
        // Additional owners (9)
        $formData['additional_owner_names'],
        $formData['additional_owner_streets'],
        $formData['additional_owner_phones'],
        $formData['additional_owner_cells'],
        $formData['additional_owner_cities'],
        $formData['additional_owner_states'],
        $formData['additional_owner_zip_codes'],
        $formData['additional_owner_other_addresses'],
        $formData['additional_owner_emails'],
        
        // Surveyor (7)
        $formData['surveyor_id'],
        $formData['surveyor_first_name'],
        $formData['surveyor_last_name'],
        $formData['surveyor_firm'],
        $formData['surveyor_email'],
        $formData['surveyor_phone'],
        $formData['surveyor_cell'],
        
        // Engineer (7)
        $formData['engineer_id'],
        $formData['engineer_first_name'],
        $formData['engineer_last_name'],
        $formData['engineer_firm'],
        $formData['engineer_email'],
        $formData['engineer_phone'],
        $formData['engineer_cell'],
        
        // Property info (8)
        $formData['property_street'],
        $formData['property_city'],
        $formData['property_state'],
        $formData['property_zip_code'],
        $formData['property_other_address'],
        $formData['parcel_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        
        // Subdivision details (13)
        $formData['mspa_topographic_survey'],
        $formData['mspa_proposed_plot_layout'],
        $formData['mspa_plat_restrictions'],
        $formData['mspa_property_owner_convenants'],
        $formData['mspa_association_covenants'],
        $formData['mspa_master_deed'],
        $formData['mspa_construction_plans'],
        $formData['mspa_traffic_impact_study'],
        $formData['mspa_geologic_study'],
        $formData['mspa_drainage_plan'],
        $formData['mspa_pavement_design'],
        $formData['mspa_SWPPP_EPSC_plan'],
        $formData['mspa_construction_bond_est'],
        
        // Checklist (15)
        $formData['checklist_application'],
        $formData['checklist_agency_signatures'],
        $formData['checklist_lot_layout'],
        $formData['checklist_topographic'],
        $formData['checklist_restrictions'],
        $formData['checklist_fees'],
        $formData['checklist_construction_plans'],
        $formData['checklist_traffic_study'],
        $formData['checklist_drainage'],
        $formData['checklist_pavement'],
        $formData['checklist_swppp'],
        $formData['checklist_bond_estimate'],
        $formData['checklist_construction_contract'],
        $formData['checklist_construction_bond'],
        $formData['checklist_notice_proceed'],
        
        // Files (12)
        $formData['file_agency_signatures'],
        $formData['file_lot_layout'],
        $formData['file_topographic'],
        $formData['file_restrictions'],
        $formData['file_construction_plans'],
        $formData['file_traffic_study'],
        $formData['file_drainage'],
        $formData['file_pavement'],
        $formData['file_swppp'],
        $formData['file_bond_estimate'],
        $formData['file_construction_contract'],
        $formData['file_construction_bond'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

// ==================== General Development Plan Application Functions ====================
// APPEND THESE TO THE END OF zoning_form_functions.php

/**
 * Extracts form data for General Development Plan Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractGeneralDevelopmentPlanFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    // Parse attorney name
    $attorneyParts = parseFullName($post['attorney_name'] ?? '');
    
    // Parse owner name
    $ownerParts = parseFullName($post['owner_name'] ?? '');
    
    return [
        // Hearing information
        'docket_number' => $post['docket_number'] ?? null,
        'public_hearing_date' => $post['public_hearing_date'] ?? null,
        'date_application_filed' => $post['date_application_filed'] ?? null,
        'pre_application_meeting_date' => $post['pre_application_meeting_date'] ?? null,
        
        // Primary applicant
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_street' => $post['applicant_street'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_city' => $post['applicant_city'] ?? null,
        'applicant_state' => $post['applicant_state'] ?? null,
        'applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner (using parsed name from form)
        'owner_first_name' => $post['applicant_first_name'] ?? $ownerParts['first_name'],
        'owner_last_name' => $post['applicant_last_name'] ?? $ownerParts['last_name'],
        'owner_street' => $post['owner_street'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_city' => $post['owner_city'] ?? null,
        'owner_state' => $post['owner_state'] ?? null,
        'owner_zip_code' => $post['owner_zip_code'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Attorney
        'attorney_first_name' => $post['attorney_first_name'] ?? $attorneyParts['first_name'],
        'attorney_last_name' => $post['attorney_last_name'] ?? $attorneyParts['last_name'],
        'law_firm' => $post['law_firm'] ?? null,
        'attorney_phone' => $post['attorney_phone'] ?? null,
        'attorney_cell' => $post['attorney_cell'] ?? null,
        'attorney_email' => $post['attorney_email'] ?? null,
        
        // Property information
        'property_street' => $post['property_street'] ?? null,
        'property_city' => $post['property_city'] ?? null,
        'property_state' => $post['property_state'] ?? null,
        'property_zip_code' => $post['property_zip_code'] ?? null,
        'property_other_address' => $post['property_other_address'] ?? null,
        'parcel_number' => $post['parcel_number'] ?? null,
        'acreage' => $post['acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        
        // GDP details
        'gdp_amendment_request' => $post['gdp_amendment_request'] ?? null,
        'proposed_conditions' => $post['proposed_conditions'] ?? null,
        'finding_type' => $post['finding_type'] ?? null,
        'findings_explanation' => $post['findings_explanation'] ?? null,
        
        // Checklist items
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_adjacent' => isset($post['checklist_adjacent']) ? 1 : 0,
        'checklist_verification' => isset($post['checklist_verification']) ? 1 : 0,
        'checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        'checklist_gdp_conditions' => isset($post['checklist_gdp_conditions']) ? 1 : 0,
        'checklist_concept' => isset($post['checklist_concept']) ? 1 : 0,
        'checklist_traffic' => isset($post['checklist_traffic']) ? 1 : 0,
        'checklist_geologic' => isset($post['checklist_geologic']) ? 1 : 0,
        
        // Signatures
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
    ];
}

/**
 * Validates General Development Plan Application data
 * 
 * @param array $formData The form data to validate
 * @return array Array of validation errors (empty if valid)
 */
function validateGeneralDevelopmentPlanData(array $formData): array
{
    $errors = [];
    
    // Define required fields
    $requiredFields = [
        'applicant_name' => 'Applicant name',
        'signature_name_1' => 'At least one signature',
        'signature_date_1' => 'At least one signature date',
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($formData[$field])) {
            $errors[] = "{$label} is required";
        }
    }
    
    // Validate ZIP codes
    $zipFields = [
        'applicant_zip_code' => 'Applicant ZIP code',
        'owner_zip_code' => 'Owner ZIP code',
        'property_zip_code' => 'Property ZIP code',
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "{$label} must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell',
        'attorney_phone' => 'Attorney phone',
        'attorney_cell' => 'Attorney cell',
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData[$field])) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate email addresses
    $emailFields = [
        'applicant_email' => 'Applicant email',
        'owner_email' => 'Owner email',
        'attorney_email' => 'Attorney email',
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "{$label} format is invalid";
        }
    }
    
    // Validate finding type if provided
    $validFindingTypes = ['significant_change', 'physical_development', 'petition_movement'];
    if (!empty($formData['finding_type']) && !in_array($formData['finding_type'], $validFindingTypes)) {
        $errors[] = "Finding type must be one of: significant_change, physical_development, petition_movement";
    }
    
    return $errors;
}

/**
 * Inserts General Development Plan Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertGeneralDevelopmentPlanApplication($conn, array $formData): array
{
    // Build SQL with 69 parameters
    $sql = "CALL sp_insert_general_development_plan_application_comprehensive(" . str_repeat("?,", 68) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (69 parameters)
    // i = integer, d = date/datetime, s = string
    $types = 'idddsssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss';
    
    // Build params array in correct order (69 parameters)
    $params = [
        // Hearing information (4)
        $formData['docket_number'],
        $formData['public_hearing_date'],
        $formData['date_application_filed'],
        $formData['pre_application_meeting_date'],
        
        // Primary applicant (9)
        $formData['applicant_name'],
        $formData['officers_names'],
        $formData['applicant_street'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_city'],
        $formData['applicant_state'],
        $formData['applicant_zip_code'],
        $formData['applicant_email'],
        
        // Additional applicants (9)
        $formData['additional_applicant_names'],
        $formData['additional_applicant_officers'],
        $formData['additional_applicant_streets'],
        $formData['additional_applicant_phones'],
        $formData['additional_applicant_cells'],
        $formData['additional_applicant_cities'],
        $formData['additional_applicant_states'],
        $formData['additional_applicant_zip_codes'],
        $formData['additional_applicant_emails'],
        
        // Property owner (9)
        $formData['owner_first_name'],
        $formData['owner_last_name'],
        $formData['owner_street'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_city'],
        $formData['owner_state'],
        $formData['owner_zip_code'],
        $formData['owner_email'],
        
        // Additional owners (8)
        $formData['additional_owner_names'],
        $formData['additional_owner_streets'],
        $formData['additional_owner_phones'],
        $formData['additional_owner_cells'],
        $formData['additional_owner_cities'],
        $formData['additional_owner_states'],
        $formData['additional_owner_zip_codes'],
        $formData['additional_owner_emails'],
        
        // Attorney (6)
        $formData['attorney_first_name'],
        $formData['attorney_last_name'],
        $formData['law_firm'],
        $formData['attorney_phone'],
        $formData['attorney_cell'],
        $formData['attorney_email'],
        
        // Property information (8)
        $formData['property_street'],
        $formData['property_city'],
        $formData['property_state'],
        $formData['property_zip_code'],
        $formData['property_other_address'],
        $formData['parcel_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        
        // GDP details (4)
        $formData['gdp_amendment_request'],
        $formData['proposed_conditions'],
        $formData['finding_type'],
        $formData['findings_explanation'],
        
        // Checklist items (8)
        $formData['checklist_application'],
        $formData['checklist_adjacent'],
        $formData['checklist_verification'],
        $formData['checklist_fees'],
        $formData['checklist_gdp_conditions'],
        $formData['checklist_concept'],
        $formData['checklist_traffic'],
        $formData['checklist_geologic'],
        
        // Signatures (4)
        $formData['signature_date_1'],
        $formData['signature_name_1'],
        $formData['signature_date_2'],
        $formData['signature_name_2'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Future Land Use Map Application Functions
 * Add these functions to zoning_form_functions.php
 */

/**
 * Extracts form data for Future Land Use Map Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractFutureLandUseMapFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    return [
        // Hearing information (4)
        'docket_number' => $post['docket_number'] ?? null,
        'public_hearing_date' => $post['public_hearing_date'] ?? null,
        'date_application_filed' => $post['date_application_filed'] ?? null,
        'pre_application_meeting_date' => $post['pre_application_meeting_date'] ?? null,
        
        // Primary applicant (10)
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_street' => $post['applicant_street'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_city' => $post['applicant_city'] ?? null,
        'applicant_state' => $post['applicant_state'] ?? null,
        'applicant_zip_code' => $post['applicant_zip_code'] ?? null,
        'applicant_other_address' => $post['applicant_other_address'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants (10)
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_streets' => convertArrayToJson($post['additional_applicant_streets'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_cities' => convertArrayToJson($post['additional_applicant_cities'] ?? [], false),
        'additional_applicant_states' => convertArrayToJson($post['additional_applicant_states'] ?? [], false),
        'additional_applicant_zip_codes' => convertArrayToJson($post['additional_applicant_zip_codes'] ?? [], false),
        'additional_applicant_other_addresses' => convertArrayToJson($post['additional_applicant_other_addresses'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner (10)
        'owner_first_name' => $post['applicant_first_name'] ?? null,
        'owner_last_name' => $post['applicant_last_name'] ?? null,
        'owner_street' => $post['owner_street'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_city' => $post['owner_city'] ?? null,
        'owner_state' => $post['owner_state'] ?? null,
        'owner_zip_code' => $post['owner_zip_code'] ?? null,
        'owner_other_address' => $post['owner_other_address'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners (9)
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_streets' => convertArrayToJson($post['additional_owner_streets'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_cities' => convertArrayToJson($post['additional_owner_cities'] ?? [], false),
        'additional_owner_states' => convertArrayToJson($post['additional_owner_states'] ?? [], false),
        'additional_owner_zip_codes' => convertArrayToJson($post['additional_owner_zip_codes'] ?? [], false),
        'additional_owner_other_addresses' => convertArrayToJson($post['additional_owner_other_addresses'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Attorney (6)
        'attorney_first_name' => $post['attorney_first_name'] ?? null,
        'attorney_last_name' => $post['attorney_last_name'] ?? null,
        'law_firm' => $post['law_firm'] ?? null,
        'attorney_phone' => $post['attorney_phone'] ?? null,
        'attorney_cell' => $post['attorney_cell'] ?? null,
        'attorney_email' => $post['attorney_email'] ?? null,
        
        // Property information (8)
        'property_street' => $post['property_street'] ?? null,
        'property_city' => $post['property_city'] ?? null,
        'property_state' => $post['property_state'] ?? null,
        'property_zip_code' => $post['property_zip_code'] ?? null,
        'property_other_address' => $post['property_other_address'] ?? null,
        'parcel_number' => isset($post['parcel_number']) && $post['parcel_number'] !== '' 
            ? (int)$post['parcel_number'] : null,
        'acreage' => $post['acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        
        // FLUM specific details (5)
        'current_designation' => $post['current_designation'] ?? null,
        'proposed_designation' => $post['proposed_designation'] ?? null,
        'designation_reason' => $post['designation_reason'] ?? null,
        'flum_request' => $post['flum_request'] ?? null,
        'finding_type' => $post['finding_type'] ?? null,
        'findings_explanation' => $post['findings_explanation'] ?? null,
        
        // Checklist items (4)
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_exhibit' => isset($post['checklist_exhibit']) ? 1 : 0,
        'checklist_concept' => isset($post['checklist_concept']) ? 1 : 0,
        'checklist_compatibility' => isset($post['checklist_compatibility']) ? 1 : 0,
        
        // Signatures (4)
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
    ];
}

/**
 * Validates Future Land Use Map application data
 * 
 * @param array $formData Extracted form data
 * @return array Array of error messages (empty if valid)
 */
function validateFutureLandUseMapData(array $formData): array
{
    $errors = [];
    
    // Required fields
    if (empty($formData['applicant_name'])) {
        $errors[] = 'Applicant name is required';
    }
    
    // Validate at least one signature
    $hasSignature = !empty($formData['signature_name_1']) || !empty($formData['signature_name_2']);
    if (!$hasSignature) {
        $errors[] = 'At least one signature is required';
    }
    
    // Validate at least one signature date
    $hasSignatureDate = !empty($formData['signature_date_1']) || !empty($formData['signature_date_2']);
    if (!$hasSignatureDate) {
        $errors[] = 'At least one signature date is required';
    }
    
    // Validate ZIP codes if provided
    $zipFields = [
        'applicant_zip_code' => 'Applicant',
        'owner_zip_code' => 'Owner',
        'property_zip_code' => 'Property'
    ];
    
    foreach ($zipFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^\d{5}(-\d{4})?$/', $formData[$field])) {
            $errors[] = "$label ZIP code must be in format 12345 or 12345-6789";
        }
    }
    
    // Validate phone numbers if provided
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell',
        'attorney_phone' => 'Attorney phone',
        'attorney_cell' => 'Attorney cell'
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^[\d\s\-\(\)\.]+$/', $formData[$field])) {
            $errors[] = "$label format is invalid";
        }
    }
    
    // Validate email addresses if provided
    $emailFields = [
        'applicant_email' => 'Applicant',
        'owner_email' => 'Owner',
        'attorney_email' => 'Attorney'
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$label email format is invalid";
        }
    }
    
    // Validate finding type if provided
    if (!empty($formData['finding_type'])) {
        $validTypes = ['public_benefit', 'inconsistency_correction', 'clear_compatability'];
        if (!in_array($formData['finding_type'], $validTypes)) {
            $errors[] = 'Finding type must be one of: public_benefit, inconsistency_correction, clear_compatability';
        }
    }
    
    return $errors;
}

/**
 * Processes file uploads for Future Land Use Map application
 * 
 * @param array $files The $_FILES array
 * @return array Array with file contents ['file_exhibit' => content, 'file_concept' => content, 'file_compatibility' => content]
 */
function processFutureLandUseMapFileUploads(array $files): array
{
    $fileData = [
        'file_exhibit' => null,
        'file_concept' => null,
        'file_compatibility' => null
    ];
    
    $fileKeys = ['file_exhibit', 'file_concept', 'file_compatibility'];
    
    foreach ($fileKeys as $key) {
        if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
            $fileData[$key] = file_get_contents($files[$key]['tmp_name']);
        }
    }
    
    return $fileData;
}

/**
 * Inserts Future Land Use Map Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @param array $fileData File upload data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertFutureLandUseMapApplication($conn, array $formData, array $fileData = []): array
{
    // Build SQL with 63 parameters
    $sql = "CALL sp_insert_future_land_use_map_application_comprehensive(" . str_repeat("?,", 62) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (63 parameters)
    // s = string, i = integer, d = date/datetime, b = blob
    $types = 'siisssssssssssssssssssssssssssssssssssisssssssssssssssssssssssbbbs';
    
    // Build params array in correct order (63 parameters)
    $params = [
        // Hearing information (4)
        $formData['docket_number'],
        $formData['public_hearing_date'],
        $formData['date_application_filed'],
        $formData['pre_application_meeting_date'],
        
        // Primary applicant (10)
        $formData['applicant_name'],
        $formData['officers_names'],
        $formData['applicant_street'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_city'],
        $formData['applicant_state'],
        $formData['applicant_zip_code'],
        $formData['applicant_other_address'],
        $formData['applicant_email'],
        
        // Additional applicants (10)
        $formData['additional_applicant_names'],
        $formData['additional_applicant_officers'],
        $formData['additional_applicant_streets'],
        $formData['additional_applicant_phones'],
        $formData['additional_applicant_cells'],
        $formData['additional_applicant_cities'],
        $formData['additional_applicant_states'],
        $formData['additional_applicant_zip_codes'],
        $formData['additional_applicant_other_addresses'],
        $formData['additional_applicant_emails'],
        
        // Property owner (10)
        $formData['owner_first_name'],
        $formData['owner_last_name'],
        $formData['owner_street'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_city'],
        $formData['owner_state'],
        $formData['owner_zip_code'],
        $formData['owner_other_address'],
        $formData['owner_email'],
        
        // Additional owners (9)
        $formData['additional_owner_names'],
        $formData['additional_owner_streets'],
        $formData['additional_owner_phones'],
        $formData['additional_owner_cells'],
        $formData['additional_owner_cities'],
        $formData['additional_owner_states'],
        $formData['additional_owner_zip_codes'],
        $formData['additional_owner_other_addresses'],
        $formData['additional_owner_emails'],
        
        // Attorney (6)
        $formData['attorney_first_name'],
        $formData['attorney_last_name'],
        $formData['law_firm'],
        $formData['attorney_phone'],
        $formData['attorney_cell'],
        $formData['attorney_email'],
        
        // Property information (8)
        $formData['property_street'],
        $formData['property_city'],
        $formData['property_state'],
        $formData['property_zip_code'],
        $formData['property_other_address'],
        $formData['parcel_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        
        // FLUM specific details (6)
        $formData['current_designation'],
        $formData['proposed_designation'],
        $formData['designation_reason'],
        $formData['flum_request'],
        $formData['finding_type'],
        $formData['findings_explanation'],
        
        // Checklist items (4)
        $formData['checklist_application'],
        $formData['checklist_exhibit'],
        $formData['checklist_concept'],
        $formData['checklist_compatibility'],
        
        // File uploads (3 blobs)
        $fileData['file_exhibit'] ?? null,
        $fileData['file_concept'] ?? null,
        $fileData['file_compatibility'] ?? null,
        
        // Signatures (4)
        $formData['signature_date_1'],
        $formData['signature_name_1'],
        $formData['signature_date_2'],
        $formData['signature_name_2'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Extracts form data for Conditional Use Permit Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractConditionalUsePermitFormData(array $post, array $files = []): array
{
    // Extract additional applicant officers dynamically
    $additional_applicant_officers = [];
    foreach ($post as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $matches)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$matches[1]] = array_filter($value);
            }
        }
    }
    
    return [
        // Form metadata (3)
        'form_datetime_resolved' => $post['date_fees_received'] ?? null,
        'form_paid_bool' => isset($post['form_paid_bool']) ? 1 : 0,
        'correction_form_id' => isset($post['correction_form_id']) && $post['correction_form_id'] !== '' 
            ? (int)$post['correction_form_id'] : null,
        
        // Hearing information (4)
        'docket_number' => $post['docket_number'] ?? null,
        'public_hearing_date' => $post['public_hearing_date'] ?? null,
        'date_application_filed' => $post['date_application_filed'] ?? null,
        'pre_application_meeting_date' => $post['pre_application_meeting_date'] ?? null,
        
        // Primary applicant (6)
        'applicant_name' => $post['applicant_name'] ?? null,
        'officers_names' => convertArrayToJson($post['officers_names'] ?? []),
        'applicant_mailing_address' => $post['applicant_mailing_address'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Additional applicants (6)
        'additional_applicant_names' => convertArrayToJson($post['additional_applicant_names'] ?? []),
        'additional_applicant_officers' => !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null,
        'additional_applicant_mailing_addresses' => convertArrayToJson($post['additional_applicant_mailing_addresses'] ?? [], false),
        'additional_applicant_phones' => convertArrayToJson($post['additional_applicant_phones'] ?? [], false),
        'additional_applicant_cells' => convertArrayToJson($post['additional_applicant_cells'] ?? [], false),
        'additional_applicant_emails' => convertArrayToJson($post['additional_applicant_emails'] ?? [], false),
        
        // Property owner (5)
        'owner_name' => $post['owner_name'] ?? null,
        'owner_mailing_address' => $post['owner_mailing_address'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Additional owners (5)
        'additional_owner_names' => convertArrayToJson($post['additional_owner_names'] ?? []),
        'additional_owner_mailing_addresses' => convertArrayToJson($post['additional_owner_mailing_addresses'] ?? [], false),
        'additional_owner_phones' => convertArrayToJson($post['additional_owner_phones'] ?? [], false),
        'additional_owner_cells' => convertArrayToJson($post['additional_owner_cells'] ?? [], false),
        'additional_owner_emails' => convertArrayToJson($post['additional_owner_emails'] ?? [], false),
        
        // Attorney (6)
        'attorney_first_name' => $post['attorney_first_name'] ?? null,
        'attorney_last_name' => $post['attorney_last_name'] ?? null,
        'law_firm' => $post['law_firm'] ?? null,
        'attorney_phone' => $post['attorney_phone'] ?? null,
        'attorney_cell' => $post['attorney_cell'] ?? null,
        'attorney_email' => $post['attorney_email'] ?? null,
        
        // Property information (4)
        'property_address' => $post['property_address'] ?? null,
        'parcel_number' => isset($post['parcel_number']) && $post['parcel_number'] !== '' 
            ? (int)$post['parcel_number'] : null,
        'acreage' => $post['acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        
        // CUP specific details (2)
        'cup_permit_request' => $post['cup_request'] ?? null,
        'cup_proposed_conditions' => $post['proposed_conditions'] ?? null,
        
        // Checklist items (4)
        'checklist_application' => isset($post['checklist_application']) ? 1 : 0,
        'checklist_exhibit' => isset($post['checklist_exhibit']) ? 1 : 0,
        'checklist_adjacent' => isset($post['checklist_adjacent']) ? 1 : 0,
        'checklist_fees' => isset($post['checklist_fees']) ? 1 : 0,
        
        // File uploads - store filenames (2)
        'file_exhibit' => isset($files['file_exhibit']) && $files['file_exhibit']['error'] === UPLOAD_ERR_OK 
            ? $files['file_exhibit']['name'] : null,
        'file_adjacent' => isset($files['file_adjacent']) && $files['file_adjacent']['error'] === UPLOAD_ERR_OK 
            ? $files['file_adjacent']['name'] : null,
        
        // Signatures (4)
        'signature_date_1' => $post['signature_date_1'] ?? null,
        'signature_name_1' => $post['signature_name_1'] ?? null,
        'signature_date_2' => $post['signature_date_2'] ?? null,
        'signature_name_2' => $post['signature_name_2'] ?? null,
        
        // Admin/fees (3)
        'application_fee' => $post['application_fee'] ?? null,
        'certificate_fee' => $post['certificate_fee'] ?? null,
        'date_fees_received' => $post['date_fees_received'] ?? null,
    ];
}

/**
 * Validates Conditional Use Permit application data
 * 
 * @param array $formData Extracted form data
 * @return array Array of error messages (empty if valid)
 */
function validateConditionalUsePermitData(array $formData): array
{
    $errors = [];
    
    // Required fields
    if (empty($formData['applicant_name'])) {
        $errors[] = 'Applicant name is required';
    }
    
    // Validate at least one signature
    $hasSignature = !empty($formData['signature_name_1']) || !empty($formData['signature_name_2']);
    if (!$hasSignature) {
        $errors[] = 'At least one signature is required';
    }
    
    // Validate at least one signature date
    $hasSignatureDate = !empty($formData['signature_date_1']) || !empty($formData['signature_date_2']);
    if (!$hasSignatureDate) {
        $errors[] = 'At least one signature date is required';
    }
    
    // Validate phone numbers if provided
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell',
        'attorney_phone' => 'Attorney phone',
        'attorney_cell' => 'Attorney cell'
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^[\d\s\-\(\)\.]+$/', $formData[$field])) {
            $errors[] = "$label format is invalid";
        }
    }
    
    // Validate email addresses if provided
    $emailFields = [
        'applicant_email' => 'Applicant',
        'owner_email' => 'Owner',
        'attorney_email' => 'Attorney'
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$label email format is invalid";
        }
    }
    
    // Validate numeric fields
    if (!empty($formData['application_fee']) && !is_numeric($formData['application_fee'])) {
        $errors[] = 'Application fee must be a valid number';
    }
    
    if (!empty($formData['certificate_fee']) && !is_numeric($formData['certificate_fee'])) {
        $errors[] = 'Certificate fee must be a valid number';
    }
    
    return $errors;
}

/**
 * Inserts Conditional Use Permit Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertConditionalUsePermitApplication($conn, array $formData): array
{
    // Build SQL with 44 parameters
    $sql = "CALL sp_insert_conditional_use_permit_application(" . str_repeat("?,", 43) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (44 parameters)
    // i = integer, s = string, d = date/datetime
    $types = 'issssssssssssssssssssssssssssssissssisssssss';
    
    // Build params array in correct order (44 parameters)
    $params = [
        // Hearing information (4)
        $formData['docket_number'],
        $formData['public_hearing_date'],
        $formData['date_application_filed'],
        $formData['pre_application_meeting_date'],
        
        // Primary applicant (6)
        $formData['applicant_name'],
        $formData['officers_names'],
        $formData['applicant_mailing_address'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_email'],
        
        // Additional applicants (6)
        $formData['additional_applicant_names'],
        $formData['additional_applicant_officers'],
        $formData['additional_applicant_mailing_addresses'],
        $formData['additional_applicant_phones'],
        $formData['additional_applicant_cells'],
        $formData['additional_applicant_emails'],
        
        // Property owner (5)
        $formData['owner_name'],
        $formData['owner_mailing_address'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_email'],
        
        // Additional owners (5)
        $formData['additional_owner_names'],
        $formData['additional_owner_mailing_addresses'],
        $formData['additional_owner_phones'],
        $formData['additional_owner_cells'],
        $formData['additional_owner_emails'],
        
        // Attorney (6)
        $formData['attorney_first_name'],
        $formData['attorney_last_name'],
        $formData['law_firm'],
        $formData['attorney_phone'],
        $formData['attorney_cell'],
        $formData['attorney_email'],
        
        // Property information (4)
        $formData['property_address'],
        $formData['parcel_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        
        // CUP specific details (2)
        $formData['cup_permit_request'],
        $formData['cup_proposed_conditions'],
        
        // Checklist items (4)
        $formData['checklist_application'],
        $formData['checklist_exhibit'],
        $formData['checklist_adjacent'],
        $formData['checklist_fees'],
        
        // File uploads (2)
        $formData['file_exhibit'],
        $formData['file_adjacent'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Extracts form data for Administrative Appeal Request Application
 * 
 * @param array $post The $_POST array
 * @return array Sanitized form data
 */
function extractAdministrativeAppealFormData(array $post): array
{
    return [
        // Form metadata (2)
        'form_paid_bool' => 0, // Default to unpaid on submission
        'correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? (int)$post['p_correction_form_id'] : null,
        
        // Dates (2)
        'hearing_date' => $post['p_aar_hearing_date'] ?? null,
        'submit_date' => $post['p_aar_submit_date'] ?? date('Y-m-d'),
        
        // Address information (4)
        'street_address' => trim($post['p_aar_street_address'] ?? ''),
        'city_address' => trim($post['p_aar_city_address'] ?? ''),
        'state_code' => trim($post['p_state_code'] ?? ''),
        'zip_code' => trim($post['p_aar_zip_code'] ?? ''),
        
        // Appeal details (3)
        'property_location' => trim($post['p_aar_property_location'] ?? ''),
        'official_decision' => trim($post['p_aar_official_decision'] ?? ''),
        'relevant_provisions' => trim($post['p_aar_relevant_provisions'] ?? ''),
        
        // Primary appellant (2)
        'appellant_first_name' => trim($post['p_aar_appellant_first_name'] ?? ''),
        'appellant_last_name' => trim($post['p_aar_appellant_last_name'] ?? ''),
        
        // Additional appellants (1)
        'additional_appellants' => convertArrayToJson($post['appellants_names'] ?? []),
        
        // Adjacent property owner (4)
        'adjacent_property_owner_street' => trim($post['p_adjacent_property_owner_street'] ?? ''),
        'adjacent_property_owner_city' => trim($post['p_adjacent_property_owner_city'] ?? ''),
        'adjacent_property_owner_state_code' => trim($post['p_adjacent_property_owner_state_code'] ?? ''),
        'adjacent_property_owner_zip' => trim($post['p_adjacent_property_owner_zip'] ?? ''),
        
        // Primary property owner (2)
        'property_owner_first_name' => trim($post['p_aar_property_owner_first_name'] ?? ''),
        'property_owner_last_name' => trim($post['p_aar_property_owner_last_name'] ?? ''),
        
        // Additional property owners (1)
        'additional_property_owners' => convertArrayToJson($post['property_owners_names'] ?? []),
    ];
}

/**
 * Validates Administrative Appeal Request application data
 * 
 * @param array $formData Extracted form data
 * @return array Array of error messages (empty if valid)
 */
function validateAdministrativeAppealData(array $formData): array
{
    $errors = [];
    
    // Required appellant information
    if (empty($formData['appellant_first_name'])) {
        $errors[] = "Appellant's first name is required";
    }
    
    if (empty($formData['appellant_last_name'])) {
        $errors[] = "Appellant's last name is required";
    }
    
    // Required property owner information
    if (empty($formData['property_owner_first_name'])) {
        $errors[] = "Property owner's first name is required";
    }
    
    if (empty($formData['property_owner_last_name'])) {
        $errors[] = "Property owner's last name is required";
    }
    
    // Required address fields
    if (empty($formData['street_address'])) {
        $errors[] = 'Street address is required';
    }
    
    if (empty($formData['city_address'])) {
        $errors[] = 'City is required';
    }
    
    if (empty($formData['state_code'])) {
        $errors[] = 'State is required';
    }
    
    if (empty($formData['zip_code'])) {
        $errors[] = 'ZIP code is required';
    }
    
    // Validate ZIP code format if provided
    if (!empty($formData['zip_code']) && !preg_match('/^\d{5}(-\d{4})?$/', $formData['zip_code'])) {
        $errors[] = 'ZIP code must be in format 12345 or 12345-6789';
    }
    
    // Validate adjacent property owner ZIP code if provided
    if (!empty($formData['adjacent_property_owner_zip']) && !preg_match('/^\d{5}(-\d{4})?$/', $formData['adjacent_property_owner_zip'])) {
        $errors[] = 'Adjacent property owner ZIP code must be in format 12345 or 12345-6789';
    }
    
    // Required appeal details
    if (empty($formData['property_location'])) {
        $errors[] = 'Location of property is required';
    }
    
    if (empty($formData['official_decision'])) {
        $errors[] = 'Decision of official is required';
    }
    
    if (empty($formData['relevant_provisions'])) {
        $errors[] = 'Relevant provisions of zoning ordinance are required';
    }
    
    return $errors;
}

/**
 * Inserts Administrative Appeal Request Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertAdministrativeAppealApplication($conn, array $formData): array
{
    // Build SQL with 20 parameters
    $sql = "CALL sp_insert_administrative_appeal_request(" . str_repeat("?,", 19) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (20 parameters)
    // i = integer, s = string
    $types = 'issssssssssssssssss';
    
    // Build params array in correct order (20 parameters)
    $params = [
        // Form metadata (1)
        $formData['form_paid_bool'],
        
        // Dates (2)
        $formData['hearing_date'],
        $formData['submit_date'],
        
        // Address information (4)
        $formData['street_address'],
        $formData['city_address'],
        $formData['state_code'],
        $formData['zip_code'],
        
        // Appeal details (3)
        $formData['property_location'],
        $formData['official_decision'],
        $formData['relevant_provisions'],
        
        // Primary appellant (2)
        $formData['appellant_first_name'],
        $formData['appellant_last_name'],
        
        // Additional appellants (1)
        $formData['additional_appellants'],
        
        // Adjacent property owner (4)
        $formData['adjacent_property_owner_street'],
        $formData['adjacent_property_owner_city'],
        $formData['adjacent_property_owner_state_code'],
        $formData['adjacent_property_owner_zip'],
        
        // Primary property owner (2)
        $formData['property_owner_first_name'],
        $formData['property_owner_last_name'],
        
        // Additional property owners (1)
        $formData['additional_property_owners'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully!',
        'form_id' => $form_id
    ];
}

/**
 * Extracts form data for Adjacent Property Owners Form Application
 * 
 * This form has a unique structure with nested arrays:
 * - Multiple neighbor properties (outer array indexed by neighbor)
 * - Multiple owners per property (inner array indexed by owner)
 * 
 * @param array $post The $_POST array
 * @return array Sanitized form data with nested JSON structures
 */
function extractAdjacentPropertyOwnersFormData(array $post): array
{
    $num_neighbors = isset($post['num_neighbors']) ? (int)$post['num_neighbors'] : 0;
    
    // Initialize arrays for neighbor properties
    $pva_map_codes = [];
    $neighbor_property_locations = [];
    $neighbor_property_deed_books = [];
    $property_street_pg_numbers = [];
    
    // Initialize nested objects for owners (keyed by neighbor index)
    $property_owner_names = new stdClass();
    $property_owner_streets = new stdClass();
    $property_owner_cities = new stdClass();
    $property_owner_state_codes = new stdClass();
    $property_owner_zips = new stdClass();
    
    // Loop through each neighbor and collect data
    for ($i = 0; $i < $num_neighbors; $i++) {
        // Collect neighbor property data
        $pva_map_codes[] = isset($post['p_PVA_map_code'][$i]) ? $post['p_PVA_map_code'][$i] : null;
        $neighbor_property_locations[] = isset($post['p_apof_neighbor_property_location'][$i]) ? $post['p_apof_neighbor_property_location'][$i] : null;
        $neighbor_property_deed_books[] = isset($post['p_apof_neighbor_property_deed_book'][$i]) ? $post['p_apof_neighbor_property_deed_book'][$i] : null;
        $property_street_pg_numbers[] = isset($post['p_apof_property_street_pg_number'][$i]) ? $post['p_apof_property_street_pg_number'][$i] : null;
        
        // Collect owners for this neighbor
        $num_owners = isset($post['num_owners'][$i]) ? (int)$post['num_owners'][$i] : 0;
        
        $owner_names = [];
        $owner_streets = [];
        $owner_cities = [];
        $owner_states = [];
        $owner_zips = [];
        
        for ($j = 0; $j < $num_owners; $j++) {
            $owner_names[] = isset($post['p_adjacent_property_owner_name'][$i][$j]) ? $post['p_adjacent_property_owner_name'][$i][$j] : '';
            $owner_streets[] = isset($post['p_adjacent_property_owner_street'][$i][$j]) ? $post['p_adjacent_property_owner_street'][$i][$j] : '';
            $owner_cities[] = isset($post['p_adjacent_property_owner_city'][$i][$j]) ? $post['p_adjacent_property_owner_city'][$i][$j] : '';
            $owner_states[] = isset($post['p_adjacent_state_code'][$i][$j]) ? $post['p_adjacent_state_code'][$i][$j] : '';
            $owner_zips[] = isset($post['p_adjacent_property_owner_zip'][$i][$j]) ? $post['p_adjacent_property_owner_zip'][$i][$j] : '';
        }
        
        // Store owner arrays keyed by neighbor index
        $property_owner_names->{$i} = $owner_names;
        $property_owner_streets->{$i} = $owner_streets;
        $property_owner_cities->{$i} = $owner_cities;
        $property_owner_state_codes->{$i} = $owner_states;
        $property_owner_zips->{$i} = $owner_zips;
    }
    
    return [
        // Form metadata (3)
        'form_datetime_resolved' => isset($post['p_form_datetime_resolved']) && $post['p_form_datetime_resolved'] !== '' 
            ? $post['p_form_datetime_resolved'] : null,
        'form_paid_bool' => 0,
        'correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? (int)$post['p_correction_form_id'] : null,
        
        // Neighbor data (9 JSON fields)
        'pva_map_codes' => json_encode($pva_map_codes),
        'neighbor_property_locations' => json_encode($neighbor_property_locations),
        'neighbor_property_deed_books' => json_encode($neighbor_property_deed_books),
        'property_street_pg_numbers' => json_encode($property_street_pg_numbers),
        'property_owner_names' => json_encode($property_owner_names),
        'property_owner_streets' => json_encode($property_owner_streets),
        'property_owner_cities' => json_encode($property_owner_cities),
        'property_owner_state_codes' => json_encode($property_owner_state_codes),
        'property_owner_zips' => json_encode($property_owner_zips),
    ];
}

/**
 * Validates Adjacent Property Owners Form application data
 * 
 * @param array $formData Extracted form data
 * @return array Array of error messages (empty if valid)
 */
function validateAdjacentPropertyOwnersFormData(array $formData): array
{
    $errors = [];
    
    // Decode the JSON data for validation
    $pva_map_codes = json_decode($formData['pva_map_codes'], true);
    $neighbor_locations = json_decode($formData['neighbor_property_locations'], true);
    $owner_names = json_decode($formData['property_owner_names'], true);
    $owner_zips = json_decode($formData['property_owner_zips'], true);
    
    // Check if we have at least one neighbor property
    if (empty($pva_map_codes) || count($pva_map_codes) === 0) {
        $errors[] = 'At least one adjacent property is required';
        return $errors; // Return early if no properties
    }
    
    // Validate each neighbor property
    foreach ($pva_map_codes as $index => $code) {
        $propertyNum = $index + 1;
        
        // Required fields for each property
        if (empty($code)) {
            $errors[] = "PVA MAP Code is required for Adjacent Property #{$propertyNum}";
        }
        
        if (empty($neighbor_locations[$index])) {
            $errors[] = "Location of Property is required for Adjacent Property #{$propertyNum}";
        }
        
        // Validate that each property has at least one owner
        if (!isset($owner_names[$index]) || empty($owner_names[$index])) {
            $errors[] = "At least one property owner is required for Adjacent Property #{$propertyNum}";
            continue;
        }
        
        // Validate each owner for this property
        foreach ($owner_names[$index] as $ownerIndex => $ownerName) {
            $ownerNum = $ownerIndex + 1;
            
            if (empty($ownerName)) {
                $errors[] = "Owner name is required for Adjacent Property #{$propertyNum}, Owner #{$ownerNum}";
            }
            
            // Validate ZIP code format if provided
            if (isset($owner_zips[$index][$ownerIndex]) && !empty($owner_zips[$index][$ownerIndex])) {
                $zip = $owner_zips[$index][$ownerIndex];
                if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
                    $errors[] = "Invalid ZIP code format for Adjacent Property #{$propertyNum}, Owner #{$ownerNum} (must be 12345 or 12345-6789)";
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Inserts Adjacent Property Owners Form Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertAdjacentPropertyOwnersFormApplication($conn, array $formData): array
{
    // Build SQL with 9 parameters (all JSON strings)
    $sql = "CALL sp_insert_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (9 parameters, all strings containing JSON)
    $types = 'sssssssss';
    
    // Build params array in correct order (9 parameters)
    $params = [
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
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Form submitted successfully for all adjacent properties!',
        'form_id' => $form_id
    ];
}

/**
 * Extracts form data for Zoning Permit Application
 * 
 * @param array $post The $_POST array
 * @param array $files The $_FILES array
 * @return array Sanitized form data
 */
function extractZoningPermitFormData(array $post, array $files = []): array
{
    return [
        // Form metadata (3)
        'form_datetime_resolved' => isset($post['p_form_datetime_resolved']) && $post['p_form_datetime_resolved'] !== '' 
            ? $post['p_form_datetime_resolved'] : null,
        'form_paid_bool' => 0,
        'correction_form_id' => isset($post['p_correction_form_id']) && $post['p_correction_form_id'] !== '' 
            ? (int)$post['p_correction_form_id'] : null,
        
        // Application details (3)
        'application_date' => $post['application_date'] ?? null,
        'construction_start_date' => $post['construction_start_date'] ?? null,
        'permit_number' => $post['permit_number'] ?? null,
        
        // Applicant information (5)
        'applicant_name' => $post['applicant_name'] ?? null,
        'applicant_address' => $post['applicant_address'] ?? null,
        'applicant_phone' => $post['applicant_phone'] ?? null,
        'applicant_cell' => $post['applicant_cell'] ?? null,
        'applicant_email' => $post['applicant_email'] ?? null,
        
        // Property owner information (5)
        'owner_name' => $post['owner_name'] ?? null,
        'owner_address' => $post['owner_address'] ?? null,
        'owner_phone' => $post['owner_phone'] ?? null,
        'owner_cell' => $post['owner_cell'] ?? null,
        'owner_email' => $post['owner_email'] ?? null,
        
        // Professional contacts (4)
        'surveyor' => $post['surveyor'] ?? null,
        'contractor' => $post['contractor'] ?? null,
        'architect' => $post['architect'] ?? null,
        'landscape_architect' => $post['landscape_architect'] ?? null,
        
        // Property information (5)
        'property_address' => $post['property_address'] ?? null,
        'pva_number' => $post['pva_number'] ?? null,
        'acreage' => $post['acreage'] ?? null,
        'current_zoning' => $post['current_zoning'] ?? null,
        'project_type' => $post['project_type'] ?? null,
        
        // Construction information (3)
        'structure_type' => $post['structure_type'] ?? null,
        'square_feet' => $post['square_feet'] ?? null,
        'project_value' => $post['project_value'] ?? null,
        
        // File uploads (5) - Note: Files are already uploaded, we just store paths
        'project_plans_file' => null, // Will be set after upload
        'landscape_plans_file' => null,
        'verification_file' => null,
        'site_evaluation_file' => null,
        'additional_docs_file' => null,
    ];
}

/**
 * Validates Zoning Permit application data
 * 
 * @param array $formData Extracted form data
 * @return array Array of error messages (empty if valid)
 */
function validateZoningPermitData(array $formData): array
{
    $errors = [];
    
    // Required fields - basic validation
    if (empty($formData['applicant_name'])) {
        $errors[] = 'Applicant name is required';
    }
    
    if (empty($formData['property_address'])) {
        $errors[] = 'Property address is required';
    }
    
    // Validate phone numbers if provided
    $phoneFields = [
        'applicant_phone' => 'Applicant phone',
        'applicant_cell' => 'Applicant cell',
        'owner_phone' => 'Owner phone',
        'owner_cell' => 'Owner cell'
    ];
    
    foreach ($phoneFields as $field => $label) {
        if (!empty($formData[$field]) && !preg_match('/^[\d\s\-\(\)\.]+$/', $formData[$field])) {
            $errors[] = "$label format is invalid";
        }
    }
    
    // Validate email addresses if provided
    $emailFields = [
        'applicant_email' => 'Applicant',
        'owner_email' => 'Owner'
    ];
    
    foreach ($emailFields as $field => $label) {
        if (!empty($formData[$field]) && !filter_var($formData[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "$label email format is invalid";
        }
    }
    
    // Validate numeric fields if provided
    if (!empty($formData['square_feet']) && !is_numeric($formData['square_feet'])) {
        $errors[] = 'Square feet must be a valid number';
    }
    
    if (!empty($formData['project_value']) && !is_numeric(str_replace(['$', ','], '', $formData['project_value']))) {
        $errors[] = 'Project value must be a valid number';
    }
    
    // Validate project type if provided
    if (!empty($formData['project_type'])) {
        $validTypes = ['Multi-Family', 'Commercial', 'Industrial', 'Temporary Use', 'Parking/Display', 'Use Change'];
        if (!in_array($formData['project_type'], $validTypes)) {
            $errors[] = 'Invalid project type selected';
        }
    }
    
    return $errors;
}

/**
 * Handles file uploads for Zoning Permit application
 * 
 * @param array $files The $_FILES array
 * @param string $uploadDir Directory to upload files to
 * @return array Array with file paths for each upload field
 */
function processZoningPermitFileUploads(array $files, string $uploadDir = 'uploads/'): array
{
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadedFiles = [
        'project_plans_file' => null,
        'landscape_plans_file' => null,
        'verification_file' => null,
        'site_evaluation_file' => null,
        'additional_docs_file' => null,
    ];
    
    $fileFields = [
        'project_plans' => 'project_plans_file',
        'landscape_plans' => 'landscape_plans_file',
        'verification_file' => 'verification_file',
        'site_evaluation_file' => 'site_evaluation_file',
        'additional_docs' => 'additional_docs_file',
    ];
    
    foreach ($fileFields as $inputName => $outputKey) {
        if (isset($files[$inputName]) && $files[$inputName]['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($files[$inputName]['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            
            if (move_uploaded_file($files[$inputName]['tmp_name'], $targetPath)) {
                $uploadedFiles[$outputKey] = $targetPath;
            }
        }
    }
    
    return $uploadedFiles;
}

/**
 * Inserts Zoning Permit Application and returns form_id
 * 
 * @param mysqli $conn Database connection
 * @param array $formData Sanitized form data (including file paths)
 * @return array ['success' => bool, 'message' => string, 'form_id' => int|null]
 */
function insertZoningPermitApplication($conn, array $formData): array
{
    // Build SQL with 28 parameters (23 from stored procedure + 5 file paths)
    // Note: The original stored procedure has 25 params but is missing applicant_name and has a typo
    // We'll use 28 parameters to include all fields properly
    $sql = "CALL sp_insert_zoning_permit_application(" . str_repeat("?,", 27) . "?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Prepare failed: ' . $conn->error,
            'form_id' => null
        ];
    }
    
    // Build the type string (28 parameters, all strings)
    $types = 'ssssssssssssssssssssssssssss';
    
    // Build params array in correct order (28 parameters)
    $params = [
        // Application details (3)
        $formData['application_date'],
        $formData['construction_start_date'],
        $formData['permit_number'],
        
        // Applicant information (5)
        $formData['applicant_name'],
        $formData['applicant_address'],
        $formData['applicant_phone'],
        $formData['applicant_cell'],
        $formData['applicant_email'],
        
        // Property owner information (5)
        $formData['owner_name'],
        $formData['owner_address'],
        $formData['owner_phone'],
        $formData['owner_cell'],
        $formData['owner_email'],
        
        // Professional contacts (4)
        $formData['surveyor'],
        $formData['contractor'],
        $formData['architect'],
        $formData['landscape_architect'],
        
        // Property information (5)
        $formData['property_address'],
        $formData['pva_number'],
        $formData['acreage'],
        $formData['current_zoning'],
        $formData['project_type'],
        
        // Construction information (3)
        $formData['structure_type'],
        $formData['square_feet'],
        $formData['project_value'],
        
        // File uploads (5)
        $formData['project_plans_file'],
        $formData['landscape_plans_file'],
        $formData['verification_file'],
        $formData['site_evaluation_file'],
        $formData['additional_docs_file'],
    ];
    
    // Create references for bind_param
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    $bindResult = @call_user_func_array([$stmt, 'bind_param'], $bind_params);
    
    if ($bindResult === false) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Bind failed: ' . $stmt->error,
            'form_id' => null
        ];
    }
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Execute failed: ' . $error,
            'form_id' => null
        ];
    }
    
    // Get the result with form_id
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $form_id = $row['form_id'] ?? null;
    $stmt->close();
    
    // Close any remaining result sets
    while($conn->more_results()) {
        $conn->next_result();
    }
    
    return [
        'success' => true,
        'message' => 'Application submitted successfully!',
        'form_id' => $form_id
    ];
}