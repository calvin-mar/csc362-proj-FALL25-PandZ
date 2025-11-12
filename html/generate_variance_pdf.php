<?php
// ===========================================
// generate_variance_pdf.php
// ===========================================
require_once('tcpdf/tcpdf.php');

// Get form_id from URL
$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;

if ($form_id <= 0) {
    die("Invalid form ID");
}

// Database connection
$conn = new mysqli("localhost", "username", "password", "database");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from view
$sql = "SELECT * FROM vw_variance_application_details WHERE form_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("No data found for this form");
}

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Danville-Boyle County Planning');
$pdf->SetAuthor('Board of Adjustments');
$pdf->SetTitle('Variance Application');
$pdf->SetSubject('Variance Application Form');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Build the HTML content for the PDF
$html = '
<style>
    h1 { text-align: center; font-size: 14pt; font-weight: bold; margin-bottom: 5px; }
    h2 { text-align: center; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
    .section-header { font-size: 11pt; font-weight: bold; background-color: #e0e0e0; padding: 5px; margin-top: 10px; margin-bottom: 5px; }
    .field-label { font-weight: bold; font-size: 9pt; margin-top: 5px; }
    .field-value { font-size: 9pt; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 8px; }
    .small-text { font-size: 8pt; }
    table { border-collapse: collapse; width: 100%; }
    td { padding: 3px; }
</style>

<h1>DANVILLE-BOYLE COUNTY BOARD of ADJUSTMENTS</h1>
<h2>APPLICATION FOR VARIANCE</h2>

<table cellpadding="3">
    <tr>
        <td width="25%"><span class="small-text">Docket Number:</span></td>
        <td width="25%">_________________</td>
        <td width="25%"><span class="small-text">Public Hearing Date:</span></td>
        <td width="25%">_________________</td>
    </tr>
    <tr>
        <td><span class="small-text">Date Application Filed:</span></td>
        <td>_________________</td>
        <td><span class="small-text">Pre-Application Meeting Date:</span></td>
        <td>_________________</td>
    </tr>
</table>

<div class="section-header">APPLICANT(S) INFORMATION</div>

<div class="field-label">1) APPLICANT(S) NAME(S):</div>
<div class="field-value">&nbsp;</div>

<div class="field-label">Names of Officers, Directors, Shareholders or Members (If Applicable):</div>
<div class="field-value">&nbsp;</div>

<table cellpadding="2">
    <tr>
        <td width="60%"><span class="field-label">Mailing Address:</span></td>
        <td width="40%"><span class="field-label">Phone Number:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td><span class="field-label">Cell Number:</span></td>
        <td><span class="field-label">E-Mail Address:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
</table>

<div class="field-label">2) PROPERTY OWNER(S) NAME(S):</div>
<div class="field-value">&nbsp;</div>

<table cellpadding="2">
    <tr>
        <td width="60%"><span class="field-label">Mailing Address:</span></td>
        <td width="40%"><span class="field-label">Phone Number:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td><span class="field-label">Cell Number:</span></td>
        <td><span class="field-label">E-Mail Address:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
</table>

<div class="field-label">3) APPLICANT(S) ATTORNEY:</div>
<div class="field-value">&nbsp;</div>

<table cellpadding="2">
    <tr>
        <td width="60%"><span class="field-label">Name of Law Firm:</span></td>
        <td width="40%"><span class="field-label">Phone Number:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td><span class="field-label">Cell Number:</span></td>
        <td><span class="field-label">E-Mail Address:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
</table>

<div class="section-header">PROPERTY INFORMATION</div>

<div class="field-label">Property Address:</div>
<div class="field-value">' . htmlspecialchars($data['address_street'] . ', ' . $data['address_city'] . ', ' . $data['state_code'] . ' ' . $data['address_zip_code']) . '</div>

<div class="field-label">PVA Parcel Number:</div>
<div class="field-value">' . htmlspecialchars($data['PVA_parcel_number']) . '</div>

<table cellpadding="2">
    <tr>
        <td width="50%"><span class="field-label">Acreage:</span></td>
        <td width="50%"><span class="field-label">Current Zoning:</span></td>
    </tr>
    <tr>
        <td><div class="field-value">' . htmlspecialchars($data['property_acreage']) . '</div></td>
        <td><div class="field-value">' . htmlspecialchars($data['property_current_zoning']) . '</div></td>
    </tr>
</table>

<div class="section-header">VARIANCE REQUEST</div>
<div class="small-text" style="margin-bottom: 5px;">Please describe the variance(s) being requested and list the section of the Zoning Ordinance from which the variance(s) is referenced:</div>
<div class="field-value">' . nl2br(htmlspecialchars($data['va_variance_request'])) . '</div>

