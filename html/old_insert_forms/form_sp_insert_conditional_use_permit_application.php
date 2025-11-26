<?php
// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'config.php';
requireLogin();
if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
$success = '';
$error = '';

// ------------------------------
// Draft editing support (same format as example)
// ------------------------------
$draft_data = null;
$draft_id = null;

if (isset($_GET['draft_id'])) {
    $draft_id = (int)$_GET['draft_id'];
    // Fetch draft via a view (expects JSON for array fields)
    $sql = "SELECT * FROM vw_conditional_use_permit_complete WHERE form_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $draft_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $draft_data = [
            // Hearing
            'docket_number' => $row['docket_number'] ?? '',
            'public_hearing_date' => $row['public_hearing_date'] ?? null,
            'date_application_filed' => $row['date_application_filed'] ?? null,
            'pre_application_meeting_date' => $row['pre_application_meeting_date'] ?? null,
            // Applicant
            'applicant_name' => $row['applicant_name'] ?? '',
            'officers_names' => !empty($row['officers_names']) ? json_decode($row['officers_names'], true) : [],
            'applicant_mailing_address' => $row['applicant_mailing_address'] ?? '',
            'applicant_phone' => $row['applicant_phone'] ?? '',
            'applicant_cell' => $row['applicant_cell'] ?? '',
            'applicant_email' => $row['applicant_email'] ?? '',
            // Additional applicants
            'additional_applicant_names' => !empty($row['additional_applicant_names']) ? json_decode($row['additional_applicant_names'], true) : [],
            'additional_applicant_officers' => !empty($row['additional_applicant_officers']) ? json_decode($row['additional_applicant_officers'], true) : new stdClass(),
            'additional_applicant_mailing_addresses' => !empty($row['additional_applicant_mailing_addresses']) ? json_decode($row['additional_applicant_mailing_addresses'], true) : [],
            'additional_applicant_phones' => !empty($row['additional_applicant_phones']) ? json_decode($row['additional_applicant_phones'], true) : [],
            'additional_applicant_cells' => !empty($row['additional_applicant_cells']) ? json_decode($row['additional_applicant_cells'], true) : [],
            'additional_applicant_emails' => !empty($row['additional_applicant_emails']) ? json_decode($row['additional_applicant_emails'], true) : [],
            // Owners
            'owner_name' => $row['owner_name'] ?? '',
            'owner_mailing_address' => $row['owner_mailing_address'] ?? '',
            'owner_phone' => $row['owner_phone'] ?? '',
            'owner_cell' => $row['owner_cell'] ?? '',
            'owner_email' => $row['owner_email'] ?? '',
            'additional_owner_names' => !empty($row['additional_owner_names']) ? json_decode($row['additional_owner_names'], true) : [],
            'additional_owner_mailing_addresses' => !empty($row['additional_owner_mailing_addresses']) ? json_decode($row['additional_owner_mailing_addresses'], true) : [],
            'additional_owner_phones' => !empty($row['additional_owner_phones']) ? json_decode($row['additional_owner_phones'], true) : [],
            'additional_owner_cells' => !empty($row['additional_owner_cells']) ? json_decode($row['additional_owner_cells'], true) : [],
            'additional_owner_emails' => !empty($row['additional_owner_emails']) ? json_decode($row['additional_owner_emails'], true) : [],
            // Attorney
            'attorney_first_name' => $row['attorney_first_name'] ?? '',
            'attorney_last_name' => $row['attorney_last_name'] ?? '',
            'law_firm' => $row['law_firm'] ?? '',
            'attorney_phone' => $row['attorney_phone'] ?? '',
            'attorney_cell' => $row['attorney_cell'] ?? '',
            'attorney_email' => $row['attorney_email'] ?? '',
            // Property
            'property_address' => $row['property_address'] ?? '',
            'parcel_number' => $row['parcel_number'] ?? null,
            'acreage' => $row['acreage'] ?? '',
            'current_zoning' => $row['current_zoning'] ?? '',
            // CUP
            'cup_request' => $row['cup_request'] ?? '',
            'proposed_conditions' => $row['proposed_conditions'] ?? '',
            // Checklist
            'checklist_application' => $row['checklist_application'] ?? 0,
            'checklist_exhibit' => $row['checklist_exhibit'] ?? 0,
            'checklist_adjacent' => $row['checklist_adjacent'] ?? 0,
            'checklist_fees' => $row['checklist_fees'] ?? 0,
            // Files
            'file_exhibit' => $row['file_exhibit'] ?? null,
            'file_adjacent' => $row['file_adjacent'] ?? null,
            // Signatures
            'signature_date_1' => $row['signature_date_1'] ?? null,
            'signature_name_1' => $row['signature_name_1'] ?? '',
            'signature_date_2' => $row['signature_date_2'] ?? null,
            'signature_name_2' => $row['signature_name_2'] ?? '',
        ];
    } else {
        $error = "Draft not found or you don't have permission to access it.";
    }
    $stmt->close();
}

