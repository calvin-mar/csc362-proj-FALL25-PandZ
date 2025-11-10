<?php
session_start(); // Ensure session is started for login functions
require_once 'config.php'; // Contains getDBConnection(), requireLogin(), getUserType(), getUserId()

requireLogin();

if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

$client_id = getUserId(); // Assuming getUserId() returns the client_id for the logged-in user
$success = '';
$error = '';

// Default values for form fields for repopulation in case of error
$p_form_datetime_resolved = null; // This field is typically set by govt_worker
$p_correction_form_id = null; // This field is typically set by govt_worker
$p_aar_submit_date = date('Y-m-d'); // Pre-fill with current date
$p_aar_street_address = '';
$p_aar_city_address = '';
$p_state_code = ''; // Or default to 'KY'
$p_aar_zip_code = '';
$p_aar_property_location = '';
$p_aar_official_decision = '';
$p_aar_relevant_provisions = '';
$p_aar_hearing_date = null; // This field is typically set by govt_worker
$p_aar_appellant_first_name = '';
$p_aar_appellant_last_name = '';
$p_adjacent_property_owner_street = '';
$p_adjacent_property_owner_city = '';
$p_adjacent_property_owner_state_code = '';
$p_adjacent_property_owner_zip = '';
$p_aar_property_owner_first_name = '';
$p_aar_property_owner_last_name = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize/validate input
    // Note: p_form_datetime_resolved and p_correction_form_id are usually set by government workers,
    // so clients might not provide them. Set to null if not present/empty.
    // Assuming you have proper validation/sanitization functions in config.php or elsewhere.
    $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
    $p_form_paid_bool = 0; // Default to unpaid on submission
    $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? (int)$_POST['p_correction_form_id'] : null;

    // Client-provided fields
    $p_aar_submit_date = $_POST['p_aar_submit_date'] ?? date('Y-m-d'); // Default to current date if not provided
    $p_aar_hearing_date = $_POST['p_aar_hearing_date'] ?? null; // Likely filled by government

    $p_aar_appellant_first_name = trim($_POST['p_aar_appellant_first_name'] ?? '');
    $p_aar_appellant_last_name = trim($_POST['p_aar_appellant_last_name'] ?? '');

    $p_aar_property_owner_first_name = trim($_POST['p_aar_property_owner_first_name'] ?? '');
    $p_aar_property_owner_last_name = trim($_POST['p_aar_property_owner_last_name'] ?? '');

    $p_aar_street_address = trim($_POST['p_aar_street_address'] ?? '');
    $p_aar_city_address = trim($_POST['p_aar_city_address'] ?? '');
    $p_state_code = trim($_POST['p_state_code'] ?? '');
    $p_aar_zip_code = trim($_POST['p_aar_zip_code'] ?? '');

    $p_aar_property_location = trim($_POST['p_aar_property_location'] ?? '');
    $p_aar_official_decision = trim($_POST['p_aar_official_decision'] ?? '');
    $p_aar_relevant_provisions = trim($_POST['p_aar_relevant_provisions'] ?? '');

    $p_adjacent_property_owner_street = trim($_POST['p_adjacent_property_owner_street'] ?? '');
    $p_adjacent_property_owner_city = trim($_POST['p_adjacent_property_owner_city'] ?? '');
    $p_adjacent_property_owner_state_code = trim($_POST['p_adjacent_property_owner_state_code'] ?? '');
    $p_adjacent_property_owner_zip = trim($_POST['p_adjacent_property_owner_zip'] ?? '');

    // Basic validation (add more robust validation as needed)
    if (empty($p_aar_appellant_first_name) || empty($p_aar_appellant_last_name)) {
        $error = "Appellant's First and Last Name are required.";
    } elseif (empty($p_aar_property_owner_first_name) || empty($p_aar_property_owner_last_name)) {
        $error = "Property Owner's First and Last Name are required.";
    } elseif (empty($p_aar_street_address) || empty($p_aar_city_address) || empty($p_state_code) || empty($p_aar_zip_code)) {
        $error = "Full address (Street, City, State, Zip) is required.";
    } elseif (empty($p_aar_property_location)) {
        $error = "Location of Property is required.";
    } elseif (empty($p_aar_official_decision)) {
        $error = "Decision of Official is required.";
    } elseif (empty($p_aar_relevant_provisions)) {
        $error = "Relevant Provisions of Zoning Ordinance are required.";
    }

    if (empty($error)) {
        // Prepare the CALL statement for your stored procedure
        $sql = "CALL sp_insert_administrative_appeal_request(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 19 params
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
        } else {
            // Adjust the types string and parameter count based on your SP definition
            // The previous types string 'isisssssssssssssss' was 18 's' and 1 'i' = 19 parameters.
            // Let's re-verify the order and types from your SP.
            // sp_insert_administrative_appeal_request(
            // 1. p_form_datetime_resolved (s)
            // 2. p_form_paid_bool (i)
            // 3. p_aar_hearing_date (s)
            // 4. p_aar_submit_date (s)
            // 5. p_aar_street_address (s)
            // 6. p_aar_city_address (s)
            // 7. p_state_code (s)
            // 8. p_aar_zip_code (s)
            // 9. p_aar_property_location (s)
            // 10. p_aar_official_decision (s)
            // 11. p_aar_relevant_provisions (s)
            // 12. p_aar_appellant_first_name (s)
            // 13. p_aar_appellant_last_name (s)
            // 14. p_adjacent_property_owner_street (s)
            // 15. p_adjacent_property_owner_city (s)
            // 16. p_adjacent_property_owner_state_code (s)
            // 17. p_adjacent_property_owner_zip (s)
            // 18. p_aar_property_owner_first_name (s)
            // 19. p_aar_property_owner_last_name (s)
            // )
            $types = 'sisisssssssssssssss'; // 1 (s) + 1 (i) + 17 (s) = 19 parameters

            $bind_values = [];
            $bind_values[] = &$p_form_datetime_resolved;
            $bind_values[] = &$p_form_paid_bool; // 0 for unpaid on submission
            $bind_values[] = &$p_aar_hearing_date; // This will likely be null from client
            $bind_values[] = &$p_aar_submit_date;
            $bind_values[] = &$p_aar_street_address;
            $bind_values[] = &$p_aar_city_address;
            $bind_values[] = &$p_state_code;
            $bind_values[] = &$p_aar_zip_code;
            $bind_values[] = &$p_aar_property_location;
            $bind_values[] = &$p_aar_official_decision;
            $bind_values[] = &$p_aar_relevant_provisions;
            $bind_values[] = &$p_aar_appellant_first_name;
            $bind_values[] = &$p_aar_appellant_last_name;
            $bind_values[] = &$p_adjacent_property_owner_street; // This is an adjacent owner, not the current property owner
            $bind_values[] = &$p_adjacent_property_owner_city;
            $bind_values[] = &$p_adjacent_property_owner_state_code;
            $bind_values[] = &$p_adjacent_property_owner_zip;
            $bind_values[] = &$p_aar_property_owner_first_name; // This is the current property owner
            $bind_values[] = &$p_aar_property_owner_last_name; // This is the current property owner

            array_unshift($bind_values, $types);
            $bindResult = call_user_func_array([$stmt, 'bind_param'], $bind_values);

            if ($bindResult === false) {
                $error = 'Bind failed: ' . $stmt->error;
            } else {
                if (!$stmt->execute()) {
                    $error = 'Execute failed: ' . $stmt->error;
                } else {
                    // Get the form_id generated by the stored procedure (if it returns one)
                    // Or retrieve it using $conn->insert_id if the SP inserts into forms directly
                    // and you're not getting it back from the SP
                    $new_form_id = $conn->insert_id; // mysqli gives last insert ID on connection

                    // Also link client to form
                    $link_sql = "INSERT INTO client_forms (client_id, form_id) VALUES (?, ?)";
                    $link_stmt = $conn->prepare($link_sql);
                    if ($link_stmt) {
                        $link_stmt->bind_param("ii", $client_id, $new_form_id);
                        if (!$link_stmt->execute()) {
                            $error .= " Failed to link client to form: " . $link_stmt->error;
                        }
                        $link_stmt->close();
                    } else {
                        $error .= " Failed to prepare client form link: " . $conn->error;
                    }
                    $success = 'Form submitted successfully! Your Form ID is ' . $new_form_id;

                    // Clear form fields after successful submission (except date)
                    $p_form_datetime_resolved = null;
                    $p_correction_form_id = null;
                    // $p_aar_submit_date = date('Y-m-d'); // Keep current date
                    $p_aar_street_address = '';
                    $p_aar_city_address = '';
                    $p_state_code = '';
                    $p_aar_zip_code = '';
                    $p_aar_property_location = '';
                    $p_aar_official_decision = '';
                    $p_aar_relevant_provisions = '';
                    $p_aar_hearing_date = null;
                    $p_aar_appellant_first_name = '';
                    $p_aar_appellant_last_name = '';
                    $p_adjacent_property_owner_street = '';
                    $p_adjacent_property_owner_city = '';
                    $p_adjacent_property_owner_state_code = '';
                    $p_adjacent_property_owner_zip = '';
                    $p_aar_property_owner_first_name = '';
                    $p_aar_property_owner_last_name = '';
                }
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application for Administrative Appeal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }
        .navbar {
            background-color: #dc3545; /* Reddish color for the commission */
            color: white;
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #ccc; /* Mimic the line in PDF */
        }
        .header-section h1 {
            color: #dc3545; /* Reddish text */
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header-section p {
            font-size: 14px;
            margin: 0;
        }
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
            text-transform: uppercase;
        }
        .form-section-label {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #999; /* Underline for sections */
        }
        .form-group-line {
            display: flex;
            align-items: flex-end; /* Align labels/inputs to the bottom */
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 5px; /* Space for the underline */
            border-bottom: 1px solid #333; /* The line itself */
        }
        .form-group-line label {
            flex-shrink: 0; /* Don't shrink label */
            margin-right: 10px;
            font-weight: normal;
            color: #333;
            font-size: 14px;
        }
        .form-group-line input[type="text"],
        .form-group-line input[type="date"],
        .form-group-line textarea {
            flex-grow: 1;
            border: 1px solid #ccc; /* Add a visible outline */
            border-radius: 4px;     /* Slightly rounded corners */
            padding: 4px 8px;       /* Add spacing inside box */
            background-color: #fff; /* White background for contrast */
            font-size: 14px;
            height: auto;
            resize: vertical;
            transition: border-color 0.2s ease;
        }

        /* Highlight when focused */
        .form-group-line input:focus,
        .form-group-line textarea:focus {
            outline: none;
            border-color: #dc3545; /* Red highlight matches the page theme */
            box-shadow: 0 0 3px rgba(220, 53, 69, 0.3);
        }

        /* Adjust multi-line textareas for better fit */
        .form-group-line.multi-line textarea {
            width: 100%;
            min-height: 60px;
        }
        .form-group-line.multi-line textarea {
            width: 100%;
            min-height: 50px;
        }
        .small-text {
            font-size: 12px;
            color: #666;
            margin-top: -10px; /* Pull it up closer to the field above */
            margin-bottom: 10px;
        }
        .footer-info {
            text-align: center;
            margin-top: 40px;
            font-size: 13px;
            color: #555;
        }
        .footer-info p {
            margin: 3px 0;
        }
        .submit-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal – Administrative Appeal</span>
  </div>
</nav>
<p><a href="client_new_form.php">&larr; Back to form selector</a></p>
<div class="form-container">
    <div class="header-section">
        <p style="text-align: left; font-size: 10px;"></p>
        <img src="path/to/DBCPZ_logo.png" alt="DBCPZ Logo" style="height: 50px; margin-bottom: 10px;">
        <h1 class="mb-0">Danville-Boyle County Planning & Zoning Commission</h1>
        <p>445 West Main Street P.O. Box 670</p>
        <p>Danville, Kentucky 40423</p>
    </div>

    <h2 class="form-title">APPLICATION FOR ADMINISTRATIVE APPEAL</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group-line">
            <label for="board_adjustments">BOARD OF ADJUSTMENTS</label>
            <!-- This field is usually a dropdown or selection in a real app, placeholder for now -->
            <input type="text" id="board_adjustments" name="board_adjustments" value="" readonly>
        </div>
        <div class="form-group-line">
            <label for="p_aar_hearing_date">DATE OF HEARING:</label>
            <input type="date" id="p_aar_hearing_date" name="p_aar_hearing_date" value="<?php echo htmlspecialchars($p_aar_hearing_date ?? ''); ?>">
        </div>
        <p style="text-align: center; margin-top: 20px; margin-bottom: 20px; font-size: 12px;">**************************************************************************************************</p>

        <div class="form-group-line">
            <label for="p_aar_appellant_first_name">APPELLANT(S):</label>
            <input type="text" id="p_aar_appellant_first_name" name="p_aar_appellant_first_name" placeholder="First Name" value="<?php echo htmlspecialchars($p_aar_appellant_first_name); ?>" required>
            <input type="text" id="p_aar_appellant_last_name" name="p_aar_appellant_last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($p_aar_appellant_last_name); ?>" required style="margin-left: 10px;">
        </div>
        <div class="form-group-line">
            <label for="p_aar_submit_date">DATE:</label>
            <input type="date" id="p_aar_submit_date" name="p_aar_submit_date" value="<?php echo htmlspecialchars($p_aar_submit_date); ?>" required>
        </div>

        <div class="form-group-line multi-line">
            <label for="p_aar_property_owner_first_name">PROPERTY OWNER(S) OR BUSINESS ENTITY:</label>
            <input type="text" id="p_aar_property_owner_first_name" name="p_aar_property_owner_first_name" placeholder="First Name (or Business Name)" value="<?php echo htmlspecialchars($p_aar_property_owner_first_name); ?>" required>
            <input type="text" id="p_aar_property_owner_last_name" name="p_aar_property_owner_last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($p_aar_property_owner_last_name); ?>" required style="margin-top: 5px; margin-left: 0;">
        </div>
        <p class="small-text">(Please List Names of All Owners, Directors and/or Shareholders)</p>

        <div class="form-group-line multi-line">
            <label>ADDRESS:</label>
            <input type="text" id="p_aar_street_address" name="p_aar_street_address" placeholder="Street Address" value="<?php echo htmlspecialchars($p_aar_street_address); ?>" required>
            <input type="text" id="p_aar_city_address" name="p_aar_city_address" placeholder="City" value="<?php echo htmlspecialchars($p_aar_city_address); ?>" required style="margin-top: 5px;">
            <input type="text" id="p_state_code" name="p_state_code" placeholder="State (e.g., KY)" value="<?php echo htmlspecialchars($p_state_code); ?>" maxlength="2" required style="margin-top: 5px;">
            <input type="text" id="p_aar_zip_code" name="p_aar_zip_code" placeholder="Zip Code" value="<?php echo htmlspecialchars($p_aar_zip_code); ?>" required style="margin-top: 5px;">
        </div>

        <div class="form-group-line multi-line">
            <label for="p_aar_property_location">LOCATION OF PROPERTY:</label>
            <textarea id="p_aar_property_location" name="p_aar_property_location" rows="3" required><?php echo htmlspecialchars($p_aar_property_location); ?></textarea>
        </div>

        <div class="form-group-line multi-line">
            <label for="p_aar_official_decision">DECISION OF OFFICIAL FROM WHICH APPEAL IS MADE:</label>
            <textarea id="p_aar_official_decision" name="p_aar_official_decision" rows="3" required><?php echo htmlspecialchars($p_aar_official_decision); ?></textarea>
        </div>

        <div class="form-group-line multi-line">
            <label for="p_aar_relevant_provisions">PROVISIONS OF ZONING ORDINANCE IN RELATION TO APPEAL:</label>
            <textarea id="p_aar_relevant_provisions" name="p_aar_relevant_provisions" rows="3" required><?php echo htmlspecialchars($p_aar_relevant_provisions); ?></textarea>
        </div>

        <!-- Appellant(s) Name, Signature, Date at the bottom -->
        <div style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 20px;">
            <div class="form-group-line">
                <label for="p_aar_appellant_bottom_name">APPELLANT(S) NAME:</label>
                <!-- Assuming this is a repetition of the appellant's full name -->
                <input type="text" id="p_aar_appellant_bottom_name" name="p_aar_appellant_bottom_name" value="<?php echo htmlspecialchars($p_aar_appellant_first_name . ' ' . $p_aar_appellant_last_name); ?>" readonly>
            </div>
            <div class="form-group-line">
                <label for="appellant_signature">APPELLANT(S) SIGNATURE:</label>
                <!-- In a real web form, this would be a drawing pad or a declaration, for now, just a text input -->
                <input type="text" id="appellant_signature" name="appellant_signature" placeholder="Signature (Type your name to acknowledge)" value="">
            </div>
            <div class="form-group-line">
                <label for="p_aar_submit_date_bottom">DATE:</label>
                <input type="date" id="p_aar_submit_date_bottom" name="p_aar_submit_date_bottom" value="<?php echo htmlspecialchars($p_aar_submit_date); ?>" readonly>
            </div>
        </div>

        <div class="submit-section">
            <button class="btn btn-danger" type="submit">Submit Application</button>
        </div>
    </form>

    <div class="footer-info">
        <p>Phone: 859.238.1235</p>
        <p>www.boyleplanning.org</p>
    </div>
</div>
</body>
</html>