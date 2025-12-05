<?php
// ========================================
// PDF GENERATION FUNCTIONS FOR ALL FORM TYPES
// ========================================


function generateVarianceApplicationHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Variance Application - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style>
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; margin: 15mm; }
            h1 { text-align: center; font-size: 16pt; font-weight: bold; margin-bottom: 10px; }
            h2 { font-size: 12pt; margin-top: 15px; border-bottom: 1px solid #000; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            td, th { padding: 5px; border: 1px solid #000; }
            .input-box { border: 1px solid #000; min-height: 18px; padding: 3px; margin-bottom: 5px; }
            ul { margin: 0; padding-left: 20px; }
        </style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY BOARD of ADJUSTMENTS<br>APPLICATION FOR VARIANCE</h1>

        <!-- Top Section -->
        <table>
            <tr>
                <td>Docket Number:</td>
                <td><div class="input-box"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></div></td>
                <td>Public Hearing Date:</td>
                <td><div class="input-box"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></div></td>
            </tr>
            <tr>
                <td>Date Application Filed:</td>
                <td><div class="input-box"><?php echo htmlspecialchars($form_details['hearing_date_application_filed'] ?? ''); ?></div></td>
                <td>Pre-Application Meeting Date:</td>
                <td><div class="input-box"><?php echo htmlspecialchars($form_details['hearing_preapp_meeting_date'] ?? ''); ?></div></td>
            </tr>
        </table>

        <!-- Applicant(s) Information -->
        <h2>APPLICANT(S) INFORMATION</h2>
        <?php
        // Display main applicant name
        echo '<div class="input-box">' . htmlspecialchars($form_details['applicant_name'] ?? '') . '</div>';

        // Display officers list
        $officers = json_decode($form_details['officers_names'] ?? '[]', true);
        if (!empty($officers)) {
            echo '<h3>Officers / Directors:</h3><ul>';
            foreach ($officers as $officer) {
                echo '<li>' . htmlspecialchars($officer) . '</li>';
            }
            echo '</ul>';
        }

        // Display additional applicants
        $additionalApplicants = json_decode($form_details['additional_applicant_names'] ?? '[]', true);
        if (!empty($additionalApplicants)) {
            echo '<h3>Additional Applicants:</h3><ul>';
            foreach ($additionalApplicants as $applicant) {
                echo '<li>' . htmlspecialchars($applicant) . '</li>';
            }
            echo '</ul>';
        }
        ?>

        <!-- Property Owner(s) Information -->
        <h2>PROPERTY OWNER(S) INFORMATION</h2>
        <?php
        echo '<div class="input-box">' . htmlspecialchars($form_details['owner_first_name'] ?? '') . ' ' . htmlspecialchars($form_details['owner_last_name'] ?? '') . '</div>';

        // Display additional owners
        $additionalOwners = json_decode($form_details['additional_owner_names'] ?? '[]', true);
        if (!empty($additionalOwners)) {
            echo '<h3>Additional Owners:</h3><ul>';
            foreach ($additionalOwners as $owner) {
                echo '<li>' . htmlspecialchars($owner) . '</li>';
            }
            echo '</ul>';
        }
        ?>

        <!-- Variance Request -->
        <h2>VARIANCE REQUEST</h2>
        <div class="input-box" style="height:80px;"><?php echo nl2br(htmlspecialchars($form_details['va_variance_request'] ?? '')); ?></div>

        <!-- Proposed Site Conditions -->
        <h2>PROPOSED SITE CONDITIONS</h2>
        <div class="input-box" style="height:80px;"><?php echo nl2br(htmlspecialchars($form_details['va_proposed_conditions'] ?? '')); ?></div>

        <!-- Findings Explanation -->
        <h2>FINDINGS REQUIRED FOR VARIANCE REQUEST</h2>
        <div class="input-box" style="height:100px;"><?php echo nl2br(htmlspecialchars($form_details['findings_explanation'] ?? '')); ?></div>

        <!-- Checklist -->
        <h2>APPLICATION CHECKLIST</h2>
        <ul>
            <li>☐ A completed and signed Application</li>
            <li>☐ A surveyed exhibit depicting the property and proposed variance areas</li>
            <li>☐ Adjacent Property Owners Form</li>
            <li>☐ Filing and Recording Fees</li>
        </ul>

        <!-- Certification -->
        <h2>APPLICANT’S CERTIFICATION</h2>
        <p>I do hereby certify that, to the best of my knowledge and belief, all application materials have been submitted and that the information they contain is true and correct.</p>
        <p>Signature of Applicant(s) and Property Owner(s): __________________________ Date: __________________________</p>
    </body>
    </html>
    <?php
    return ob_get_clean();
}


// 2. CONDITIONAL USE PERMIT APPLICATION
function generateConditionalUsePermitHtml($form_id, $form_details) {
    ob_start(); ?>
    <html>
    <head>
        <title>Conditional Use Permit Application</title>
        <style>
            body { font-family: Arial, sans-serif; background: white; color: #333; }
            .section { margin: 30px 0; page-break-inside: avoid; }
            .section h2 { background: #e0e0e0; padding: 8px 12px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 15px; }
            .field-group { margin: 12px 0; }
            .field-label { font-weight: bold; font-size: 13px; margin-bottom: 3px; }
            .input-box { border: 1px solid #999; min-height: 20px; padding: 5px; background: #fafafa; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            table td { padding: 5px; border: 1px solid #999; }
            h1 { text-align: center; font-size: 16px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>CONDITIONAL USE PERMIT APPLICATION</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Docket Number:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></td>
                <td class="label">Public Hearing Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></td>
            </tr>
            <tr>
                <td class="label">Date Application Filed:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date_application_filed'] ?? ''); ?></td>
                <td class="label">Pre-Application Meeting Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_preapp_meeting_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S) INFORMATION</h2>
        <div class="field-label">Applicant Name(s):</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PROPERTY INFORMATION</h2>
        <div class="field-label">Property Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'property'); ?></div>
        <div class="field-label">PVA Parcel Number:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['pva_parcel_number'] ?? ''); ?></div>

        <h2>PERMIT REQUEST</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['cupa_permit_request'] ?? '')); ?></div>

        <h2>PROPOSED CONDITIONS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['cupa_proposed_conditions'] ?? '')); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 3. ZONING MAP AMENDMENT APPLICATION
