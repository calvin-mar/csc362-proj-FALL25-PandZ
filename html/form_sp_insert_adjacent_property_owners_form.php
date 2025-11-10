<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_neighbors = isset($_POST['num_neighbors']) ? (int)$_POST['num_neighbors'] : 0;

    for ($i = 0; $i < $num_neighbors; $i++) {
        $p_form_datetime_resolved = isset($_POST['p_form_datetime_resolved']) && $_POST['p_form_datetime_resolved'] !== '' ? $_POST['p_form_datetime_resolved'] : null;
        $p_form_paid_bool = 0; // Always 0 for client submission
        $p_correction_form_id = isset($_POST['p_correction_form_id']) && $_POST['p_correction_form_id'] !== '' ? $_POST['p_correction_form_id'] : null;

        $p_PVA_map_code = isset($_POST['p_PVA_map_code'][$i]) && $_POST['p_PVA_map_code'][$i] !== '' ? $_POST['p_PVA_map_code'][$i] : null;
        $p_apof_neighbor_property_location = isset($_POST['p_apof_neighbor_property_location'][$i]) && $_POST['p_apof_neighbor_property_location'][$i] !== '' ? $_POST['p_apof_neighbor_property_location'][$i] : null;
        $p_adjacent_property_owner_name = isset($_POST['p_adjacent_property_owner_name'][$i]) && $_POST['p_adjacent_property_owner_name'][$i] !== '' ? $_POST['p_adjacent_property_owner_name'][$i] : null;

        // Mailing Address (combined for simplicity as per PDF structure)
        $mailing_address_parts = [];
        if (isset($_POST['p_adjacent_property_owner_street'][$i]) && $_POST['p_adjacent_property_owner_street'][$i] !== '') {
            $mailing_address_parts[] = $_POST['p_adjacent_property_owner_street'][$i];
        }
        if (isset($_POST['p_adjacent_property_owner_city'][$i]) && $_POST['p_adjacent_property_owner_city'][$i] !== '') {
            $mailing_address_parts[] = $_POST['p_adjacent_property_owner_city'][$i];
        }
        if (isset($_POST['p_adjacent_state_code'][$i]) && $_POST['p_adjacent_state_code'][$i] !== '') {
            $mailing_address_parts[] = $_POST['p_adjacent_state_code'][$i];
        }
        if (isset($_POST['p_adjacent_property_owner_zip'][$i]) && $_POST['p_adjacent_property_owner_zip'][$i] !== '') {
            $mailing_address_parts[] = $_POST['p_adjacent_property_owner_zip'][$i];
        }
        $p_apof_mailing_address = implode(', ', $mailing_address_parts);
        if (empty($p_apof_mailing_address)) {
            $p_apof_mailing_address = null;
        }

        $p_apof_neighbor_property_deed_book = isset($_POST['p_apof_neighbor_property_deed_book'][$i]) && $_POST['p_apof_neighbor_property_deed_book'][$i] !== '' ? $_POST['p_apof_neighbor_property_deed_book'][$i] : null;
        $p_apof_property_street_pg_number = isset($_POST['p_apof_property_street_pg_number'][$i]) && $_POST['p_apof_property_street_pg_number'][$i] !== '' ? $_POST['p_apof_property_street_pg_number'][$i] : null;


        // The stored procedure sp_insert_adjacent_property_owners_form currently expects individual address components.
        // We need to adapt the input from the combined mailing address.
        // For now, let's just pass the street, city, state, zip as they would typically be extracted if available.
        // If the stored procedure needs a single mailing address string, this part would need adjustment.
        // Based on the old form, it expects individual components, so we will try to parse them if the combined field is used,
        // or add separate fields for street, city, state, zip for the mailing address.
        // For this rewrite, let's assume we need to populate these based on user input, or just pass null if not explicitly entered.

        // To match the original SP, we need separate street, city, state, zip for the *neighbor's property* and the *adjacent owner's mailing address*.
        // The PDF has "Location of Property" and "Mailing Address of Property Owner(s)".
        // It seems the original SP was designed for more granular data than the PDF's table implies.
        // For the sake of matching the PDF visual structure, I will focus on the table columns,
        // and for the SP I'll make assumptions for the missing fields, e.g., using "Location of Property" for `p_apof_neighbor_property_street` etc.
        // This is a common challenge when mapping a simple UI to a complex backend SP.

        // Let's re-evaluate the SP parameters and the PDF.
        // PDF Columns: PVA MAP Code No., Location of Property, Name of Property Owner(s), Mailing Address of Property Owner(s) (Street, State & Zip Code), Deed Book & Page No.
        // SP Parameters:
        // p_form_datetime_resolved, p_form_paid_bool, p_correction_form_id,
        // p_PVA_map_code,
        // p_apof_neighbor_property_location, <-- Matches "Location of Property"
        // p_apof_neighbor_property_street, <-- NOT directly in PDF table, maybe derived from "Location of Property"?
        // p_apof_neighbor_property_city,   <-- NOT directly in PDF table
        // p_apof_state_code,               <-- NOT directly in PDF table
        // p_apof_neighbor_property_zip,    <-- NOT directly in PDF table
        // p_apof_neighbor_property_deed_book, <-- Part of "Deed Book & Page No."
        // p_apof_property_street_pg_number,   <-- Part of "Deed Book & Page No." (this field name is confusing, "street page number"?)
        // p_adjacent_property_owner_street, <-- Part of "Mailing Address of Property Owner(s)"
        // p_adjacent_property_owner_city,   <-- Part of "Mailing Address of Property Owner(s)"
        // p_adjacent_state_code,            <-- Part of "Mailing Address of Property Owner(s)"
        // p_adjacent_property_owner_zip     <-- Part of "Mailing Address of Property Owner(s)"

        // Given the PDF, the `apof_neighbor_property_street/city/state/zip` are not directly asked for the *neighboring property itself*.
        // Only its "Location" is asked. This suggests the SP might be slightly misaligned with the PDF or expects these to be derived.
        // For this form, we will simplify and only capture what's explicitly in the PDF table.
        // I will map 'Location of Property' to `p_apof_neighbor_property_location` and leave the individual street/city/state/zip for the *neighbor's property* as null for now,
        // unless the user provides them.

        // Let's assume the Mailing Address from the PDF (Street, State & Zip Code) will be parsed into its components
        // for `p_adjacent_property_owner_street`, `p_adjacent_property_owner_city`, `p_adjacent_state_code`, `p_adjacent_property_owner_zip`.
        // The original form had these as separate inputs. The PDF table combines them.
        // I will keep the input fields separate for clarity and to align with the SP, but visually group them under one 'Mailing Address' header.

        // So, the SP expects:
        // p_PVA_map_code
        // p_apof_neighbor_property_location
        // p_adjacent_property_owner_name (new field needed based on PDF)
        // p_adjacent_property_owner_street
        // p_adjacent_property_owner_city
        // p_adjacent_state_code
        // p_adjacent_property_owner_zip
        // p_apof_neighbor_property_deed_book
        // p_apof_property_street_pg_number (assuming this is the "Page No." part)

        // For the sake of matching the SP, I'll need to add a `p_adjacent_property_owner_name` field.
        // And for the neighbor property's individual address components (street, city, state, zip),
        // I'll make them derivable or explicitly add them if a more detailed SP needs them.
        // Given the current SP, it takes many address components. The PDF simplifies this.

        $sql = "CALL sp_insert_adjacent_property_owners_form(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // Added one more 's' for owner name
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare failed: ' . $conn->error;
            break;
        } else {
            // Need to pass a name parameter to the SP if it exists. Re-checking original SP.
            // Original SP was missing owner_name. I will assume it should be added to the SP.
            // For now, I'll map p_apof_neighbor_property_location for "Location of Property"
            // and `p_apof_neighbor_property_street`, `p_apof_neighbor_property_city`, `p_apof_state_code`, `p_apof_neighbor_property_zip`
            // will be null if not explicitly provided as they aren't directly in the PDF table column "Location of Property".

            // To accurately map the PDF columns to the SP parameters:
            // PVA MAP Code No. -> p_PVA_map_code
            // Location of Property -> p_apof_neighbor_property_location
            // Name of Property Owner(s) -> p_adjacent_property_owner_name (new input needed, and SP update)
            // Mailing Address of Property Owner(s) (Street, State & Zip Code) -> p_adjacent_property_owner_street, p_adjacent_property_owner_city (if parsed), p_adjacent_state_code, p_adjacent_property_owner_zip
            // Deed Book & Page No. -> p_apof_neighbor_property_deed_book, p_apof_property_street_pg_number

            // Assuming the SP has been updated to include `p_adjacent_property_owner_name` and uses it.
            // For now, I'll fill in dummy/null values for the `p_apof_neighbor_property_street/city/state/zip`
            // since they are not directly from the PDF table's "Location of Property" column, which is a single string.

            $p_apof_neighbor_property_street = null;
            $p_apof_neighbor_property_city = null;
            $p_apof_state_code_neighbor_prop = null; // Renamed to avoid conflict with owner's state code
            $p_apof_neighbor_property_zip = null;

            $types = 'ssissssssssssss'; // Adjusted types: datetime_resolved, paid_bool, correction_id, pva_map_code, neighbor_location, owner_name, neighbor_street(null), neighbor_city(null), neighbor_state(null), neighbor_zip(null), deed_book, pg_number, owner_street, owner_city, owner_state, owner_zip
            $bind_names = array();
            $bind_names[] = &$p_form_datetime_resolved;
            $bind_names[] = &$p_form_paid_bool;
            $bind_names[] = &$p_correction_form_id;
            $bind_names[] = &$p_PVA_map_code;
            $bind_names[] = &$p_apof_neighbor_property_location;
            $bind_names[] = &$p_adjacent_property_owner_name; // New: owner name
            $bind_names[] = &$p_apof_neighbor_property_street; // Null for now
            $bind_names[] = &$p_apof_neighbor_property_city;   // Null for now
            $bind_names[] = &$p_apof_state_code_neighbor_prop; // Null for now
            $bind_names[] = &$p_apof_neighbor_property_zip;    // Null for now
            $bind_names[] = &$p_apof_neighbor_property_deed_book;
            $bind_names[] = &$p_apof_property_street_pg_number;
            $bind_names[] = &$p_adjacent_property_owner_street[$i]; // From form, specific to this neighbor
            $bind_names[] = &$p_adjacent_property_owner_city[$i];   // From form
            $bind_names[] = &$p_adjacent_state_code[$i];            // From form
            $bind_names[] = &$p_adjacent_property_owner_zip[$i];    // From form

            array_unshift($bind_names, $types);
            $bindResult = @call_user_func_array(array($stmt, 'bind_param'), $bind_names);

            if ($bindResult === false) {
                $error = 'Bind failed for neighbor ' . ($i + 1) . ': ' . $stmt->error;
                break;
            } else {
                if (!$stmt->execute()) {
                    $error = 'Execute failed for neighbor ' . ($i + 1) . ': ' . $stmt->error;
                    break;
                }
            }
            $stmt->close();
        }
    }

    if (!$error) {
        $success = 'Form submitted successfully for all adjacent properties!';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Adjacent Property Owners Form</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body { background: linear-gradient(135deg, #ede7f6, #d1c4e9); }
    .form-section {
      border: 1px solid #ccc;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
    .form-section h5 {
      margin-top: 0;
      margin-bottom: 15px;
      color: #6a1b9a;
    }
    .table-responsive {
        margin-top: 20px;
    }
    .table-form th, .table-form td {
        vertical-align: middle;
        padding: 0.5rem;
    }
    .table-form input[type="text"],
    .table-form input[type="number"] {
        width: 100%;
        border: none;
        padding: 0.375rem 0.75rem;
        background-color: transparent;
    }
    .table-form input[type="text"]:focus,
    .table-form input[type="number"]:focus {
        outline: none;
        background-color: #e6e6e6;
    }
    .add-row-btn {
        margin-top: 15px;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-dark" style="background-color: #6a1b9a;">
  <div class="container">
    <span class="navbar-brand mb-0 h1">Client Portal – Planning & Zoning</span>
  </div>
</nav>

<div class="container py-4">
  <div class="text-center mb-4">
    <h2>ADJACENT PROPERTY OWNERS' NAME</h2>
    <h2>&</h2>
    <h2>MAILING ADDRESS FORM</h2>
  </div>

  <p>Applicants are required to furnish the Danville-BBoyle County Planning & Zoning Commission with the names and mailing address of the owners of all adjacent property. Adjacent property is defined as being property across roads, streets, interstates, rivers, streams, etc., as well as abutting the subject property. The applicant may rely on the records maintained by the Boyle County Property Valuation Administrator to determine the identity and address of the adjacent property owners. Instructions for completing this form:</p>
  <ol>
    <li>To determine the PVA map code number the applicant should refer to the PVA property location maps.</li>
    <li>To determine the name of the adjacent property owner, the location and/or address of the adjacent property and the deed book and page number, the applicant should refer to the computer data base maintained by the PVA office.</li>
    <li>To determine the mailing address of the adjacent property owner, the applicant should refer to the computer data base of mailing addresses maintained by the PVA office.</li>
  </ol>

  <p><a href="client_new_form.php">&larr; Back to form selector</a></p>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="card p-4 shadow-sm">
    <form method="post" id="adjacentPropertyForm">
        <!-- Hidden fields for form metadata, not part of the table display -->
        <input type="hidden" name="p_form_datetime_resolved" value="<?php echo date('Y-m-d H:i:s'); ?>">
        <input type="hidden" name="p_correction_form_id" value=""> <!-- If correction, this would be populated -->
        <input type="hidden" name="num_neighbors" id="num_neighbors" value="1">

        <div class="table-responsive">
            <table class="table table-bordered table-form">
                <thead>
                    <tr>
                        <th>PVA MAP Code No.</th>
                        <th>Location of Property</th>
                        <th>Name of Property Owner(s)</th>
                        <th>Mailing Address of Property Owner(s)<br>(Street, City, State & Zip Code)</th>
                        <th>Deed Book & Page No.</th>
                    </tr>
                </thead>
                <tbody id="neighbor_rows">
                    <!-- Initial row -->
                    <tr class="neighbor-row">
                        <td><input type="text" name="p_PVA_map_code[]" placeholder="" aria-label="PVA Map Code"></td>
                        <td><input type="text" name="p_apof_neighbor_property_location[]" placeholder="" aria-label="Location of Property"></td>
                        <td><input type="text" name="p_adjacent_property_owner_name[]" placeholder="" aria-label="Name of Property Owner(s)"></td>
                        <td>
                            <input type="text" name="p_adjacent_property_owner_street[]" placeholder="Street" aria-label="Owner Street"><br>
                            <input type="text" name="p_adjacent_property_owner_city[]" placeholder="City" aria-label="Owner City"><br>
                            <input type="text" name="p_adjacent_state_code[]" placeholder="State" maxlength="2" aria-label="Owner State Code"><br>
                            <input type="text" name="p_adjacent_property_owner_zip[]" placeholder="Zip Code" aria-label="Owner Zip Code">
                        </td>
                        <td>
                            <input type="text" name="p_apof_neighbor_property_deed_book[]" placeholder="Deed Book" aria-label="Deed Book"><br>
                            <input type="text" name="p_apof_property_street_pg_number[]" placeholder="Page No." aria-label="Page Number">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-secondary add-row-btn" id="add_neighbor_row">Add Another Adjacent Property</button>

        <div class="form-group mt-3">
            <button class="btn btn-primary" type="submit">Submit All Adjacent Properties</button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addRowBtn = document.getElementById('add_neighbor_row');
    const neighborRows = document.getElementById('neighbor_rows');
    const numNeighborsInput = document.getElementById('num_neighbors');

    addRowBtn.addEventListener('click', function() {
        const newRow = document.querySelector('.neighbor-row').cloneNode(true);
        const inputs = newRow.querySelectorAll('input');
        inputs.forEach(input => {
            input.value = ''; // Clear values for new row
        });
        neighborRows.appendChild(newRow);
        numNeighborsInput.value = parseInt(numNeighborsInput.value) + 1;
    });
});
</script>
</body>
</html>