<div class="section-header">PROPOSED SITE CONDITIONS</div>
<div class="small-text" style="margin-bottom: 5px;">Please provide a list of all proposed conditions for the subject property:</div>
<div class="field-value">' . nl2br(htmlspecialchars($data['va_proposed_conditions'])) . '</div>
';

// Add second page for findings and checklist
$pdf->AddPage();

$html2 = '
<div class="section-header">FINDINGS REQUIRED FOR VARIANCE REQUEST</div>
<div class="small-text" style="margin-bottom: 8px;">In order for the Board of Adjustments to grant a variance, it must make findings of fact in support of its approval. Please provide a detailed explanation as to:</div>

<div class="field-label">How the requested variance(s) arises from special circumstances which do not generally apply to land in the general vicinity, or in the same zone:</div>
<div class="field-value" style="min-height: 40px;">&nbsp;</div>

<div class="field-label">How the strict application of the provisions of the regulation would deprive the applicant of the reasonable use of the land or would create an unnecessary hardship on the applicant:</div>
<div class="field-value" style="min-height: 40px;">&nbsp;</div>

<div class="field-label">How the circumstances are the result of actions of the applicant taken subsequent to the adoption of the zoning regulation from which relief is sought:</div>
<div class="field-value" style="min-height: 40px;">&nbsp;</div>

<div class="small-text" style="margin-top: 5px; font-style: italic;">The Board of Adjustments shall deny any request for a Variance arising from circumstances that are the result of willful violations of the zoning regulation by the applicant subsequent to the adoption of the zoning regulation from which relief is sought.</div>

<div class="section-header">APPLICATION CHECKLIST</div>
<div style="margin-left: 10px;">
    <div class="small-text">☐ A completed and signed Application</div>
    <div class="small-text">☐ A surveyed exhibit depicting the various portion(s) of the property to be utilized for the proposed variance, including buildings, travelways, parking areas, etc. (Please include: two (2) - 18"x 24" copies and two (2) - 11" x 17" copies)</div>
    <div class="small-text">☐ Adjacent Property Owners Form</div>
    <div class="small-text">☐ Filing and Recording Fees</div>
</div>

<div class="section-header">APPLICANT\'S CERTIFICATION</div>
<div class="small-text" style="margin-bottom: 10px;">I do hereby certify that, to the best of my knowledge and belief, all application materials have been submitted and that the information they contain is true and correct. Please attach additional signature pages if needed.</div>

<table cellpadding="5" style="margin-top: 15px;">
    <tr>
        <td width="70%"><div class="field-label">Signature of Applicant(s) and Property Owner(s):</div></td>
        <td width="30%"><div class="field-label">Date:</div></td>
    </tr>
    <tr>
        <td><div class="field-value" style="min-height: 20px;">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td colspan="2"><div class="small-text">(please print name and title)</div></td>
    </tr>
    <tr>
        <td><div class="field-value" style="min-height: 20px;">&nbsp;</div></td>
        <td><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td colspan="2"><div class="small-text">(please print name and title)</div></td>
    </tr>
</table>

<div class="small-text" style="margin-top: 10px; font-style: italic;">The foregoing signatures constitute all of the owners of the affected property necessary to convey fee title, their attorney, or their legally constituted attorney-in-fact. If the signature is of an attorney, then such signature is certification that the attorney represents each and every owner of the affected property. Please use additional signature pages, if needed.</div>

<div class="section-header">REQUIRED FILING FEES MUST BE PAID BEFORE ANY APPLICATION WILL BE ACCEPTED</div>
<table cellpadding="3">
    <tr>
        <td width="50%"><div class="field-label">Application Fee:</div><div class="field-value">&nbsp;</div></td>
        <td width="50%"><div class="field-label">Land Use Certificate Fee:</div><div class="field-value">&nbsp;</div></td>
    </tr>
    <tr>
        <td colspan="2"><div class="field-label">Date Fees Received:</div><div class="field-value">&nbsp;</div></td>
    </tr>
</table>

<div style="margin-top: 15px; text-align: center; font-size: 9pt;">
    <strong>Submit Application to:</strong><br>
    Danville-Boyle County Planning and Zoning Commission<br>
    P.O. Box 670<br>
    Danville, KY 40423-0670<br>
    859.238.1235<br>
    zoning@danvilleky.gov<br>
    www.boyleplanning.org
</div>
';

// Write the HTML content
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->writeHTML($html2, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('Variance_Application_' . $form_id . '.pdf', 'D');

$stmt->close();
$conn->close();
?>