function generateZoningMapAmendmentHtml($form_id, $form_details) {
    ob_start(); ?>
    <html>
    <head>
        <title>Zoning Map Amendment Application</title>
        <style>
            body { font-family: Arial, sans-serif; background: white; color: #333; }
            .section { margin: 30px 0; page-break-inside: avoid; }
            .section h2 { background: #e0e0e0; padding: 8px 12px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 15px; }
            .field-group { margin: 12px 0; }
            .field-label { font-weight: bold; font-size: 13px; margin-bottom: 3px; }
            .input-box { border: 1px solid #999; min-height: 20px; padding: 5px; background: #fafafa; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            table td { padding: 5px; border: 1px solid #999; }
            h1 { text-align: center; font-size: 16px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>APPLICATION FOR ZONING MAP AMENDMENT</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Docket Number:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></td>
                <td class="label">Public Hearing Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S) INFORMATION</h2>
        <div class="field-label">Applicant Name(s):</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PROPERTY INFORMATION</h2>
        <div class="field-label">Property Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'property'); ?></div>
        <div class="field-label">Current Zoning:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['property_current_zoning'] ?? ''); ?></div>

        <h2>ZONING MAP AMENDMENT REQUEST</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['zoning_map_amendment_request'] ?? '')); ?></div>

        <h2>PROPOSED ZONE CHANGE CONDITIONS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['zmaa_proposed_conditions'] ?? '')); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 4. ADMINISTRATIVE APPEAL REQUEST
function generateAdministrativeAppealHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Administrative Appeal - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY BOARD OF ADJUSTMENTS<br>ADMINISTRATIVE APPEAL REQUEST</h1>

        <div class="field-label">Hearing Date:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['aar_hearing_date'] ?? ''); ?></div>

        <div class="field-label">Submit Date:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['aar_submit_date'] ?? ''); ?></div>

        <h2>APPELLANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['appellants'] ?? ''); ?></div>

        <h2>PROPERTY OWNERS</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['property_owners'] ?? ''); ?></div>

        <h2>PROPERTY ADDRESS</h2>
        <div class="input-box"><?php echo formatAddress($form_details, 'address'); ?></div>

        <h2>OFFICIAL DECISION</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['aar_official_decision'] ?? '')); ?></div>

        <h2>RELEVANT PROVISIONS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['aar_relevant_provisions'] ?? '')); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 5. ZONING VERIFICATION LETTER
function generateZoningVerificationHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Zoning Verification Letter - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>ZONING VERIFICATION LETTER<br>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION</h1>

        <h2>PROPERTY OWNER</h2>
        <div class="field-label">Name:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['zva_owner_first_name'] ?? '') . ' ' . ($form_details['zva_owner_last_name'] ?? '')); ?></div>
        <div class="field-label">Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'owner'); ?></div>

        <h2>APPLICANT</h2>
        <div class="field-label">Name:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['zva_applicant_first_name'] ?? '') . ' ' . ($form_details['zva_applicant_last_name'] ?? '')); ?></div>
        <div class="field-label">Phone:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['zva_applicant_phone_number'] ?? ''); ?></div>
        <div class="field-label">Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'applicant'); ?></div>

        <h2>PHYSICAL ADDRESS OF PROPERTY</h2>
        <div class="input-box"><?php echo formatAddress($form_details, 'property_address'); ?></div>

        <h2>LETTER CONTENT</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['zva_letter_content'] ?? '')); ?></div>

        <div class="fee-section">
            <p class="important-note">Fee: $20.00</p>
        </div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 6. SIGN PERMIT APPLICATION
function generateSignPermitHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Sign Permit - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>SIGN PERMIT</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['sp_date'] ?? ''); ?></td>
                <td class="label">Permit #:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['sp_permit_number'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>OWNER INFORMATION</h2>
        <div class="field-label">Business Name:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['sp_business_name'] ?? ''); ?></div>
        <div class="field-label">Business Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'sp_business'); ?></div>

        <div class="field-label">Property Owner:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['sp_owner_first_name'] ?? '') . ' ' . ($form_details['sp_owner_last_name'] ?? '')); ?></div>
        <div class="field-label">Owner Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'sp_owner'); ?></div>

        <h2>CONTRACTOR</h2>
        <div class="field-label">Name:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['sp_contractor_first_name'] ?? '') . ' ' . ($form_details['sp_contractor_last_name'] ?? '')); ?></div>
        <div class="field-label">Phone:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['sp_contractor_phone_number'] ?? ''); ?></div>

        <h2>SIGN INFORMATION</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['signs'] ?? ''); ?></div>

        <div class="field-label">Building Coverage:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['sp_building_coverage_percent'] ?? ''); ?>%</div>

        <div class="fee-section">
            <p><strong>Permit Fee:</strong> <?php echo htmlspecialchars($form_details['sp_permit_fee'] ?? ''); ?></p>
        </div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 7. MAJOR SUBDIVISION PLAT APPLICATION
