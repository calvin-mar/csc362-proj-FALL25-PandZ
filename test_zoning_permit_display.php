<?php
// Test script to debug Zoning Permit display

require_once 'html/config.php';
require_once 'html/pdf_generation_functions.php';

// Create a sample form_details array matching the view structure
$form_details = [
    'form_id' => 1,
    'form_type' => 'Zoning Permit Application',
    'form_datetime_submitted' => date('Y-m-d H:i:s'),
    'form_datetime_resolved' => null,
    'form_paid_bool' => 0,
    'applicant_first_names' => 'John',
    'applicant_last_names' => 'Doe',
    'project_type' => 'Commercial Use',
    'zpa_project_plans' => 'plans.pdf',
    'zpa_preliminary_site_evaluation' => 'evaluation.pdf',
    'PVA_parcel_number' => 123456,
    'property_acreage' => 2.5,
    'property_current_zoning' => 'C-2',
    'property_street' => '123 Main St',
    'property_city' => 'Danville',
    'property_state' => 'KY',
    'property_zip' => '40422',
    'surveyor_first_name' => 'Jane',
    'surveyor_last_name' => 'Smith',
    'surveyor_firm' => 'Smith Surveying',
    'surveyor_email' => 'jane@smith.com',
    'surveyor_phone' => '859-555-1234',
    'surveyor_cell' => '859-555-5678',
    'architect_first_name' => 'Bob',
    'architect_last_name' => 'Johnson',
    'architect_firm' => 'Johnson Architects',
    'architect_email' => 'bob@johnson.com',
    'architect_phone' => '859-555-4321',
    'architect_cell' => '859-555-8765',
    'land_architect_first_name' => null,
    'land_architect_last_name' => null,
    'land_architect_firm' => null,
    'land_architect_email' => null,
    'land_architect_phone' => null,
    'land_architect_cell' => null,
    'contractor_first_name' => 'Mike',
    'contractor_last_name' => 'Builder',
    'contractor_firm' => 'Builder Construction',
    'contractor_email' => 'mike@builder.com',
    'contractor_phone' => '859-555-9999',
    'contractor_cell' => '859-555-0000'
];

// Generate the HTML
$html = generateZoningPermitHtml(1, $form_details);

// Display it
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>