// ------------------------------
// Handle POST (save_draft or submit)
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'submit';

    // Collect dynamic arrays
    $officers_names = isset($_POST['officers_names']) && is_array($_POST['officers_names']) ? array_filter($_POST['officers_names']) : [];
    $additional_applicant_names = isset($_POST['additional_applicant_names']) && is_array($_POST['additional_applicant_names']) ? array_filter($_POST['additional_applicant_names']) : [];

    // Additional applicant officers keyed by applicant index
    $additional_applicant_officers = [];
    foreach ($_POST as $key => $value) {
        if (preg_match('/^additional_applicant_officers_(\d+)$/', $key, $m)) {
            if (is_array($value) && !empty(array_filter($value))) {
                $additional_applicant_officers[$m[1]] = array_filter($value);
            }
        }
    }

    $additional_applicant_mailing_addresses = isset($_POST['additional_applicant_mailing_addresses']) && is_array($_POST['additional_applicant_mailing_addresses']) ? $_POST['additional_applicant_mailing_addresses'] : [];
    $additional_applicant_phones = isset($_POST['additional_applicant_phones']) && is_array($_POST['additional_applicant_phones']) ? $_POST['additional_applicant_phones'] : [];
    $additional_applicant_cells = isset($_POST['additional_applicant_cells']) && is_array($_POST['additional_applicant_cells']) ? $_POST['additional_applicant_cells'] : [];
    $additional_applicant_emails = isset($_POST['additional_applicant_emails']) && is_array($_POST['additional_applicant_emails']) ? $_POST['additional_applicant_emails'] : [];

    $additional_owner_names = isset($_POST['additional_owner_names']) && is_array($_POST['additional_owner_names']) ? array_filter($_POST['additional_owner_names']) : [];
    $additional_owner_mailing_addresses = isset($_POST['additional_owner_mailing_addresses']) && is_array($_POST['additional_owner_mailing_addresses']) ? $_POST['additional_owner_mailing_addresses'] : [];
    $additional_owner_phones = isset($_POST['additional_owner_phones']) && is_array($_POST['additional_owner_phones']) ? $_POST['additional_owner_phones'] : [];
    $additional_owner_cells = isset($_POST['additional_owner_cells']) && is_array($_POST['additional_owner_cells']) ? $_POST['additional_owner_cells'] : [];
    $additional_owner_emails = isset($_POST['additional_owner_emails']) && is_array($_POST['additional_owner_emails']) ? $_POST['additional_owner_emails'] : [];

    // Scalars
    $p_docket_number = $_POST['docket_number'] ?? null;
    $p_public_hearing_date = $_POST['public_hearing_date'] ?? null;
    $p_date_application_filed = $_POST['date_application_filed'] ?? null;
    $p_preapp_meeting_date = $_POST['pre_application_meeting_date'] ?? null;

    $p_applicant_name = trim($_POST['applicant_name'] ?? '');
    $p_applicant_mailing_address = trim($_POST['applicant_mailing_address'] ?? '');
    $p_applicant_phone = trim($_POST['applicant_phone'] ?? '');
    $p_applicant_cell = trim($_POST['applicant_cell'] ?? '');
    $p_applicant_email = trim($_POST['applicant_email'] ?? '');

    $p_owner_name = trim($_POST['owner_name'] ?? '');
    $p_owner_mailing_address = trim($_POST['owner_mailing_address'] ?? '');
    $p_owner_phone = trim($_POST['owner_phone'] ?? '');
    $p_owner_cell = trim($_POST['owner_cell'] ?? '');
    $p_owner_email = trim($_POST['owner_email'] ?? '');

    $p_attorney_first_name = trim($_POST['attorney_first_name'] ?? '');
    $p_attorney_last_name = trim($_POST['attorney_last_name'] ?? '');
    $p_law_firm = trim($_POST['law_firm'] ?? '');
    $p_attorney_phone = trim($_POST['attorney_phone'] ?? '');
    $p_attorney_cell = trim($_POST['attorney_cell'] ?? '');
    $p_attorney_email = trim($_POST['attorney_email'] ?? '');

    $p_property_address = trim($_POST['property_address'] ?? '');
    $p_parcel_number = isset($_POST['parcel_number']) && $_POST['parcel_number'] !== '' ? (int)$_POST['parcel_number'] : null;
    $p_acreage = trim($_POST['acreage'] ?? '');
    $p_current_zoning = trim($_POST['current_zoning'] ?? '');

    $p_cupa_permit_request = trim($_POST['cup_request'] ?? '');
    $p_cupa_proposed_conditions = trim($_POST['proposed_conditions'] ?? '');

    $p_checklist_application = isset($_POST['checklist_application']) ? 1 : 0;
    $p_checklist_exhibit = isset($_POST['checklist_exhibit']) ? 1 : 0;
    $p_checklist_adjacent = isset($_POST['checklist_adjacent']) ? 1 : 0;
    $p_checklist_fees = isset($_POST['checklist_fees']) ? 1 : 0;

    // Files (store original filenames like original file)
    $p_file_exhibit = (isset($_FILES['file_exhibit']) && $_FILES['file_exhibit']['error'] === UPLOAD_ERR_OK) ? $_FILES['file_exhibit']['name'] : null;
    $p_file_adjacent = (isset($_FILES['file_adjacent']) && $_FILES['file_adjacent']['error'] === UPLOAD_ERR_OK) ? $_FILES['file_adjacent']['name'] : null;

    // Signatures
    $p_signature_date_1 = $_POST['signature_date_1'] ?? null;
    $p_signature_name_1 = $_POST['signature_name_1'] ?? null;
    $p_signature_date_2 = $_POST['signature_date_2'] ?? null;
    $p_signature_name_2 = $_POST['signature_name_2'] ?? null;

    // JSON fields
    $p_officers_names = !empty($officers_names) ? json_encode($officers_names) : null;
    $p_additional_applicant_names = !empty($additional_applicant_names) ? json_encode($additional_applicant_names) : null;
    $p_additional_applicant_officers = !empty($additional_applicant_officers) ? json_encode($additional_applicant_officers) : null;
    $p_additional_applicant_mailing_addresses = !empty($additional_applicant_mailing_addresses) ? json_encode($additional_applicant_mailing_addresses) : null;
    $p_additional_applicant_phones = !empty($additional_applicant_phones) ? json_encode($additional_applicant_phones) : null;
    $p_additional_applicant_cells = !empty($additional_applicant_cells) ? json_encode($additional_applicant_cells) : null;
    $p_additional_applicant_emails = !empty($additional_applicant_emails) ? json_encode($additional_applicant_emails) : null;

    $p_additional_owner_names = !empty($additional_owner_names) ? json_encode($additional_owner_names) : null;
    $p_additional_owner_mailing_addresses = !empty($additional_owner_mailing_addresses) ? json_encode($additional_owner_mailing_addresses) : null;
    $p_additional_owner_phones = !empty($additional_owner_phones) ? json_encode($additional_owner_phones) : null;
    $p_additional_owner_cells = !empty($additional_owner_cells) ? json_encode($additional_owner_cells) : null;
    $p_additional_owner_emails = !empty($additional_owner_emails) ? json_encode($additional_owner_emails) : null;

    try {
        if ($action === 'save_draft') {
            // Save draft (incomplete allowed)
            $conn->query("CALL submit_draft()");

            if ($draft_id) {
                // Update existing draft
                $sql = "CALL sp_update_conditional_use_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'issssssssssssssssssssssssssssssissssisssssss',
                    $draft_id,
                    $p_docket_number,
                    $p_public_hearing_date,
                    $p_date_application_filed,
                    $p_preapp_meeting_date,
                    $p_applicant_name,
                    $p_officers_names,
                    $p_applicant_mailing_address,
                    $p_applicant_phone,
                    $p_applicant_cell,
                    $p_applicant_email,
                    $p_additional_applicant_names,
                    $p_additional_applicant_officers,
                    $p_additional_applicant_mailing_addresses,
                    $p_additional_applicant_phones,
                    $p_additional_applicant_cells,
                    $p_additional_applicant_emails,
                    $p_owner_name,
                    $p_owner_mailing_address,
                    $p_owner_phone,
                    $p_owner_cell,
                    $p_owner_email,
                    $p_additional_owner_names,
                    $p_additional_owner_mailing_addresses,
                    $p_additional_owner_phones,
                    $p_additional_owner_cells,
                    $p_additional_owner_emails,
                    $p_attorney_first_name,
                    $p_attorney_last_name,
                    $p_law_firm,
                    $p_attorney_phone,
                    $p_attorney_cell,
                    $p_attorney_email,
                    $p_property_address,
                    $p_parcel_number,
                    $p_acreage,
                    $p_current_zoning,
                    $p_cupa_permit_request,
                    $p_cupa_proposed_conditions,
                    $p_checklist_application,
                    $p_checklist_exhibit,
                    $p_checklist_adjacent,
                    $p_checklist_fees,
                    $p_file_exhibit,
                    $p_file_adjacent
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }

                // Update timestamp like example
                $sql = "UPDATE forms SET form_datetime_submitted = NOW() WHERE form_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $draft_id);
                $stmt->execute();
                $stmt->close();

                $conn->query("CALL draft_submitted()");
                $success = "Draft updated successfully! Draft ID: {$draft_id}";
            } else {
                // Create new draft (insert)
                $sql = "CALL sp_insert_conditional_use_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'issssssssssssssssssssssssssssssissssisssssss',
                    $p_docket_number,
                    $p_public_hearing_date,
                    $p_date_application_filed,
                    $p_preapp_meeting_date,
                    $p_applicant_name,
                    $p_officers_names,
                    $p_applicant_mailing_address,
                    $p_applicant_phone,
                    $p_applicant_cell,
                    $p_applicant_email,
                    $p_additional_applicant_names,
                    $p_additional_applicant_officers,
                    $p_additional_applicant_mailing_addresses,
                    $p_additional_applicant_phones,
                    $p_additional_applicant_cells,
                    $p_additional_applicant_emails,
                    $p_owner_name,
                    $p_owner_mailing_address,
                    $p_owner_phone,
                    $p_owner_cell,
                    $p_owner_email,
                    $p_additional_owner_names,
                    $p_additional_owner_mailing_addresses,
                    $p_additional_owner_phones,
                    $p_additional_owner_cells,
                    $p_additional_owner_emails,
                    $p_attorney_first_name,
                    $p_attorney_last_name,
                    $p_law_firm,
                    $p_attorney_phone,
                    $p_attorney_cell,
                    $p_attorney_email,
                    $p_property_address,
                    $p_parcel_number,
                    $p_acreage,
                    $p_current_zoning,
                    $p_cupa_permit_request,
                    $p_cupa_proposed_conditions,
                    $p_checklist_application,
                    $p_checklist_exhibit,
                    $p_checklist_adjacent,
                    $p_checklist_fees,
                    $p_file_exhibit,
                    $p_file_adjacent
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                if (!$row || empty($row['form_id'])) { throw new Exception('Failed to retrieve form ID'); }
                $form_id = (int)$row['form_id'];
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }

                // Add to incomplete drafts
                $sql = "INSERT INTO incomplete_client_forms (form_id, client_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ii', $form_id, $client_id);
                $stmt->execute();
                $stmt->close();

                $draft_id = $form_id;
                $conn->query("CALL draft_submitted()");
                $success = "Draft saved successfully! Draft ID: {$draft_id}";
            }
        } else {
            // Final submission (basic validation similar to example)
            if (empty($p_applicant_name)) { throw new Exception("Applicant Name is required."); }
            if (empty($p_property_address)) { throw new Exception("Property Address is required."); }
            if (empty($p_cupa_permit_request)) { throw new Exception("Conditional Use Permit request description is required."); }

            if ($draft_id) {
                // Update existing draft to final
                $sql = "CALL sp_update_conditional_use_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'issssssssssssssssssssssssssssssissssisssssss',
                    $draft_id,
                    $p_docket_number,
                    $p_public_hearing_date,
                    $p_date_application_filed,
                    $p_preapp_meeting_date,
                    $p_applicant_name,
                    $p_officers_names,
                    $p_applicant_mailing_address,
                    $p_applicant_phone,
                    $p_applicant_cell,
                    $p_applicant_email,
                    $p_additional_applicant_names,
                    $p_additional_applicant_officers,
                    $p_additional_applicant_mailing_addresses,
                    $p_additional_applicant_phones,
                    $p_additional_applicant_cells,
                    $p_additional_applicant_emails,
                    $p_owner_name,
                    $p_owner_mailing_address,
                    $p_owner_phone,
                    $p_owner_cell,
                    $p_owner_email,
                    $p_additional_owner_names,
                    $p_additional_owner_mailing_addresses,
                    $p_additional_owner_phones,
                    $p_additional_owner_cells,
                    $p_additional_owner_emails,
                    $p_attorney_first_name,
                    $p_attorney_last_name,
                    $p_law_firm,
                    $p_attorney_phone,
                    $p_attorney_cell,
                    $p_attorney_email,
                    $p_property_address,
                    $p_parcel_number,
                    $p_acreage,
                    $p_current_zoning,
                    $p_cupa_permit_request,
                    $p_cupa_proposed_conditions,
                    $p_checklist_application,
                    $p_checklist_exhibit,
                    $p_checklist_adjacent,
                    $p_checklist_fees,
                    $p_file_exhibit,
                    $p_file_adjacent
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }

                // Remove from incomplete drafts
                $sql = "DELETE FROM incomplete_client_forms WHERE incomplete_form_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $draft_id);
                $stmt->execute();
                $stmt->close();

                $form_id = $draft_id;
            } else {
                // New final submission
                $sql = "CALL sp_insert_conditional_use_permit_application(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'issssssssssssssssssssssssssssssissssisssssss',
                    $p_docket_number,
                    $p_public_hearing_date,
                    $p_date_application_filed,
                    $p_preapp_meeting_date,
                    $p_applicant_name,
                    $p_officers_names,
                    $p_applicant_mailing_address,
                    $p_applicant_phone,
                    $p_applicant_cell,
                    $p_applicant_email,
                    $p_additional_applicant_names,
                    $p_additional_applicant_officers,
                    $p_additional_applicant_mailing_addresses,
                    $p_additional_applicant_phones,
                    $p_additional_applicant_cells,
                    $p_additional_applicant_emails,
                    $p_owner_name,
                    $p_owner_mailing_address,
                    $p_owner_phone,
                    $p_owner_cell,
                    $p_owner_email,
                    $p_additional_owner_names,
                    $p_additional_owner_mailing_addresses,
                    $p_additional_owner_phones,
                    $p_additional_owner_cells,
                    $p_additional_owner_emails,
                    $p_attorney_first_name,
                    $p_attorney_last_name,
                    $p_law_firm,
                    $p_attorney_phone,
                    $p_attorney_cell,
                    $p_attorney_email,
                    $p_property_address,
                    $p_parcel_number,
                    $p_acreage,
                    $p_current_zoning,
                    $p_cupa_permit_request,
                    $p_cupa_proposed_conditions,
                    $p_checklist_application,
                    $p_checklist_exhibit,
                    $p_checklist_adjacent,
                    $p_checklist_fees,
                    $p_file_exhibit,
                    $p_file_adjacent
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                if (!$row || empty($row['form_id'])) { throw new Exception('Failed to retrieve form ID'); }
                $form_id = (int)$row['form_id'];
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }
            }

            // Link final to client
            $sql = "INSERT INTO client_forms(form_id, client_id) VALUES(?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $form_id, $client_id);
            $stmt->execute();
            $stmt->close();

            $success = "Form submitted successfully! Form ID: {$form_id}";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        if ($conn->errno) { $conn->rollback(); }
    }
}