function generateMajorSubdivisionHtml($form_id, $form_details) {
    ob_start();
    ?>
    <html>
    <head>
        <title>Major Subdivision Plat Application</title>
        <style>
            body { font-family: Arial, sans-serif; background: white; color: #333; }
            .section { margin: 30px 0; page-break-inside: avoid; }
            .section h2 { background: #e0e0e0; padding: 8px 12px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 15px; }
            .field-group { margin: 12px 0; }
            .field-label { font-weight: bold; font-size: 13px; margin-bottom: 3px; }
            .input-box { border: 1px solid #999; min-height: 20px; padding: 5px; background: #fafafa; }
            .input-box.empty { color: #999; font-style: italic; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            table td { padding: 5px; border: 1px solid #999; }
            .document-list { margin: 10px 0; }
            .doc-item { padding: 5px; border-bottom: 1px solid #ddd; }
            h1 { text-align: center; font-size: 16px; margin: 20px 0; }
            .professional-group { margin: 15px 0; }
            .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
            .badge-success { background: #d4edda; color: #155724; }
            .badge-warning { background: #fff3cd; color: #856404; }
            .badge-danger { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>
        <h1>MAJOR SUBDIVISION PLAT APPLICATION</h1>

        <!-- Form Information -->
        <div class="section">
            <h2>Form Information</h2>
            <table>
                <tr>
                    <td style="font-weight:bold; width:30%;">Form ID:</td>
                    <td><?= htmlspecialchars($form_id) ?></td>
                    <td style="font-weight:bold; width:30%;">Submitted:</td>
                    <td><?= !empty($form_details['form_datetime_submitted']) ? date('M j, Y g:i A', strtotime($form_details['form_datetime_submitted'])) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Status:</td>
                    <td><?= !empty($form_details['form_datetime_resolved']) ? 'Resolved' : 'Pending' ?></td>
                    <td style="font-weight:bold;">Payment:</td>
                    <td><?= $form_details['form_paid_bool'] ? 'Paid' : 'Unpaid' ?></td>
                </tr>
            </table>
        </div>

        <!-- Application Dates -->
        <div class="section">
            <h2>Application Dates</h2>
            <div class="field-group">
                <div class="field-label">Filing Date:</div>
                <div class="input-box"><?= !empty($form_details['technical_app_filing_date']) ? htmlspecialchars($form_details['technical_app_filing_date']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Technical Review Date:</div>
                <div class="input-box"><?= !empty($form_details['technical_review_date']) ? htmlspecialchars($form_details['technical_review_date']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Preliminary Approval Date:</div>
                <div class="input-box"><?= !empty($form_details['technical_prelim_approval_date']) ? htmlspecialchars($form_details['technical_prelim_approval_date']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Final Approval Date:</div>
                <div class="input-box"><?= !empty($form_details['technical_final_approval_date']) ? htmlspecialchars($form_details['technical_final_approval_date']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
        </div>

        <!-- Applicants & Owners -->
        <div class="section">
            <h2>Applicants & Property Owners</h2>
            <div class="field-group">
                <div class="field-label">Applicants:</div>
                <div class="input-box"><?= !empty($form_details['applicants']) ? nl2br(htmlspecialchars($form_details['applicants'])) : '<span style="color:#999;">Not listed</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Property Owners:</div>
                <div class="input-box"><?= !empty($form_details['property_owners']) ? nl2br(htmlspecialchars($form_details['property_owners'])) : '<span style="color:#999;">Not listed</span>' ?></div>
            </div>
        </div>

        <!-- Property Information -->
        <div class="section">
            <h2>Property Information</h2>
            <div class="field-group">
                <div class="field-label">Street Address:</div>
                <div class="input-box"><?= !empty($form_details['property_street']) ? htmlspecialchars($form_details['property_street']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <table>
                <tr>
                    <td style="font-weight:bold;">City:</td>
                    <td><?= !empty($form_details['property_city']) ? htmlspecialchars($form_details['property_city']) : 'N/A' ?></td>
                    <td style="font-weight:bold;">State:</td>
                    <td><?= !empty($form_details['property_state']) ? htmlspecialchars($form_details['property_state']) : 'N/A' ?></td>
                    <td style="font-weight:bold;">Zip:</td>
                    <td><?= !empty($form_details['property_zip']) ? htmlspecialchars($form_details['property_zip']) : 'N/A' ?></td>
                </tr>
            </table>
            <div class="field-group">
                <div class="field-label">PVA Parcel Number:</div>
                <div class="input-box"><?= !empty($form_details['PVA_parcel_number']) ? htmlspecialchars($form_details['PVA_parcel_number']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Acreage:</div>
                <div class="input-box"><?= !empty($form_details['property_acreage']) ? htmlspecialchars($form_details['property_acreage']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Current Zoning:</div>
                <div class="input-box"><?= !empty($form_details['property_current_zoning']) ? htmlspecialchars($form_details['property_current_zoning']) : '<span style="color:#999;">Not provided</span>' ?></div>
            </div>
        </div>

        <!-- Surveyor & Engineer -->
        <div class="section">
            <h2>Professional Contacts</h2>
            <div class="professional-group">
                <div style="font-weight:bold; margin-bottom:8px;">SURVEYOR</div>
                <table>
                    <tr>
                        <td style="font-weight:bold;">Name:</td>
                        <td><?= !empty($form_details['surveyor_first_name']) || !empty($form_details['surveyor_last_name']) ? htmlspecialchars($form_details['surveyor_first_name'] . ' ' . $form_details['surveyor_last_name']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Firm:</td>
                        <td><?= !empty($form_details['surveyor_firm']) ? htmlspecialchars($form_details['surveyor_firm']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Phone:</td>
                        <td><?= !empty($form_details['surveyor_phone']) ? htmlspecialchars($form_details['surveyor_phone']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Cell:</td>
                        <td><?= !empty($form_details['surveyor_cell']) ? htmlspecialchars($form_details['surveyor_cell']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Email:</td>
                        <td><?= !empty($form_details['surveyor_email']) ? htmlspecialchars($form_details['surveyor_email']) : 'N/A' ?></td>
                    </tr>
                </table>
            </div>
            <div class="professional-group" style="margin-top:20px;">
                <div style="font-weight:bold; margin-bottom:8px;">ENGINEER</div>
                <table>
                    <tr>
                        <td style="font-weight:bold;">Name:</td>
                        <td><?= !empty($form_details['engineer_first_name']) || !empty($form_details['engineer_last_name']) ? htmlspecialchars($form_details['engineer_first_name'] . ' ' . $form_details['engineer_last_name']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Firm:</td>
                        <td><?= !empty($form_details['engineer_firm']) ? htmlspecialchars($form_details['engineer_firm']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Phone:</td>
                        <td><?= !empty($form_details['engineer_phone']) ? htmlspecialchars($form_details['engineer_phone']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Cell:</td>
                        <td><?= !empty($form_details['engineer_cell']) ? htmlspecialchars($form_details['engineer_cell']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Email:</td>
                        <td><?= !empty($form_details['engineer_email']) ? htmlspecialchars($form_details['engineer_email']) : 'N/A' ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Subdivision Documents -->
        <div class="section">
            <h2>Submitted Documents</h2>
            <?php
            $docs = [
                'Topographic Survey' => 'mspa_topographic_survey',
                'Proposed Plot Layout' => 'mspa_proposed_plot_layout',
                'Plat Restrictions' => 'mspa_plat_restrictions',
                'Property Owner Covenants' => 'mspa_property_owner_convenants',
                'Association Covenants' => 'mspa_association_covenants',
                'Master Deed' => 'mspa_master_deed',
                'Construction Plans' => 'mspa_construction_plans',
                'Traffic Impact Study' => 'mspa_traffic_impact_study',
                'Geologic Study' => 'mspa_geologic_study',
                'Drainage Plan' => 'mspa_drainage_plan',
                'Pavement Design' => 'mspa_pavement_design',
                'SWPPP/EPSC Plan' => 'mspa_SWPPP_EPSC_plan',
                'Construction Bond Estimate' => 'mspa_construction_bond_est'
            ];
            $submitted = 0;
            foreach ($docs as $doc) {
                if (!empty($form_details[$doc])) $submitted++;
            }
            ?>
            <div style="margin-bottom: 15px;">
                <span><?= $submitted ?> of <?= count($docs) ?> documents submitted</span>
                <?php if ($submitted === count($docs)): ?>
                    <span class="badge badge-success">Complete</span>
                <?php elseif ($submitted > 0): ?>
                    <span class="badge badge-warning">Incomplete</span>
                <?php else: ?>
                    <span class="badge badge-danger">No documents</span>
                <?php endif; ?>
            </div>
            <div class="document-list">
                <?php foreach ($docs as $label => $field): ?>
                    <div class="doc-item">
                        <strong><?= htmlspecialchars($label) ?>:</strong>
                        <?= !empty($form_details[$field]) ? htmlspecialchars($form_details[$field]) : '<span style="color:#999;">Not submitted</span>' ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}


function renderHeader($title) {
    ?>
    <h1>
        DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>
        <?= htmlspecialchars($title) ?>
    </h1>
    <?php
}

function renderApplicationDates($form_details) {
    $dates = [
        'Filing Date' => getValue($form_details, 'technical_app_filing_date'),
        'Review Date' => getValue($form_details, 'technical_review_date'),
        'Preliminary Approval' => getValue($form_details, 'technical_prelim_approval_date'),
        'Final Approval' => getValue($form_details, 'technical_final_approval_date')
    ];
    
    // Check if any dates exist
    $hasDates = array_filter($dates, fn($date) => !empty($date));
    ?>
    <section class="section">
        <h2>APPLICATION DATES</h2>
        <?php if (empty($hasDates)): ?>
            <div class="empty-state">No application dates recorded</div>
        <?php else: ?>
            <table class="info-table">
                <tr>
                    <th>Filing Date:</th>
                    <td><?= formatDateValue($dates['Filing Date']) ?></td>
                    <th>Review Date:</th>
                    <td><?= formatDateValue($dates['Review Date']) ?></td>
                </tr>
                <tr>
                    <th>Preliminary Approval:</th>
                    <td><?= formatDateValue($dates['Preliminary Approval']) ?></td>
                    <th>Final Approval:</th>
                    <td><?= formatDateValue($dates['Final Approval']) ?></td>
                </tr>
            </table>
        <?php endif; ?>
    </section>
    <?php
}

function renderApplicants($form_details) {
    $applicants = getValue($form_details, 'applicants');
    ?>
    <section class="section">
        <h2>APPLICANT(S)</h2>
        <div class="input-box <?= empty($applicants) ? 'empty' : '' ?>">
            <?= !empty($applicants) ? htmlspecialchars($applicants) : '<span class="placeholder">No applicants listed</span>' ?>
        </div>
    </section>
    <?php
}

function renderPropertyInformation($form_details) {
    $fields = [
        'Property Address' => formatAddress($form_details, 'property'),
        'PVA Parcel Number' => getValue($form_details, 'pva_parcel_number'),
        'Acreage' => getValue($form_details, 'property_acreage'),
        'Current Zoning' => getValue($form_details, 'property_current_zoning')
    ];
    
    // Check if any property information exists
    $hasPropertyInfo = array_filter($fields, fn($value) => !empty($value));
    ?>
    <section class="section">
        <h2>PROPERTY INFORMATION</h2>
        <?php if (empty($hasPropertyInfo)): ?>
            <div class="empty-state">No property information available</div>
        <?php else: ?>
            <?php foreach ($fields as $label => $value): ?>
                <div class="field-group">
                    <div class="field-label"><?= htmlspecialchars($label) ?>:</div>
                    <div class="input-box <?= empty($value) ? 'empty' : '' ?>">
                        <?= !empty($value) ? htmlspecialchars($value) : '<span class="placeholder">Not provided</span>' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php
}

function renderProfessionals($form_details) {
    $professionals = [
        'SURVEYOR' => [
            'first_name' => getValue($form_details, 'surveyor_first_name'),
            'last_name' => getValue($form_details, 'surveyor_last_name'),
            'firm' => getValue($form_details, 'surveyor_firm'),
            'email' => getValue($form_details, 'surveyor_email'),
            'phone' => getValue($form_details, 'surveyor_phone')
        ],
        'ENGINEER' => [
            'first_name' => getValue($form_details, 'engineer_first_name'),
            'last_name' => getValue($form_details, 'engineer_last_name'),
            'firm' => getValue($form_details, 'engineer_firm'),
            'email' => getValue($form_details, 'engineer_email'),
            'phone' => getValue($form_details, 'engineer_phone')
        ]
    ];
    ?>
    <section class="section">
        <h2>PROFESSIONAL CONTACTS</h2>
        <?php foreach ($professionals as $title => $person): ?>
            <div class="professional-group">
                <h3><?= $title ?></h3>
                <?php 
                $formatted = formatProfessionalInfo($person);
                ?>
                <div class="input-box <?= $formatted['isEmpty'] ? 'empty' : '' ?>">
                    <?= $formatted['html'] ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
    <?php
}

function renderRequiredDocuments($form_details) {
    $documents = [
        'Topographic Survey' => 'mspa_topographic_survey',
        'Proposed Plot Layout' => 'mspa_proposed_plot_layout',
        'Plat Restrictions' => 'mspa_plat_restrictions',
        'Property Owner Covenants' => 'mspa_property_owner_convenants',
        'Association Covenants' => 'mspa_association_covenants',
        'Master Deed' => 'mspa_master_deed',
        'Construction Plans' => 'mspa_construction_plans',
        'Traffic Impact Study' => 'mspa_traffic_impact_study',
        'Geologic Study' => 'mspa_geologic_study',
        'Drainage Plan' => 'mspa_drainage_plan',
        'Pavement Design' => 'mspa_pavement_design',
        'SWPPP/EPSC Plan' => 'mspa_SWPPP_EPSC_plan',
        'Construction Bond Estimate' => 'mspa_construction_bond_est'
    ];
    
    // Count submitted documents
    $submittedCount = 0;
    foreach ($documents as $key) {
        if (!empty($form_details[$key])) {
            $submittedCount++;
        }
    }
    ?>
    <section class="section">
        <h2>REQUIRED DOCUMENTS</h2>
        <div class="document-summary">
            <span class="summary-text">
                <?= $submittedCount ?> of <?= count($documents) ?> documents submitted
            </span>
            <?php if ($submittedCount === count($documents)): ?>
                <span class="badge badge-success">Complete</span>
            <?php elseif ($submittedCount > 0): ?>
                <span class="badge badge-warning">Incomplete</span>
            <?php else: ?>
                <span class="badge badge-danger">No documents</span>
            <?php endif; ?>
        </div>
        
        <div class="document-checklist">
            <?php foreach ($documents as $label => $key): ?>
                <?php 
                $value = getValue($form_details, $key);
                $isSubmitted = !empty($value);
                ?>
                <div class="checkbox-item <?= $isSubmitted ? 'submitted' : 'not-submitted' ?>">
                    <span class="checkbox <?= $isSubmitted ? 'checked' : 'unchecked' ?>">
                        <?= $isSubmitted ? '✓' : '☐' ?>
                    </span>
                    <span class="checkbox-label"><?= htmlspecialchars($label) ?></span>
                    <?php if ($isSubmitted && $value !== '1' && $value !== 'Yes'): ?>
                        <span class="file-name" title="<?= htmlspecialchars($value) ?>">
                            (<?= htmlspecialchars(truncateFileName($value, 30)) ?>)
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Format a date value with null handling
 */
function formatDateValue($date) {
    if (empty($date)) {
        return '<span class="placeholder">Not set</span>';
    }
    
    // Try to format the date nicely
    try {
        $dateObj = new DateTime($date);
        return htmlspecialchars($dateObj->format('M d, Y'));
    } catch (Exception $e) {
        return htmlspecialchars($date);
    }
}


/**
 * Format professional contact information with null handling
 */
function formatProfessionalInfo($person) {
    $firstName = getValue($person, 'first_name');
    $lastName = getValue($person, 'last_name');
    $firm = getValue($person, 'firm');
    $email = getValue($person, 'email');
    $phone = getValue($person, 'phone');
    
    // Build name
    $nameParts = array_filter([$firstName, $lastName]);
    $name = !empty($nameParts) ? implode(' ', $nameParts) : null;
    
    // Check if we have any information
    $hasInfo = $name || $firm || $email || $phone;
    
    if (!$hasInfo) {
        return [
            'html' => '<span class="placeholder">Not assigned</span>',
            'isEmpty' => true
        ];
    }
    
    // Build HTML
    $html = '';
    
    if ($name) {
        $html .= '<div class="professional-name">' . htmlspecialchars($name) . '</div>';
    }
    
    if ($firm) {
        $html .= '<div class="professional-firm">' . htmlspecialchars($firm) . '</div>';
    }
    
    $contactParts = [];
    if ($email) {
        $contactParts[] = '<a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>';
    }
    if ($phone) {
        $contactParts[] = '<a href="tel:' . htmlspecialchars($phone) . '">' . htmlspecialchars($phone) . '</a>';
    }
    
    if (!empty($contactParts)) {
        $html .= '<div class="professional-contact">' . implode(' • ', $contactParts) . '</div>';
    }
    
    return [
        'html' => $html,
        'isEmpty' => false
    ];
}

/**
 * Truncate a filename to a maximum length
 */
function truncateFileName($filename, $maxLength = 30) {
    if (strlen($filename) <= $maxLength) {
        return $filename;
    }
    
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    
    $truncated = substr($basename, 0, $maxLength - strlen($extension) - 4) . '...';
    
    return $truncated . '.' . $extension;
}


// 8. MINOR SUBDIVISION PLAT APPLICATION
function generateMinorSubdivisionHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Minor Subdivision Plat - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>MINOR SUBDIVISION PLAT APPLICATION</h1>

        <h2>APPLICATION DATES</h2>
        <table class="info-table">
            <tr>
                <th>Filing Date:</th>
                <td><?php echo htmlspecialchars($form_details['technical_app_filing_date'] ?? ''); ?></td>
                <th>Review Date:</th>
                <td><?php echo htmlspecialchars($form_details['technical_review_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PROPERTY INFORMATION</h2>
        <div class="field-label">Property Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'property'); ?></div>
        <div class="field-label">PVA Parcel Number:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['pva_parcel_number'] ?? ''); ?></div>

        <h2>SURVEYOR</h2>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['surveyor_first_name'] ?? '') . ' ' . ($form_details['surveyor_last_name'] ?? '') . ' - ' . ($form_details['surveyor_firm'] ?? '')); ?></div>

        <h2>REQUIRED DOCUMENTS</h2>
        <div class="checkbox-item">Topographic Survey: <?php echo $form_details['minspa_topographic_survey'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">Proposed Plot Layout: <?php echo $form_details['minspa_proposed_plot_layout'] ? 'Yes' : 'No'; ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 9. GENERAL DEVELOPMENT PLAN APPLICATION
function generateGeneralDevelopmentPlanHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>General Development Plan - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>GENERAL DEVELOPMENT PLAN APPLICATION</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Docket Number:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></td>
                <td class="label">Hearing Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PLAN AMENDMENT REQUEST</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['gdpa_plan_amendment_request'] ?? '')); ?></div>

        <h2>PROPOSED CONDITIONS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['gdpa_proposed_conditions'] ?? '')); ?></div>

        <h2>FINDINGS TYPE</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['required_findings_type'] ?? ''); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 10. SITE DEVELOPMENT PLAN APPLICATION
function generateSiteDevelopmentPlanHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Site Development Plan - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>SITE DEVELOPMENT PLAN APPLICATION</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Docket Number:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></td>
                <td class="label">Hearing Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>SITE PLAN REQUEST</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['site_plan_request'] ?? '')); ?></div>

        <h2>PROFESSIONAL SERVICES</h2>
        <div class="field-label">Surveyor:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['surveyor_first_name'] ?? '') . ' ' . ($form_details['surveyor_last_name'] ?? '') . ' - ' . ($form_details['surveyor_firm'] ?? '')); ?></div>
        
        <div class="field-label">Engineer:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['engineer_first_name'] ?? '') . ' ' . ($form_details['engineer_last_name'] ?? '') . ' - ' . ($form_details['engineer_firm'] ?? '')); ?></div>
        
        <div class="field-label">Architect:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['architect_first_name'] ?? '') . ' ' . ($form_details['architect_last_name'] ?? '') . ' - ' . ($form_details['architect_firm'] ?? '')); ?></div>
        
        <div class="field-label">Landscape Architect:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['land_architect_first_name'] ?? '') . ' ' . ($form_details['land_architect_last_name'] ?? '') . ' - ' . ($form_details['land_architect_firm'] ?? '')); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 11. FUTURE LAND USE MAP (FLUM) APPLICATION
function generateFLUMApplicationHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>FLUM Application - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>FUTURE LAND USE MAP (FLUM) APPLICATION</h1>

        <table class="header-grid">
            <tr>
                <td class="label">Docket Number:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_docket_number'] ?? ''); ?></td>
                <td class="label">Hearing Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['hearing_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PROPERTY INFORMATION</h2>
        <div class="field-label">Property Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'property'); ?></div>
        <div class="field-label">PVA Parcel Number:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['pva_parcel_number'] ?? ''); ?></div>
        <div class="field-label">Current Zoning:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['property_current_zoning'] ?? ''); ?></div>

        <h2>FUTURE LAND USE MAP AMENDMENT PROPOSAL</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['future_land_use_map_amendment_prop'] ?? '')); ?></div>

        <h2>FINDINGS TYPE</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['required_findings_type'] ?? ''); ?></div>

        <h2>FINDINGS EXPLANATION</h2>
        <div class="input-box xlarge-input"><?php echo nl2br(htmlspecialchars($form_details['findings_explanation'] ?? '')); ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 12. ADJACENT PROPERTY OWNERS FORM
function generateAdjacentPropertyOwnersHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Adjacent Property Owners - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>ADJACENT PROPERTY OWNERS FORM</h1>

        <?php 
        // Debug: Check what we received
        if (!$form_details) {
            echo '<div class="section-container"><p class="note">No form details available.</p></div>';
        } elseif (!isset($form_details['neighbors'])) {
            echo '<div class="section-container"><p class="note">No neighbors data found in form details.</p></div>';
        } elseif (!is_array($form_details['neighbors'])) {
            echo '<div class="section-container"><p class="note">Neighbors data is not in expected format.</p></div>';
        } elseif (count($form_details['neighbors']) === 0) {
            echo '<div class="section-container"><p class="note">No adjacent properties recorded for this form.</p></div>';
        } else {
            // We have neighbors, display them
            foreach ($form_details['neighbors'] as $index => $neighbor):
        ?>
                <h2>Adjacent Property #<?php echo ($index + 1); ?></h2>
                <div class="section-container">
                    <div class="field-label">PVA Map Code:</div>
                    <div class="input-box"><?php echo htmlspecialchars($neighbor['PVA_map_code'] ?? ''); ?></div>
                    
                    <div class="field-label">Property Location:</div>
                    <div class="input-box"><?php echo htmlspecialchars($neighbor['apof_neighbor_property_location'] ?? ''); ?></div>
                    
                    <div class="field-label">Deed Book:</div>
                    <div class="input-box"><?php echo htmlspecialchars($neighbor['apof_neighbor_property_deed_book'] ?? ''); ?></div>
                    
                    <div class="field-label">Page Number:</div>
                    <div class="input-box"><?php echo htmlspecialchars($neighbor['apof_property_street_pg_number'] ?? ''); ?></div>

                    <?php if (isset($neighbor['owners']) && is_array($neighbor['owners']) && count($neighbor['owners']) > 0): ?>
                        <h3 style="font-size: 12pt; color: #667eea; margin-top: 20px; margin-bottom: 10px;">Property Owner(s):</h3>
                        <?php foreach ($neighbor['owners'] as $owner_index => $owner): ?>
                            <div style="background: #f8f9fa; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 3px solid #667eea;">
                                <div style="font-weight: 600; margin-bottom: 8px; color: #495057;">Owner #<?php echo ($owner_index + 1); ?></div>
                                
                                <div class="field-label">Name:</div>
                                <div class="input-box"><?php echo htmlspecialchars(trim(($owner['adjacent_property_owner_first_name'] ?? '') . ' ' . ($owner['adjacent_property_owner_last_name'] ?? ''))); ?></div>
                                
                                <div class="field-label">Mailing Address:</div>
                                <div class="input-box">
                                    <?php 
                                    $address_parts = array_filter([
                                        $owner['owner_street'] ?? '',
                                        $owner['owner_city'] ?? '',
                                        $owner['owner_state'] ?? '',
                                        $owner['owner_zip'] ?? ''
                                    ]);
                                    echo htmlspecialchars(implode(', ', $address_parts)); 
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="note">No property owners recorded for this property.</p>
                    <?php endif; ?>
                </div>
        <?php 
            endforeach;
        }
        ?>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 13. OPEN RECORDS REQUEST
function generateOpenRecordsRequestHtml($form_id, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Open Records Request - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>OPEN RECORDS REQUEST</h1>

        <h2>APPLICANT INFORMATION</h2>
        <div class="field-label">Name:</div>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['orr_applicant_first_name'] ?? '') . ' ' . ($form_details['orr_applicant_last_name'] ?? '')); ?></div>
        
        <div class="field-label">Telephone:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['orr_applicant_telephone'] ?? ''); ?></div>
        
        <div class="field-label">Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'orr_applicant'); ?></div>

        <h2>REQUEST DETAILS</h2>
        <div class="field-label">Commercial Purpose:</div>
        <div class="input-box"><?php echo $form_details['orr_commercial_purpose'] ? 'Yes' : 'No'; ?></div>
        
        <div class="field-label">Request for Copies:</div>
        <div class="input-box"><?php echo $form_details['orr_request_for_copies'] ? 'Yes' : 'No'; ?></div>

        <h2>REQUESTED RECORDS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['requested_records'] ?? '')); ?></div>

        <h2>REQUEST DATES</h2>
        <div class="field-label">Received On:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['orr_received_on_datetime'] ?? ''); ?></div>
        
        <div class="field-label">Receivable:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['orr_receivable_datetime'] ?? ''); ?></div>

        <?php if (!empty($form_details['orr_denied_reasons'])): ?>
        <h2>DENIAL REASONS</h2>
        <div class="input-box large-input"><?php echo nl2br(htmlspecialchars($form_details['orr_denied_reasons'])); ?></div>
        <?php endif; ?>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 14. ZONING PERMIT APPLICATION
