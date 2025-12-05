<?php
// generate_form_pdf.php
// Show all errors during development (optional, good for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Dompdf Autoloader
$backonedir = dirname(getcwd());
$vendorpath = $backonedir . "/" . 'vendor/autoload.php';
require_once $vendorpath; // Adjust path if not using Composer
use Dompdf\Dompdf;
use Dompdf\Options;

// Include your config and ensure user is logged in and authorized
require_once 'config.php';
requireLogin();

// Include pdf generation functions
require_once "pdf_generation_functions.php";

if (getUserType() != 'govt_worker') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$form_id = filter_input(INPUT_GET, 'form_id', FILTER_VALIDATE_INT); // Changed to 'form_id' to match button

// Validate form_id
if ($form_id === false || $form_id === null || $form_id <= 0) {
    die('Invalid form ID provided.'); // Simple error for this script
}

// Fetch form data (similar to your view_form_details.php)
// Get basic form information using the summary view
$stmt = $conn->prepare("SELECT * FROM vw_form_summary_with_client WHERE form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();
$stmt->close();

if (!$form) {
    die('Form not found.');
}

// Initialize variables for form-specific details
$form_details = null;
$view_name = '';

// Get form-specific details based on form type using the complete views
try {
    switch ($form['form_type']) {
        case 'Administrative Appeal Request': $view_name = 'vw_administrative_appeal_complete'; break;
        case 'Variance Application': $view_name = 'vw_variance_application_complete'; break;
        case 'Zoning Verification Application': $view_name = 'vw_zoning_verification_complete'; break;
        case 'Conditional Use Permit Application': $view_name = 'vw_conditional_use_permit_complete'; break;
        case 'Zoning Map Amendment Application': $view_name = 'vw_zoning_map_amendment_complete'; break;
        case 'Major Subdivision Plat Application': $view_name = 'vw_major_subdivision_complete'; break;
        case 'Minor Subdivision Plat Application': $view_name = 'vw_minor_subdivision_complete'; break;
        case 'Development Plan Application (General)': $view_name = 'vw_general_development_plan_complete'; break;
        case 'Development Plan Application (Site)': $view_name = 'vw_site_development_plan_complete'; break;
        case 'Future Land Use Map (FLUM) Application': $view_name = 'vw_flum_application_complete'; break;
        case 'Adjacent Property Owners Form': $view_name = 'vw_adjacent_property_owners_complete'; break;
        case 'Open Records Request': $view_name = 'vw_open_records_request_complete'; break;
        case 'Sign Permit Appplication': $view_name = 'vw_sign_permit_application_complete'; break; // Note: typo in original stored procedure
        case 'Zoning Permit Application': $view_name = 'vw_zoning_permit_application_complete'; break;
    }
    
    // Query the appropriate view if one was found
    if ($view_name) {
        $stmt = $conn->prepare("SELECT * FROM {$view_name} WHERE form_id = ?");
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $form_details = $result->fetch_assoc();
        $stmt->close();
    }
} catch(Exception $e) {
    error_log("Error loading form details for PDF from view {$view_name}: " . $e->getMessage());
    // Optionally, display a user-friendly error or redirect
}

// Get department interactions using view
$interactions = [];
try {
    $stmt = $conn->prepare("
        SELECT * FROM vw_department_interactions 
        WHERE form_id = ? 
        ORDER BY department_form_interaction_id DESC
    ");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interactions[] = $row;
    }
    $stmt->close();
} catch(Exception $e) {
    error_log("Error loading interactions for PDF: " . $e->getMessage());
}

// Get corrections using view
$corrections = [];
try {
    if ($form['correction_form_id']) {
        $stmt = $conn->prepare("
            SELECT * FROM vw_correction_forms_detail 
            WHERE form_id = ? -- Assuming this view links corrections directly to the form_id
            ORDER BY correction_box_id
        ");
        $stmt->bind_param("i", $form_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $corrections[] = $row;
        }
        $stmt->close();
    }
} catch(Exception $e) {
    error_log("Error loading corrections for PDF: " . $e->getMessage());
}

$conn->close();

/**
 * Helper function to format field names for display (copy-pasted from your original file)
 */
function formatFieldName($fieldName) {
    // Remove common prefixes
    $fieldName = preg_replace('/^(va_|zvl_|mspa_|minspa_|zpa_|aar_|zva_|cupa_|zmaa_|gdpa_|sdpa_|flum_|orr_|sp_|apof_)/', '', $fieldName);
    
    // Convert underscores to spaces and capitalize words
    $name = str_replace('_', ' ', $fieldName);
    $name = ucwords($name);
    
    // Fix common abbreviations and acronyms
    $replacements = [
        'Pva' => 'PVA',
        'Id' => 'ID',
        'Datetime' => 'Date/Time',
        'Bool' => '',
        'Gdp' => 'GDP',
        'Flum' => 'FLUM',
        'Zmaa' => 'ZMAA',
        'Cupa' => 'CUP',
        'Swppp' => 'SWPPP',
        'Epsc' => 'EPSC',
        'Orr' => 'ORR',
        'Zva' => 'ZVA',
        'Apof' => 'APOF',
        'Mspa' => 'Major Subdivision',
        'Minspa' => 'Minor Subdivision',
    ];
    
    foreach ($replacements as $search => $replace) {
        $name = str_replace($search, $replace, $name);
    }
    
    return trim($name);
}

/**
 * Helper function to check if field should be displayed (copy-pasted from your original file)
 */
function shouldDisplayField($key, $value) {
    // Skip internal/system fields
    $skip_fields = [
        'form_id', 'form_type', 'form_datetime_submitted', 'form_datetime_resolved',
        'form_paid_bool', 'client_id', 'correction_form_id', 'form_status', 
        'payment_status', 'days_since_submission', 'days_to_resolve',
        'client_username', 'client_type', 'has_corrections'
    ];
    
    if (in_array($key, $skip_fields)) {
        return false;
    }
    
    // Skip ID fields
    if (preg_match('/_id$/', $key)) {
        return false;
    }
    
    // Skip null or empty values
    if ($value === null || $value === '') {
        return false;
    }
    
    return true;
}

/**
 * Helper function to determine if value is long text (copy-pasted from your original file)
 */
function isLongText($value) {
    return strlen($value) > 150 || substr_count($value, "\n") > 2;
}

// Start building the HTML content for the PDF


$html = generateFormPDF($form_id, $form['form_type'], $form_details);


 // Get the buffered HTML content

// Instantiate and use the Dompdf class
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable if you have external images or CSS
$options->set('defaultFont', 'DejaVu Sans'); // Set default font for Unicode support

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$filename = "form_details_" . $form_id . "_" . date('YmdHis') . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]); // true = download, false = open in browser
?>