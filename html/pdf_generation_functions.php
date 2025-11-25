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
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Conditional Use Permit - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
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
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Zoning Map Amendment - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
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
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Major Subdivision Plat - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style><?php echo getCommonStyles(); ?></style>
    </head>
    <body>
        <h1>DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION<br>MAJOR SUBDIVISION PLAT APPLICATION</h1>

        <h2>APPLICATION DATES</h2>
        <table class="info-table">
            <tr>
                <th>Filing Date:</th>
                <td><?php echo htmlspecialchars($form_details['technical_app_filing_date'] ?? ''); ?></td>
                <th>Review Date:</th>
                <td><?php echo htmlspecialchars($form_details['technical_review_date'] ?? ''); ?></td>
            </tr>
            <tr>
                <th>Preliminary Approval:</th>
                <td><?php echo htmlspecialchars($form_details['technical_prelim_approval_date'] ?? ''); ?></td>
                <th>Final Approval:</th>
                <td><?php echo htmlspecialchars($form_details['technical_final_approval_date'] ?? ''); ?></td>
            </tr>
        </table>

        <h2>APPLICANT(S)</h2>
        <div class="input-box"><?php echo htmlspecialchars($form_details['applicants'] ?? ''); ?></div>

        <h2>PROPERTY INFORMATION</h2>
        <div class="field-label">Property Address:</div>
        <div class="input-box"><?php echo formatAddress($form_details, 'property'); ?></div>
        <div class="field-label">PVA Parcel Number:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['pva_parcel_number'] ?? ''); ?></div>
        <div class="field-label">Acreage:</div>
        <div class="input-box"><?php echo htmlspecialchars($form_details['property_acreage'] ?? ''); ?></div>

        <h2>SURVEYOR</h2>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['surveyor_first_name'] ?? '') . ' ' . ($form_details['surveyor_last_name'] ?? '') . ' - ' . ($form_details['surveyor_firm'] ?? '')); ?></div>

        <h2>ENGINEER</h2>
        <div class="input-box"><?php echo htmlspecialchars(($form_details['engineer_first_name'] ?? '') . ' ' . ($form_details['engineer_last_name'] ?? '') . ' - ' . ($form_details['engineer_firm'] ?? '')); ?></div>

        <h2>REQUIRED DOCUMENTS</h2>
        <div class="checkbox-item">Topographic Survey: <?php echo $form_details['mspa_topographic_survey'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">Proposed Plot Layout: <?php echo $form_details['mspa_proposed_plot_layout'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">Plat Restrictions: <?php echo $form_details['mspa_plat_restrictions'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">Construction Plans: <?php echo $form_details['mspa_construction_plans'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">Traffic Impact Study: <?php echo $form_details['mspa_traffic_impact_study'] ? 'Yes' : 'No'; ?></div>
        <div class="checkbox-item">SWPPP/EPSC Plan: <?php echo $form_details['mspa_SWPPP_EPSC_plan'] ? 'Yes' : 'No'; ?></div>

        <?php echo getFooter($form_details); ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
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
    ob_start(); ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Zoning Permit Application - ID: <?php echo htmlspecialchars($form_id); ?></title>
        <style>
            body { 
                font-family: 'DejaVu Sans', sans-serif; 
                font-size: 10pt; 
                margin: 15mm;
                line-height: 1.3;
            }
            .header {
                text-align: center;
                font-weight: bold;
                font-size: 12pt;
                margin-bottom: 5px;
            }
            .title {
                text-align: center;
                font-weight: bold;
                font-size: 11pt;
                margin-bottom: 15px;
            }
            .header-grid {
                width: 100%;
                margin-bottom: 15px;
                border-collapse: collapse;
            }
            .header-grid td {
                padding: 5px;
                vertical-align: top;
            }
            .header-grid .label {
                font-weight: bold;
                width: 35%;
            }
            .header-grid .value {
                border-bottom: 1px solid #000;
                width: 65%;
                min-height: 18px;
            }
            h2 {
                background-color: #d9d9d9;
                padding: 5px;
                font-size: 10pt;
                font-weight: bold;
                margin-top: 15px;
                margin-bottom: 8px;
                text-transform: uppercase;
            }
            .field-row {
                margin-bottom: 8px;
            }
            .field-label {
                font-weight: bold;
                margin-bottom: 2px;
                font-size: 9pt;
            }
            .field-value {
                border-bottom: 1px solid #000;
                min-height: 18px;
                padding: 2px 5px;
            }
            .two-column {
                width: 100%;
                margin-bottom: 8px;
            }
            .two-column::after {
                content: "";
                display: table;
                clear: both;
            }
            .two-column .left {
                float: left;
                width: 58%;
                padding-right: 2%;
            }
            .two-column .right {
                float: left;
                width: 38%;
                padding-left: 2%;
            }
            .checklist-section {
                margin: 15px 0;
            }
            .checkbox-item {
                margin: 6px 0;
                padding-left: 20px;
                position: relative;
            }
            .checkbox-item:before {
                content: '☐';
                position: absolute;
                left: 0;
                font-size: 12pt;
            }
            .checkbox-item.checked:before {
                content: '☑';
            }
            .checkbox-inline {
                display: inline-block;
                margin-right: 15px;
            }
            .construction-table {
                width: 100%;
                border-collapse: collapse;
                margin: 10px 0;
            }
            .construction-table th {
                background-color: #d9d9d9;
                padding: 5px;
                border: 1px solid #000;
                font-weight: bold;
                font-size: 9pt;
            }
            .construction-table td {
                padding: 8px 5px;
                border: 1px solid #000;
                min-height: 25px;
            }
            .certification-section {
                margin-top: 20px;
                border: 1px solid #000;
                padding: 10px;
            }
            .signature-line {
                border-bottom: 1px solid #000;
                min-height: 20px;
                margin: 8px 0;
                padding: 2px 5px;
            }
            .note {
                font-size: 8pt;
                font-style: italic;
                margin-top: 5px;
            }
            .fee-section {
                margin-top: 20px;
                padding: 10px;
                border: 1px solid #000;
            }
            .fee-section .important {
                font-weight: bold;
                text-align: center;
                margin-bottom: 10px;
            }
            .footer {
                text-align: center;
                font-size: 9pt;
                margin-top: 20px;
                border-top: 2px solid #000;
                padding-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="header">DANVILLE-BOYLE COUNTY PLANNING & ZONING COMMISSION</div>
        <div class="title">APPLICATION FOR ZONING PERMIT</div>

        <!-- Header Information -->
        <table class="header-grid">
            <tr>
                <td class="label">Date Application Filed:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['form_datetime_submitted'] ?? ''); ?></td>
                <td class="label">Zoning Permit Approval Date:</td>
                <td class="value"><?php echo htmlspecialchars($form_details['form_datetime_resolved'] ?? ''); ?></td>
            </tr>
            <tr>
                <td class="label">Construction Start Date:</td>
                <td class="value"></td>
                <td class="label">Zoning Permit Approval No:</td>
                <td class="value"><?php echo htmlspecialchars($form_id ?? ''); ?></td>
            </tr>
        </table>

        <!-- APPLICANT(S) INFORMATION -->
        <h2>APPLICANT(S) INFORMATION</h2>

        <div class="field-row">
            <div class="field-label">1) APPLICANT(S) NAME(S):</div>
            <div class="field-value">
                <?php 
                $applicant_names = [];
                if (!empty($form_details['applicant_first_names'])) {
                    $first_names = explode(',', $form_details['applicant_first_names']);
                    $last_names = explode(',', $form_details['applicant_last_names'] ?? '');
                    for ($i = 0; $i < count($first_names); $i++) {
                        $applicant_names[] = trim($first_names[$i]) . ' ' . trim($last_names[$i] ?? '');
                    }
                }
                echo htmlspecialchars(implode(', ', $applicant_names));
                ?>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">Names of Officers, Directors, Shareholders or Members (If Applicable):</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['officers'] ?? ''); ?></div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Mailing Address:</div>
                <div class="field-value">
                    <?php 
                    $applicant_address = array_filter([
                        $form_details['address_street'] ?? '',
                        $form_details['address_city'] ?? '',
                        $form_details['state_code'] ?? '',
                        $form_details['address_zip_code'] ?? ''
                    ]);
                    echo htmlspecialchars(implode(', ', $applicant_address));
                    ?>
                </div>
            </div>
            <div class="right">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['t1_applicant_phone_number'] ?? ''); ?></div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">E-Mail Address:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['t1_applicant_email'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['t1_applicant_cell_phone'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">2) PROPERTY OWNER(S) NAME(S):</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['property_owners'] ?? ''); ?></div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Mailing Address:</div>
                <div class="field-value"></div>
            </div>
            <div class="right">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"></div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">E-Mail Address:</div>
                <div class="field-value"></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"></div>
            </div>
        </div>

        <div class="note">*PLEASE USE ADDITIONAL PAGES IF NEEDED*</div>

        <!-- Professional Services -->
        <div class="field-row" style="margin-top: 10px;">
            <div class="field-label">3) SURVEYOR/ ENGINEER:</div>
            <div class="two-column">
                <div class="left">
                    <div class="field-value">
                        <?php echo htmlspecialchars(trim(($form_details['surveyor_first_name'] ?? '') . ' ' . ($form_details['surveyor_last_name'] ?? ''))); ?>
                    </div>
                </div>
                <div class="right">
                    <div class="field-label">Name of Firm:</div>
                    <div class="field-value"><?php echo htmlspecialchars($form_details['surveyor_firm'] ?? ''); ?></div>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['surveyor_phone'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['surveyor_cell'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">E-Mail Address:</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['surveyor_email'] ?? ''); ?></div>
        </div>

        <div class="field-row" style="margin-top: 10px;">
            <div class="field-label">4) CONTRACTOR:</div>
            <div class="two-column">
                <div class="left">
                    <div class="field-value">
                        <?php echo htmlspecialchars(trim(($form_details['contractor_first_name'] ?? '') . ' ' . ($form_details['contractor_last_name'] ?? ''))); ?>
                    </div>
                </div>
                <div class="right">
                    <div class="field-label">Name of Firm:</div>
                    <div class="field-value"><?php echo htmlspecialchars($form_details['contractor_firm'] ?? ''); ?></div>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['contractor_phone'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['contractor_cell'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">E-Mail Address:</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['contractor_email'] ?? ''); ?></div>
        </div>

        <div class="field-row" style="margin-top: 10px;">
            <div class="field-label">5) ARCHITECT:</div>
            <div class="two-column">
                <div class="left">
                    <div class="field-value">
                        <?php echo htmlspecialchars(trim(($form_details['architect_first_name'] ?? '') . ' ' . ($form_details['architect_last_name'] ?? ''))); ?>
                    </div>
                </div>
                <div class="right">
                    <div class="field-label">Name of Firm:</div>
                    <div class="field-value"><?php echo htmlspecialchars($form_details['architect_firm'] ?? ''); ?></div>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['architect_phone'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['architect_cell'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">E-Mail Address:</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['architect_email'] ?? ''); ?></div>
        </div>

        <div class="field-row" style="margin-top: 10px;">
            <div class="field-label">6) LANDSCAPE ARCHITECT:</div>
            <div class="two-column">
                <div class="left">
                    <div class="field-value">
                        <?php echo htmlspecialchars(trim(($form_details['land_architect_first_name'] ?? '') . ' ' . ($form_details['land_architect_last_name'] ?? ''))); ?>
                    </div>
                </div>
                <div class="right">
                    <div class="field-label">Name of Firm:</div>
                    <div class="field-value"><?php echo htmlspecialchars($form_details['land_architect_firm'] ?? ''); ?></div>
                </div>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">Phone Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['land_architect_phone'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Cell Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['land_architect_cell'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">E-Mail Address:</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['land_architect_email'] ?? ''); ?></div>
        </div>

        <!-- PROPERTY INFORMATION -->
        <h2>PROPERTY INFORMATION</h2>

        <div class="field-row">
            <div class="field-label">Property Address:</div>
            <div class="field-value">
                <?php 
                $property_address = array_filter([
                    $form_details['property_street'] ?? '',
                    $form_details['property_city'] ?? '',
                    $form_details['property_state'] ?? '',
                    $form_details['property_zip'] ?? ''
                ]);
                echo htmlspecialchars(implode(', ', $property_address));
                ?>
            </div>
        </div>

        <div class="two-column">
            <div class="left">
                <div class="field-label">PVA Parcel Number:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['PVA_parcel_number'] ?? ''); ?></div>
            </div>
            <div class="right">
                <div class="field-label">Acreage:</div>
                <div class="field-value"><?php echo htmlspecialchars($form_details['property_acreage'] ?? ''); ?></div>
            </div>
        </div>

        <div class="field-row">
            <div class="field-label">Current Zoning:</div>
            <div class="field-value"><?php echo htmlspecialchars($form_details['property_current_zoning'] ?? ''); ?></div>
        </div>

        <!-- APPLICATION CHECKLIST -->
        <h2>APPLICATION CHECKLIST</h2>

        <div class="checklist-section">
            <div class="checkbox-item">A completed and signed Application</div>
            
            <div class="field-row">
                <strong>Type of Project:</strong>
                <span class="checkbox-inline">_____ Multi-Family Use</span>
                <span class="checkbox-inline">_____ Commercial Use</span>
                <span class="checkbox-inline">_____ Industrial Use</span><br>
                <span style="margin-left: 120px;" class="checkbox-inline">_____ Temporary Use</span>
                <span class="checkbox-inline">_____ Parking/ Display</span>
                <span class="checkbox-inline">_____ Use Change</span>
                <?php if (!empty($form_details['project_type'])): ?>
                <br><strong>Selected: <?php echo htmlspecialchars($form_details['project_type']); ?></strong>
                <?php endif; ?>
            </div>

            <div class="checkbox-item <?php echo (!empty($form_details['zpa_project_plans']) && $form_details['zpa_project_plans']) ? 'checked' : ''; ?>">
                Complete set of project plans depicting the various portion(s) of the property to be included in the proposed construction project (Please include: two (2) - 11" x 17" plan-sets)
            </div>
            
            <div class="checkbox-item">Landscape, Drainage and/ or Stormwater Plan(s), if applicable</div>
            
            <div class="checkbox-item">Water/ Sewer/ Floodplain Verification Letter(s) or Signature(s), if applicable</div>
            
            <div class="checkbox-item <?php echo (!empty($form_details['zpa_preliminary_site_evaluation']) && $form_details['zpa_preliminary_site_evaluation']) ? 'checked' : ''; ?>">
                Preliminary site evaluation information (if project is not required to be on public sewer)
            </div>
        </div>

        <!-- CONSTRUCTION INFORMATION -->
        <h2>CONSTRUCTION INFORMATION</h2>

        <table class="construction-table">
            <tr>
                <th>Type of Structure or Use</th>
                <th>Square Feet</th>
                <th>Project Value</th>
                <th>Notes</th>
            </tr>
            <tr>
                <td></td>
                <td>S.F.</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>S.F.</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td>S.F.</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <!-- APPLICANT'S CERTIFICATION -->
        <div class="certification-section">
            <h2 style="margin-top: 0;">APPLICANT'S CERTIFICATION</h2>
            
            <p style="font-size: 9pt; margin-bottom: 10px;">
                I do hereby certify that, to the best of my knowledge and belief, all application materials have been submitted
                and that the information they contain is true and correct. Please attach additional signature pages if needed.
            </p>

            <div class="field-label">Signature of Applicant(s) and Property Owner(s):</div>
            
            <div style="margin: 10px 0;">
                1) <span class="signature-line" style="display: inline-block; width: 300px; margin-left: 10px;"></span>
                <span style="margin-left: 20px;">Date: <span class="signature-line" style="display: inline-block; width: 150px; margin-left: 5px;"></span></span>
                <div class="field-label" style="margin-left: 20px; font-weight: normal; font-size: 8pt;">(please print name and title)</div>
            </div>

            <div style="margin: 10px 0;">
                2) <span class="signature-line" style="display: inline-block; width: 300px; margin-left: 10px;"></span>
                <span style="margin-left: 20px;">Date: <span class="signature-line" style="display: inline-block; width: 150px; margin-left: 5px;"></span></span>
                <div class="field-label" style="margin-left: 20px; font-weight: normal; font-size: 8pt;">(please print name and title)</div>
            </div>

            <p class="note" style="margin-top: 10px;">
                The foregoing signatures constitute all of the owners of the affected property necessary to convey fee title, their attorney, or their legally constituted
                attorney-in-fact. If the signature is of an attorney, then such signature is certification that the attorney represents each and every owner of the affected
                property. Please use additional signature pages, if needed.
            </p>
        </div>

        <!-- FEES SECTION -->
        <div class="fee-section">
            <div class="important">REQUIRED FILING FEES MUST BE PAID BEFORE ANY APPLICATION WILL BE ACCEPTED</div>
            
            <div class="two-column">
                <div class="left">
                    <div class="field-label">Application Fee:</div>
                    <div class="field-value"><?php echo htmlspecialchars($form_details['application_fee'] ?? ''); ?></div>
                </div>
                <div class="right">
                    <div class="field-label">Date Fees Received:</div>
                    <div class="field-value">
                        <?php 
                        if (!empty($form_details['form_paid_bool']) && $form_details['form_paid_bool']) {
                            echo htmlspecialchars($form_details['form_datetime_resolved'] ?? '');
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="field-row">
                <div class="field-label">CO Approval Date:</div>
                <div class="field-value"></div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <strong>Submit Application to:</strong><br>
            Danville-Boyle County Planning and Zoning Commission<br>
            P.O. Box 670<br>
            Danville, KY 40423-0670<br>
            859.238.1235<br>
            zoning@danvilleky.gov<br>
            www.boyleplanning.org
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// ========================================
// HELPER FUNCTIONS
// ========================================

function getCommonStyles() {
    return <<<CSS
        @page { margin: 15mm; }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11pt; 
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        h1 { 
            text-align: center; 
            font-size: 14pt; 
            font-weight: bold; 
            margin-bottom: 20px;
            text-transform: uppercase;
            line-height: 1.3;
        }
        h2 { 
            font-size: 11pt; 
            font-weight: bold;
            margin-top: 20px; 
            margin-bottom: 10px;
            background-color: #e0e0e0;
            padding: 5px 8px;
            text-transform: uppercase;
        }
        .header-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-grid td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .label { 
            font-weight: bold; 
            width: 40%;
        }
        .value {
            width: 60%;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .info-table th {
            padding: 6px 8px;
            border: 1px solid #000;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        .field-label {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
        }
        .input-box { 
            border: 1px solid #000; 
            min-height: 22px; 
            padding: 5px; 
            margin-bottom: 8px;
            background-color: #fff;
        }
        .large-input {
            min-height: 80px;
        }
        .xlarge-input {
            min-height: 120px;
        }
        .checkbox-item { 
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        .checkbox-item:before {
            content: '☐';
            position: absolute;
            left: 0;
            font-size: 14pt;
        }
        .signature-section {
            margin-top: 15px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 300px;
            margin: 0 10px;
        }
        .footer { 
            font-size: 9pt; 
            margin-top: 30px; 
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .page-break { 
            page-break-before: always; 
        }
        .note {
            font-style: italic;
            font-size: 10pt;
            margin: 10px 0;
        }
        .important-note {
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            font-size: 11pt;
        }
        ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        ul li {
            margin: 8px 0;
        }
        .fee-section {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
        }
CSS;
}

function formatAddress($form_details, $prefix) {
    $parts = [];
    
    // Different field name patterns based on prefix
    if ($prefix === 'applicant') {
        $parts = array_filter([
            $form_details['applicant_street'] ?? '',
            $form_details['applicant_city'] ?? '',
            $form_details['applicant_state'] ?? '',
            $form_details['applicant_zip'] ?? ''
        ]);
    } elseif ($prefix === 'owner') {
        $parts = array_filter([
            $form_details['owner_street'] ?? '',
            $form_details['owner_city'] ?? '',
            $form_details['owner_state'] ?? '',
            $form_details['owner_zip'] ?? ''
        ]);
    } elseif ($prefix === 'property') {
        $parts = array_filter([
            $form_details['property_street'] ?? '',
            $form_details['property_city'] ?? '',
            $form_details['property_state'] ?? '',
            $form_details['property_zip'] ?? ''
        ]);
    } elseif ($prefix === 'address') {
        $parts = array_filter([
            $form_details['address_street'] ?? '',
            $form_details['address_city'] ?? '',
            $form_details['state_code'] ?? '',
            $form_details['address_zip_code'] ?? ''
        ]);
    } elseif ($prefix === 'sp_owner') {
        $parts = array_filter([
            $form_details['sp_owner_street'] ?? '',
            $form_details['sp_owner_city'] ?? '',
            $form_details['owner_state'] ?? '',
            $form_details['sp_owner_zip_code'] ?? ''
        ]);
    } elseif ($prefix === 'sp_business') {
        $parts = array_filter([
            $form_details['sp_business_street'] ?? '',
            $form_details['sp_business_city'] ?? '',
            $form_details['business_state'] ?? '',
            $form_details['sp_business_zip_code'] ?? ''
        ]);
    } elseif ($prefix === 'property_address') {
        $parts = array_filter([
            $form_details['property_address_street'] ?? '',
            $form_details['property_address_city'] ?? '',
            $form_details['property_state_code'] ?? '',
            $form_details['property_zip_code'] ?? ''
        ]);
    } elseif ($prefix === 'orr_applicant') {
        $parts = array_filter([
            $form_details['orr_applicant_street'] ?? '',
            $form_details['orr_applicant_city'] ?? '',
            $form_details['applicant_state'] ?? '',
            $form_details['orr_applicant_zip_code'] ?? ''
        ]);
    } elseif ($prefix === 'apof_neighbor_property') {
        $parts = array_filter([
            $form_details['apof_neighbor_property_street'] ?? '',
            $form_details['apof_neighbor_property_city'] ?? '',
            $form_details['neighbor_state_code'] ?? '',
            $form_details['apof_neighbor_property_zip'] ?? ''
        ]);
    } elseif ($prefix === 'adjacent_property_owner') {
        $parts = array_filter([
            $form_details['adjacent_property_owner_street'] ?? '',
            $form_details['adjacent_property_owner_city'] ?? '',
            $form_details['owner_state_code'] ?? '',
            $form_details['adjacent_property_owner_zip'] ?? ''
        ]);
    }
    
    return htmlspecialchars(implode(', ', $parts));
}

function getFooter($form_details) {
    ob_start(); ?>
    <div class="fee-section">
        <p class="important-note">REQUIRED FILING FEES MUST BE PAID BEFORE ANY APPLICATION WILL BE ACCEPTED</p>
        <p>
            <?php if (isset($form_details['application_fee'])): ?>
                <strong>Application Fee:</strong> <?php echo htmlspecialchars($form_details['application_fee']); ?> | 
            <?php endif; ?>
            <?php if (isset($form_details['form_datetime_resolved'])): ?>
                <strong>Date Fees Received:</strong> <?php echo htmlspecialchars($form_details['form_datetime_resolved']); ?>
            <?php endif; ?>
        </p>
        <?php if (isset($form_details['form_paid_bool'])): ?>
        <p><strong>Payment Status:</strong> <?php echo $form_details['form_paid_bool'] ? 'PAID' : 'UNPAID'; ?></p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <strong>Submit Application to:</strong><br>
        Danville-Boyle County Planning and Zoning Commission<br>
        P.O. Box 670<br>
        Danville, KY 40423-0670<br>
        859.238.1235<br>
        zoning@danvilleky.gov<br>
        www.boyleplanning.org
    </div>
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