function generateZoningPermitHtml($form_id, $form_details) {
    // Ensure form_details is an array, default to empty array if null
    if (!is_array($form_details)) {
        $form_details = [];
    }
    
    ob_start(); ?>
    <html>
    <head>
        <title>Zoning Permit Application</title>
        <style>
            body { font-family: Arial, sans-serif; background: white; color: #333; }
            .section { margin: 30px 0; page-break-inside: avoid; }
            .section h2 { background: #e0e0e0; padding: 8px 12px; font-weight: bold; font-size: 14px; text-transform: uppercase; margin-bottom: 15px; }
            .field-group { margin: 12px 0; }
            .field-label { font-weight: bold; font-size: 13px; margin-bottom: 3px; }
            .input-box { border: 1px solid #999; min-height: 20px; padding: 5px; background: #fafafa; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            table td { padding: 5px; border: 1px solid #999; }
            h1 { text-align: center; font-size: 16px; margin: 20px 0; }
            .checklist { margin: 10px 0; padding-left: 20px; }
            .checklist-item { margin: 8px 0; }
            .placeholder { color: #999; font-style: italic; }
        </style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>APPLICATION FOR ZONING PERMIT</h1>

        <!-- Application Information -->
        <div class="section">
            <h2>Application Information</h2>
            <table>
                <tr>
                    <td style="font-weight:bold; width:30%;">Application Filed:</td>
                    <td><?= !empty($form_details['form_datetime_submitted']) ? htmlspecialchars($form_details['form_datetime_submitted']) : '<span class="placeholder">Not provided</span>' ?></td>
                    <td style="font-weight:bold; width:30%;">Permit No.:</td>
                    <td><?= htmlspecialchars($form_id) ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Approval Date:</td>
                    <td><?= !empty($form_details['form_datetime_resolved']) ? htmlspecialchars($form_details['form_datetime_resolved']) : '<span class="placeholder">Pending</span>' ?></td>
                    <td style="font-weight:bold;">Status:</td>
                    <td><?= !empty($form_details['form_datetime_resolved']) ? 'Approved' : 'Pending' ?></td>
                </tr>
            </table>
        </div>

        <!-- Applicant Information -->
        <div class="section">
            <h2>Applicant Information</h2>
            <div class="field-group">
                <div class="field-label">Applicant Name:</div>
                <div class="input-box"><?= !empty($form_details['applicant_first_names']) ? htmlspecialchars($form_details['applicant_first_names']) : '<span class="placeholder">Not provided</span>' ?></div>
            </div>
        </div>

        <!-- Property Information -->
        <div class="section">
            <h2>Property Information</h2>
            <div class="field-group">
                <div class="field-label">Street Address:</div>
                <div class="input-box"><?= !empty($form_details['property_street']) ? htmlspecialchars($form_details['property_street']) : '<span class="placeholder">Not provided</span>' ?></div>
            </div>
            <table>
                <tr>
                    <td style="font-weight:bold;">City:</td>
                    <td><?= !empty($form_details['property_city']) ? htmlspecialchars($form_details['property_city']) : '<span class="placeholder">N/A</span>' ?></td>
                    <td style="font-weight:bold;">State:</td>
                    <td><?= !empty($form_details['property_state']) ? htmlspecialchars($form_details['property_state']) : '<span class="placeholder">N/A</span>' ?></td>
                    <td style="font-weight:bold;">Zip:</td>
                    <td><?= !empty($form_details['property_zip']) ? htmlspecialchars($form_details['property_zip']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
            </table>
            <div class="field-group">
                <div class="field-label">Parcel Number:</div>
                <div class="input-box"><?= !empty($form_details['PVA_parcel_number']) ? htmlspecialchars($form_details['PVA_parcel_number']) : '<span class="placeholder">Not provided</span>' ?></div>
            </div>
            <div class="field-group">
                <div class="field-label">Current Zoning:</div>
                <div class="input-box"><?= !empty($form_details['property_current_zoning']) ? htmlspecialchars($form_details['property_current_zoning']) : '<span class="placeholder">Not provided</span>' ?></div>
            </div>
        </div>

        <!-- Professional Services -->
        <div class="section">
            <h2>Professional Contacts</h2>
            <div style="font-weight:bold; margin-bottom:10px;">Surveyor/Engineer:</div>
            <table>
                <tr>
                    <td style="font-weight:bold;">Name:</td>
                    <td><?= !empty($form_details['surveyor_first_name']) ? htmlspecialchars($form_details['surveyor_first_name'] . ' ' . ($form_details['surveyor_last_name'] ?? '')) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Firm:</td>
                    <td><?= !empty($form_details['surveyor_firm']) ? htmlspecialchars($form_details['surveyor_firm']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Phone:</td>
                    <td><?= !empty($form_details['surveyor_phone']) ? htmlspecialchars($form_details['surveyor_phone']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Email:</td>
                    <td><?= !empty($form_details['surveyor_email']) ? htmlspecialchars($form_details['surveyor_email']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
            </table>
            
            <div style="font-weight:bold; margin-top:15px; margin-bottom:10px;">Contractor:</div>
            <table>
                <tr>
                    <td style="font-weight:bold;">Name:</td>
                    <td><?= !empty($form_details['contractor_first_name']) ? htmlspecialchars($form_details['contractor_first_name'] . ' ' . ($form_details['contractor_last_name'] ?? '')) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Firm:</td>
                    <td><?= !empty($form_details['contractor_firm']) ? htmlspecialchars($form_details['contractor_firm']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Phone:</td>
                    <td><?= !empty($form_details['contractor_phone']) ? htmlspecialchars($form_details['contractor_phone']) : '<span class="placeholder">N/A</span>' ?></td>
                </tr>
            </table>
        </div>

        <!-- Permit Details -->
        <div class="section">
            <h2>Permit Details</h2>
            <div class="field-group">
                <div class="field-label">Project Type:</div>
                <div class="input-box"><?= !empty($form_details['project_type']) ? htmlspecialchars($form_details['project_type']) : '<span class="placeholder">Not provided</span>' ?></div>
            </div>
        </div>

        <!-- Application Checklist -->
        <div class="section">
            <h2>Application Checklist</h2>
            <div class="checklist">
                <div class="checklist-item">☐ Completed and signed application form</div>
                <div class="checklist-item">☐ Plot plan/site plan showing property lines and proposed structure</div>
                <div class="checklist-item">☐ Floor plan(s) and elevations of proposed structure</div>
                <div class="checklist-item">☐ Proof of property ownership</div>
                <div class="checklist-item">☐ Filing and permit fees</div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="section">
            <h2>Payment Status</h2>
            <table>
                <tr>
                    <td style="font-weight:bold; width:40%;">Paid:</td>
                    <td><?= !empty($form_details['form_paid_bool']) ? 'Yes' : 'No' ?></td>
                </tr>
            </table>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// ========================================
// MAIN DISPATCHER FUNCTION
// ========================================

function generateFormPDF($form_id, $form_type, $form_details) {
    switch ($form_type) {
        case 'Variance Application':
            return generateVarianceApplicationHtml($form_id, $form_details);
            
        case 'Conditional Use Permit Application':
            return generateConditionalUsePermitHtml($form_id, $form_details);
            
        case 'Zoning Map Amendment Application':
            return generateZoningMapAmendmentHtml($form_id, $form_details);
            
        case 'Administrative Appeal Request':
            return generateAdministrativeAppealHtml($form_id, $form_details);
            
        case 'Zoning Verification Application':
            return generateZoningVerificationHtml($form_id, $form_details);
            
        case 'Sign Permit Appplication': // Note: typo in database
            return generateSignPermitHtml($form_id, $form_details);
            
        case 'Major Subdivision Plat Application':
            return generateMajorSubdivisionHtml($form_id, $form_details);
            
        case 'Minor Subdivision Plat Application':
            return generateMinorSubdivisionHtml($form_id, $form_details);
            
        case 'Development Plan Application (General)':
            return generateGeneralDevelopmentPlanHtml($form_id, $form_details);
            
        case 'Development Plan Application (Site)':
            return generateSiteDevelopmentPlanHtml($form_id, $form_details);
            
        case 'Future Land Use Map (FLUM) Application':
            return generateFLUMApplicationHtml($form_id, $form_details);
            
        case 'Adjacent Property Owners Form':
            return generateAdjacentPropertyOwnersHtml($form_id, $form_details);
            
        case 'Open Records Request':
            return generateOpenRecordsRequestHtml($form_id, $form_details);
            
        case 'Zoning Permit Application':
            return generateZoningPermitHtml($form_id, $form_details);
            
        default:
            return generateGenericFormHtml($form_id, $form_type, $form_details);
    }
}

// Generic fallback for any form type not specifically handled
function generateGenericFormHtml($form_id, $form_type, $form_details) {
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($form_type); ?> - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br><?php echo strtoupper(htmlspecialchars($form_type)); ?></h1>

        <h2>FORM ID: <?php echo htmlspecialchars($form_id); ?></h2>
        
        <h2>FORM DETAILS</h2>
        <?php foreach ($form_details as $key => $value): ?>
            <?php if ($value !== null && $value !== ''): ?>
                <div class="field-label"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</div>
                <div class="input-box"><?php echo nl2br(htmlspecialchars($value)); ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>