// Helper: value from draft or default
function getFieldValue($field_name, $draft_data, $default = '') {
    return $draft_data[$field_name] ?? $default;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Conditional Use Permit Application</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f5f5; font-family: Arial, sans-serif; }
        .form-container { background: white; max-width: 900px; margin: 20px auto; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .additional-entry { border: 1px solid #ddd; padding: 15px; margin: 15px 0; background: #f9f9f9; position: relative; }
        .officer-entry { border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #fff; position: relative; border-radius: 4px; }
        .remove-btn { position: absolute; top: 10px; right: 10px; }
        .add-more-btn { margin: 15px 0 20px 0; display: inline-block; }
        .form-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 25px; }
        .form-header h1 { font-size: 18px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .form-header h2 { font-size: 16px; font-weight: bold; margin: 5px 0 0 0; }
        .header-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; }
        .header-info > div { flex: 1; }
        .section-title { background: #e0e0e0; padding: 8px 12px; font-weight: bold; font-size: 14px; margin-top: 25px; margin-bottom: 15px; text-transform: uppercase; }
        .form-group label { font-weight: 600; font-size: 14px; margin-bottom: 5px; }
        .form-control, .form-control:focus { font-size: 14px; }
        .small-input { display: inline-block; width: auto; max-width: 200px; }
        .checklist-item { padding: 8px 0; border-bottom: 1px solid #eee; }
        .checklist-item:last-child { border-bottom: none; }
        .signature-line { border-bottom: 1px solid #333; min-height: 40px; margin: 10px 0; }
        .info-text { font-size: 12px; color: #666; font-style: italic; margin-top: 10px; }
        .footer-info { background: #f0f0f0; padding: 15px; margin-top: 30px; font-size: 13px; text-align: center; border: 1px solid #ddd; }
        .file-upload-section { margin-top: 10px; padding: 10px; background: #f0f8ff; border-radius: 4px; }
        .draft-badge { display: inline-block; background: #ffc107; color: #333; padding: 5px 10px; border-radius: 4px; font-size: 14px; font-weight: bold; margin-left: 10px; }
        .button-group { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    </style>
    <script>
        let applicantCount = 0;
        let ownerCount = 0;
        let officerCount = 0;
        let additionalOfficerCounters = {};
        function addOfficer(name = '') {
            officerCount++;
            const container = document.getElementById('officers-container');
            const div = document.createElement('div');
            div.className = 'officer-entry';
            div.id = 'officer-' + officerCount;
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('officer-${officerCount}')">Remove</button>
                <div class="form-group mb-2">
                    <label>Name:</label>
                    <input type="text" class="form-control" name="officers_names[]" placeholder="Full name of officer/director/shareholder/member" value="${name}">
                </div>
            `;
            container.appendChild(div);
        }
        function addAdditionalApplicantOfficer(applicantId, name = '') {
            if (!additionalOfficerCounters[applicantId]) { additionalOfficerCounters[applicantId] = 0; }
            additionalOfficerCounters[applicantId]++;
            const container = document.getElementById('additional-officers-' + applicantId);
            const div = document.createElement('div');
            div.className = 'officer-entry';
            div.id = 'additional-officer-' + applicantId + '-' + additionalOfficerCounters[applicantId];
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('additional-officer-${applicantId}-${additionalOfficerCounters[applicantId]}')">Remove</button>
                <div class="form-group mb-2">
                    <label>Name:</label>
                    <input type="text" class="form-control" name="additional_applicant_officers_${applicantId}[]" placeholder="Full name of officer/director/shareholder/member" value="${name}">
                </div>
            `;
            container.appendChild(div);
        }
        function addApplicant(data = null) {
            applicantCount++;
            const container = document.getElementById('additional-applicants');
            const div = document.createElement('div');
            div.className = 'additional-entry';
            div.id = 'applicant-' + applicantCount;
            const nameVal = data && data.name ? data.name : '';
            const mailVal = data && data.mailing ? data.mailing : '';
            const phoneVal = data && data.phone ? data.phone : '';
            const cellVal = data && data.cell ? data.cell : '';
            const emailVal = data && data.email ? data.email : '';
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('applicant-${applicantCount}')">Remove</button>
                <h6 class="mb-3"><strong>Additional Applicant ${applicantCount}</strong></h6>
                <div class="form-group">
                    <label>APPLICANT NAME:</label>
                    <input type="text" class="form-control" name="additional_applicant_names[]" value="${nameVal}">
                </div>
                <div class="form-group">
                    <label>Names of Officers, Directors, Shareholders or Members (If Applicable):</label>
                    <p class="info-text">Add each name individually below. Click "Add Another Name" to add more.</p>
                    <div id="additional-officers-${applicantCount}"></div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addAdditionalApplicantOfficer(${applicantCount})">+ Add Another Name</button>
                </div>
                <div class="form-group">
                    <label>Mailing Address:</label>
                    <input type="text" class="form-control" name="additional_applicant_mailing_addresses[]" value="${mailVal}">
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Phone Number:</label>
                            <input type="text" class="form-control" name="additional_applicant_phones[]" value="${phoneVal}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cell Number:</label>
                            <input type="text" class="form-control" name="additional_applicant_cells[]" value="${cellVal}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>E-Mail:</label>
                            <input type="email" class="form-control" name="additional_applicant_emails[]" value="${emailVal}">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(div);
            // Preload officers for this applicant if provided
            if (data && Array.isArray(data.officers)) {
                data.officers.forEach(function(nm){ addAdditionalApplicantOfficer(applicantCount, nm || ''); });
            }
        }
        function addOwner(data = null) {
            ownerCount++;
            const container = document.getElementById('additional-owners');
            const div = document.createElement('div');
            div.className = 'additional-entry';
            div.id = 'owner-' + ownerCount;
            const nameVal = data && data.name ? data.name : '';
            const mailVal = data && data.mailing ? data.mailing : '';
            const phoneVal = data && data.phone ? data.phone : '';
            const cellVal = data && data.cell ? data.cell : '';
            const emailVal = data && data.email ? data.email : '';
            div.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeElement('owner-${ownerCount}')">Remove</button>
                <h6 class="mb-3"><strong>Additional Property Owner ${ownerCount}</strong></h6>
                <div class="form-group">
                    <label>Property Owner Name(s):</label>
                    <input type="text" class="form-control" name="additional_owner_names[]" value="${nameVal}">
                </div>
                <div class="form-group">
                    <label>Mailing Address:</label>
                    <input type="text" class="form-control" name="additional_owner_mailing_addresses[]" value="${mailVal}">
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Phone Number:</label>
                            <input type="text" class="form-control" name="additional_owner_phones[]" value="${phoneVal}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cell Number:</label>
                            <input type="text" class="form-control" name="additional_owner_cells[]" value="${cellVal}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>E-Mail:</label>
                            <input type="email" class="form-control" name="additional_owner_emails[]" value="${emailVal}">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(div);
        }
        function removeElement(id) {
            const el = document.getElementById(id);
            if (el) { el.remove(); }
        }
        function submitForm(action) {
            document.getElementById('actionInput').value = action;
            document.getElementById('mainForm').submit();
        }
        // Preload draft data on page load (same pattern as example)
        window.onload = function(){
            const d = <?php echo json_encode($draft_data ?: null); ?>;
            if (!d) return;
            // Officers
            if (Array.isArray(d.officers_names)) {
                d.officers_names.forEach(function(nm){ if (nm) addOfficer(nm); });
            }
            // Additional applicants with their officers
            const addNames = Array.isArray(d.additional_applicant_names) ? d.additional_applicant_names : [];
            const addMail = Array.isArray(d.additional_applicant_mailing_addresses) ? d.additional_applicant_mailing_addresses : [];
            const addPhone = Array.isArray(d.additional_applicant_phones) ? d.additional_applicant_phones : [];
            const addCell = Array.isArray(d.additional_applicant_cells) ? d.additional_applicant_cells : [];
            const addEmail = Array.isArray(d.additional_applicant_emails) ? d.additional_applicant_emails : [];
            const addOfficers = d.additional_applicant_officers || {};
            const maxApplicants = Math.max(addNames.length, addMail.length, addPhone.length, addCell.length, addEmail.length);
            for (let i = 0; i < maxApplicants; i++) {
                const data = {
                    name: addNames[i] || '',
                    mailing: addMail[i] || '',
                    phone: addPhone[i] || '',
                    cell: addCell[i] || '',
                    email: addEmail[i] || '',
                    officers: addOfficers[i] || []
                };
                addApplicant(data);
            }
            // Additional owners
            const ownNames = Array.isArray(d.additional_owner_names) ? d.additional_owner_names : [];
            const ownMail = Array.isArray(d.additional_owner_mailing_addresses) ? d.additional_owner_mailing_addresses : [];
            const ownPhone = Array.isArray(d.additional_owner_phones) ? d.additional_owner_phones : [];
            const ownCell = Array.isArray(d.additional_owner_cells) ? d.additional_owner_cells : [];
            const ownEmail = Array.isArray(d.additional_owner_emails) ? d.additional_owner_emails : [];
            const maxOwners = Math.max(ownNames.length, ownMail.length, ownPhone.length, ownCell.length, ownEmail.length);
            for (let j = 0; j < maxOwners; j++) {
                const data = {
                    name: ownNames[j] || '',
                    mailing: ownMail[j] || '',
                    phone: ownPhone[j] || '',
                    cell: ownCell[j] || '',
                    email: ownEmail[j] || ''
                };
                addOwner(data);
            }

            // Pre-fill scalars
            document.querySelector('input[name="docket_number"]').value = d.docket_number || '';
            document.querySelector('input[name="public_hearing_date"]').value = d.public_hearing_date || '';
            document.querySelector('input[name="date_application_filed"]').value = d.date_application_filed || '';
            document.querySelector('input[name="pre_application_meeting_date"]').value = d.pre_application_meeting_date || '';

            document.querySelector('input[name="applicant_name"]').value = d.applicant_name || '';
            document.querySelector('input[name="applicant_mailing_address"]').value = d.applicant_mailing_address || '';
            document.querySelector('input[name="applicant_phone"]').value = d.applicant_phone || '';
            document.querySelector('input[name="applicant_cell"]').value = d.applicant_cell || '';
            document.querySelector('input[name="applicant_email"]').value = d.applicant_email || '';

            document.querySelector('input[name="owner_name"]').value = d.owner_name || '';
            document.querySelector('input[name="owner_mailing_address"]').value = d.owner_mailing_address || '';
            document.querySelector('input[name="owner_phone"]').value = d.owner_phone || '';
            document.querySelector('input[name="owner_cell"]').value = d.owner_cell || '';
            document.querySelector('input[name="owner_email"]').value = d.owner_email || '';

            document.querySelector('input[name="attorney_first_name"]').value = d.attorney_first_name || '';
            document.querySelector('input[name="attorney_last_name"]').value = d.attorney_last_name || '';
            document.querySelector('input[name="law_firm"]').value = d.law_firm || '';
            document.querySelector('input[name="attorney_phone"]').value = d.attorney_phone || '';
            document.querySelector('input[name="attorney_cell"]').value = d.attorney_cell || '';
            document.querySelector('input[name="attorney_email"]').value = d.attorney_email || '';

            document.querySelector('input[name="property_address"]').value = d.property_address || '';
            document.querySelector('input[name="parcel_number"]').value = d.parcel_number || '';
            document.querySelector('input[name="acreage"]').value = d.acreage || '';
            document.querySelector('input[name="current_zoning"]').value = d.current_zoning || '';

            document.querySelector('textarea[name="cup_request"]').value = d.cup_request || '';
            document.querySelector('textarea[name="proposed_conditions"]').value = d.proposed_conditions || '';

            document.getElementById('check1').checked = !!(parseInt(d.checklist_application) === 1);
            document.getElementById('check2').checked = !!(parseInt(d.checklist_exhibit) === 1);
            document.getElementById('check3').checked = !!(parseInt(d.checklist_adjacent) === 1);
            document.getElementById('check4').checked = !!(parseInt(d.checklist_fees) === 1);
        };
    </script>
</head>
<body>
<div class="form-container">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="form-header">
        <h1>Danville-Boyle County Planning & Zoning Commission</h1>
        <h2>
            Application for Conditional Use Permit
            <?php if ($draft_id): ?>
            <span class="draft-badge">EDITING DRAFT #<?php echo $draft_id; ?></span>
            <?php endif; ?>
        </h2>
    </div>
    <p><a href="client_new_form.php">← Back to form selector</a></p>
    <form method="post" enctype="multipart/form-data" id="mainForm">
        <input type="hidden" name="action" id="actionInput" value="submit">
        <?php if ($draft_id): ?>
            <input type="hidden" name="draft_id" value="<?php echo $draft_id; ?>">
        <?php endif; ?>

        <div class="header-info">
            <div>
                <strong>Docket Number:</strong>
                <input type="text" name="docket_number" class="form-control small-input d-inline" style="width: 150px;">
            </div>
            <div>
                <strong>Public Hearing Date:</strong>
                <input type="date" name="public_hearing_date" class="form-control small-input d-inline" style="width: 150px;">
            </div>
        </div>
        <div class="header-info">
            <div>
                <strong>Date Application Filed:</strong>
                <input type="date" name="date_application_filed" class="form-control small-input d-inline" style="width: 150px;">
            </div>
            <div>
                <strong>Pre-Application Meeting Date:</strong>
                <input type="date" name="pre_application_meeting_date" class="form-control small-input d-inline" style="width: 150px;">
            </div>
        </div>

        <!-- APPLICANT'S INFORMATION -->
        <div class="section-title">APPLICANT(S) INFORMATION</div>
        <div class="form-group">
            <label>1) APPLICANT NAME:</label>
            <input type="text" class="form-control" name="applicant_name">
        </div>
        <div class="form-group">
            <label>Names of Officers, Directors, Shareholders or Members (If Applicable):</label>
            <p class="info-text">Add each name individually below. Click "Add Another Name" to add more.</p>
            <div id="officers-container"></div>
            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addOfficer()">+ Add Another Name</button>
        </div>
        <div class="form-group">
            <label>Mailing Address:</label>
            <input type="text" class="form-control" name="applicant_mailing_address">
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" class="form-control" name="applicant_phone">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Cell Number:</label>
                    <input type="text" class="form-control" name="applicant_cell">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>E-Mail Address:</label>
                    <input type="email" class="form-control" name="applicant_email">
                </div>
            </div>
        </div>
        <div id="additional-applicants"></div>
        <button type="button" class="btn btn-secondary add-more-btn" onclick="addApplicant()">+ Add Another Applicant</button>

        <div class="form-group">
            <label>2) PROPERTY OWNER NAME(S):</label>
            <input type="text" class="form-control" name="owner_name">
        </div>
        <div class="form-group">
            <label>Mailing Address:</label>
            <input type="text" class="form-control" name="owner_mailing_address">
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" class="form-control" name="owner_phone">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Cell Number:</label>
                    <input type="text" class="form-control" name="owner_cell">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>E-Mail Address:</label>
                    <input type="email" class="form-control" name="owner_email">
                </div>
            </div>
        </div>
        <div id="additional-owners"></div>
        <button type="button" class="btn btn-secondary add-more-btn" onclick="addOwner()">+ Add Another Property Owner</button>
        <p class="info-text">*PLEASE ADD ADDITIONAL APPLICANTS AND PROPERTY OWNERS IF NEEDED*</p>

        <label><b>3) APPLICANT(S) ATTORNEY:</b></label>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>ATTORNEY FIRST NAME:</label>
                    <input type="text" class="form-control" name="attorney_first_name">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>ATTORNEY LAST NAME:</label>
                    <input type="text" class="form-control" name="attorney_last_name">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Name of Law Firm:</label>
            <input type="text" class="form-control" name="law_firm">
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="text" class="form-control" name="attorney_phone">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Cell Number:</label>
                    <input type="text" class="form-control" name="attorney_cell">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>E-Mail Address:</label>
                    <input type="email" class="form-control" name="attorney_email">
                </div>
            </div>
        </div>

        <!-- PROPERTY INFORMATION -->
        <div class="section-title">PROPERTY INFORMATION</div>
        <div class="form-group">
            <label>Property Address:</label>
            <input type="text" class="form-control" name="property_address">
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>PVA Parcel Number:</label>
                    <input type="number" class="form-control" name="parcel_number">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Acreage:</label>
                    <input type="text" class="form-control" name="acreage">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Current Zoning:</label>
                    <input type="text" class="form-control" name="current_zoning">
                </div>
            </div>
        </div>

        <!-- CONDITIONAL USE PERMIT REQUEST -->
        <div class="section-title">CONDITIONAL USE PERMIT REQUEST</div>
        <div class="form-group">
            <label>Please describe, in detail, the Conditional Use Permit (CUP) being requested:</label>
            <textarea class="form-control" name="cup_request" rows="4"></textarea>
        </div>

        <!-- PROPOSED CONDITIONS -->
        <div class="section-title">PROPOSED CONDITIONS</div>
        <div class="form-group">
            <label>Please provide a list of all proposed conditions for the subject property:</label>
            <textarea class="form-control" name="proposed_conditions" rows="4"></textarea>
        </div>

        <!-- FINDINGS REQUIRED -->
        <div class="section-title">FINDINGS REQUIRED FOR CONDITIONAL USE PERMIT</div>
        <p style="font-size: 13px;">In order for the Board of Adjustments to grant a conditional use permit, it must make findings of fact in support of its approval:</p>
        <ul style="font-size: 13px;">
            <li>The use is not detrimental to the public health, safety or welfare in the zone in which it is proposed;</li>
            <li>The use will not contribute toward an overburdening of municipal services;</li>
            <li>The use will not result in increased traffic congestion, additional parking problems, substantial increase in population density, environmental problems or constitute a nuisance; and</li>
            <li>That the use otherwise meets the requirements of the Zoning Ordinance.</li>
        </ul>

        <!-- APPLICATION CHECKLIST -->
        <div class="section-title">APPLICATION CHECKLIST</div>
        <div class="checklist-item">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checklist_application" id="check1">
                <label class="form-check-label" for="check1">
                    A completed and signed Application
                </label>
            </div>
        </div>
        <div class="checklist-item">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checklist_exhibit" id="check2">
                <label class="form-check-label" for="check2">
                    A surveyed exhibit depicting the various portion(s) of the property to be utilized for the proposed conditional use, including buildings, travelways, parking areas, etc. (Please include: two (2) - 18" x 24" copies and two (2) - 11" x 17" copies)
                </label>
            </div>
            <div class="file-upload-section">
                <label for="file_exhibit" class="font-weight-normal">Upload Exhibit:</label>
                <input type="file" class="form-control-file" name="file_exhibit" id="file_exhibit">
            </div>
        </div>
        <div class="checklist-item">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checklist_adjacent" id="check3">
                <label class="form-check-label" for="check3">
                    Adjacent Property Owners Form
                </label>
            </div>
            <div class="file-upload-section">
                <label for="file_adjacent" class="font-weight-normal">Upload Adjacent Property Owners Form:</label>
                <input type="file" class="form-control-file" name="file_adjacent" id="file_adjacent">
            </div>
        </div>
        <div class="checklist-item">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="checklist_fees" id="check4">
                <label class="form-check-label" for="check4">
                    Filing and Recording Fees
                </label>
            </div>
        </div>

        <!-- APPLICANT'S CERTIFICATION -->
        <div class="section-title">APPLICANT'S CERTIFICATION</div>
        <p style="font-size: 13px;">I do hereby certify that, to the best of my knowledge and belief, all application materials have been submitted and that the information they contain is true and correct. Please attach additional signature pages if needed.</p>
        <p><strong>Signature of Applicant(s) and Property Owner(s):</strong></p>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>1) Signature:</label>
                    <div class="signature-line"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" class="form-control" name="signature_date_1">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>(please print name and title)</label>
            <input type="text" class="form-control" name="signature_name_1">
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>2) Signature:</label>
                    <div class="signature-line"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" class="form-control" name="signature_date_2">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>(please print name and title)</label>
            <input type="text" class="form-control" name="signature_name_2">
        </div>
        <p class="info-text">The foregoing signatures constitute all of the owners of the affected property necessary to convey fee title, their attorney, or their legally constituted attorney-in-fact.</p>

        <!-- BUTTONS -->
        <div class="text-center mt-4 button-group">
            <button class="btn btn-warning btn-lg" type="button" onclick="submitForm('save_draft')">Save as Draft</button>
            <button class="btn btn-primary btn-lg" type="button" onclick="submitForm('submit')">Submit Application</button>
        </div>
        <p class="text-center text-muted mt-3" style="font-size: 13px;">* You can save a draft with incomplete information.</p>
    </form>
    <div class="footer-info">
        <strong>Submit Application to:</strong><br>
        Danville-Boyle County Planning and Zoning Commission<br>
        P.O. Box 670<br>
        Danville, KY 40423-0670<br>
        859.238.1235<br>
        zoning@danvilleky.gov<br>
        www.boyleplanning.org
    </div>
</div>
</body>
</html>
