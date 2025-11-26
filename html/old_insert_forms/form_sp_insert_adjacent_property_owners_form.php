<?php
// Show all errors (for development; consider removing in production)

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
// Draft editing support (same logic format as example)
// ------------------------------
$draft_data = null;
$draft_id = null;

if (isset($_GET['draft_id'])) {
    $draft_id = (int)$_GET['draft_id'];
    // Fetch draft via a view that returns JSON columns for all arrays
    $sql = "SELECT * FROM vw_adjacent_property_owners_complete WHERE form_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $draft_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Build draft_data structure from JSON columns
        $draft_data = [
            'pva_map_codes' => !empty($row['pva_map_codes']) ? json_decode($row['pva_map_codes'], true) : [],
            'neighbor_property_locations' => !empty($row['neighbor_property_locations']) ? json_decode($row['neighbor_property_locations'], true) : [],
            'neighbor_property_deed_books' => !empty($row['neighbor_property_deed_books']) ? json_decode($row['neighbor_property_deed_books'], true) : [],
            'property_street_pg_numbers' => !empty($row['property_street_pg_numbers']) ? json_decode($row['property_street_pg_numbers'], true) : [],
            // Owners per neighbor (arrays keyed by neighbor index)
            'property_owner_names' => !empty($row['property_owner_names']) ? json_decode($row['property_owner_names'], true) : new stdClass(),
            'property_owner_streets' => !empty($row['property_owner_streets']) ? json_decode($row['property_owner_streets'], true) : new stdClass(),
            'property_owner_cities' => !empty($row['property_owner_cities']) ? json_decode($row['property_owner_cities'], true) : new stdClass(),
            'property_owner_state_codes' => !empty($row['property_owner_state_codes']) ? json_decode($row['property_owner_state_codes'], true) : new stdClass(),
            'property_owner_zips' => !empty($row['property_owner_zips']) ? json_decode($row['property_owner_zips'], true) : new stdClass(),
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

    // Collect arrays from form
    $num_neighbors = isset($_POST['num_neighbors']) ? (int)$_POST['num_neighbors'] : 0;

    $pva_map_codes = $_POST['p_PVA_map_code'] ?? [];
    $neighbor_property_locations = $_POST['p_apof_neighbor_property_location'] ?? [];
    $neighbor_property_deed_books = $_POST['p_apof_neighbor_property_deed_book'] ?? [];
    $property_street_pg_numbers = $_POST['p_apof_property_street_pg_number'] ?? [];

    // Owners nested arrays per neighbor index
    $property_owner_names_obj = new stdClass();
    $property_owner_streets_obj = new stdClass();
    $property_owner_cities_obj = new stdClass();
    $property_owner_state_codes_obj = new stdClass();
    $property_owner_zips_obj = new stdClass();

    for ($i = 0; $i < $num_neighbors; $i++) {
        $names = $_POST['p_adjacent_property_owner_name'][$i] ?? [];
        $streets = $_POST['p_adjacent_property_owner_street'][$i] ?? [];
        $cities = $_POST['p_adjacent_property_owner_city'][$i] ?? [];
        $states = $_POST['p_adjacent_state_code'][$i] ?? [];
        $zips = $_POST['p_adjacent_property_owner_zip'][$i] ?? [];

        $property_owner_names_obj->{$i} = $names;
        $property_owner_streets_obj->{$i} = $streets;
        $property_owner_cities_obj->{$i} = $cities;
        $property_owner_state_codes_obj->{$i} = $states;
        $property_owner_zips_obj->{$i} = $zips;
    }

    // Encode to JSON for procedures
    $json_pva_map_codes = json_encode($pva_map_codes);
    $json_neighbor_locations = json_encode($neighbor_property_locations);
    $json_deed_books = json_encode($neighbor_property_deed_books);
    $json_page_numbers = json_encode($property_street_pg_numbers);
    $json_owner_names = json_encode($property_owner_names_obj);
    $json_owner_streets = json_encode($property_owner_streets_obj);
    $json_owner_cities = json_encode($property_owner_cities_obj);
    $json_owner_states = json_encode($property_owner_state_codes_obj);
    $json_owner_zips = json_encode($property_owner_zips_obj);

    try {
        if ($action === 'save_draft') {
            // Save as draft (allow incomplete fields)
            $conn->query("CALL submit_draft()");

            if ($draft_id) {
                // Update existing draft
                $sql = "CALL sp_update_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $p_form_paid_bool = 0; // Consistent with example
                $stmt->bind_param(
                    'isssssssss',
                    $draft_id,
                    $json_pva_map_codes,
                    $json_neighbor_locations,
                    $json_deed_books,
                    $json_page_numbers,
                    $json_owner_names,
                    $json_owner_streets,
                    $json_owner_cities,
                    $json_owner_states,
                    $json_owner_zips
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $stmt->close();
                // Close any remaining results
                while($conn->more_results()) { $conn->next_result(); }

                // Update timestamp similar to example
                $sql = "UPDATE forms SET form_datetime_submitted = NOW() WHERE form_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $draft_id);
                $stmt->execute();
                $stmt->close();

                $conn->query("CALL draft_submitted()");
                $success = "Draft updated successfully! Draft ID: {$draft_id}";
            } else {
                // Create new draft
                $sql = "CALL sp_insert_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'sssssssss',
                    $json_pva_map_codes,
                    $json_neighbor_locations,
                    $json_deed_books,
                    $json_page_numbers,
                    $json_owner_names,
                    $json_owner_streets,
                    $json_owner_cities,
                    $json_owner_states,
                    $json_owner_zips
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                if (!$row || empty($row['form_id'])) { throw new Exception('Failed to retrieve form ID'); }
                $form_id = (int)$row['form_id'];
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }

                // Insert into incomplete drafts table (same pattern)
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
            // Submit (final submission) with basic validation
            // Require at least one neighbor with PVA code, location, and one owner name
            $has_valid_neighbor = false;
            for ($i = 0; $i < $num_neighbors; $i++) {
                $pva_ok = !empty($pva_map_codes[$i] ?? '');
                $loc_ok = !empty($neighbor_property_locations[$i] ?? '');
                $names = $property_owner_names_obj->{$i} ?? [];
                $owner_ok = is_array($names) && count(array_filter($names)) > 0;
                if ($pva_ok && $loc_ok && $owner_ok) { $has_valid_neighbor = true; break; }
            }
            if (!$has_valid_neighbor) { throw new Exception('Please provide at least one complete adjacent property (PVA code, location, and at least one owner name).'); }

            if ($draft_id) {
                // Update existing draft into final submission
                $sql = "CALL sp_update_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $p_form_paid_bool = 0;
                $stmt->bind_param(
                    'isssssssss',
                    $draft_id,
                    $json_pva_map_codes,
                    $json_neighbor_locations,
                    $json_deed_books,
                    $json_page_numbers,
                    $json_owner_names,
                    $json_owner_streets,
                    $json_owner_cities,
                    $json_owner_states,
                    $json_owner_zips
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
                // Insert new final submission
                $sql = "CALL sp_insert_adjacent_property_owners_form_json(?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
                $stmt->bind_param(
                    'sssssssss',
                    $json_pva_map_codes,
                    $json_neighbor_locations,
                    $json_deed_books,
                    $json_page_numbers,
                    $json_owner_names,
                    $json_owner_streets,
                    $json_owner_cities,
                    $json_owner_states,
                    $json_owner_zips
                );
                if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                if (!$row || empty($row['form_id'])) { throw new Exception('Failed to retrieve form ID'); }
                $form_id = (int)$row['form_id'];
                $stmt->close();
                while($conn->more_results()) { $conn->next_result(); }
            }

            // Link final form to client
            $sql = "INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $form_id, $client_id);
            $stmt->execute();
            $stmt->close();

            $success = "Form submitted successfully! Form ID: {$form_id}";
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
        if ($conn->errno) { $conn->rollback(); }
    }
}

// ------------------------------
// Fetch states for dropdown (kept from original file)
// ------------------------------
$states_result = $conn->query("SELECT state_code FROM states ORDER BY state_code");
$states = [];
if ($states_result) {
    while ($row = $states_result->fetch_assoc()) { $states[] = $row['state_code']; }
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
        .form-section { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; border-radius: 5px; background-color: #f9f9f9; }
        .form-section h5 { margin-top: 0; margin-bottom: 15px; color: #6a1b9a; }
        .neighbor-card { border: 2px solid #6a1b9a; border-radius: 8px; padding: 20px; margin-bottom: 25px; background-color: #fff; position: relative; }
        .neighbor-card-header { background-color: #6a1b9a; color: white; padding: 10px 15px; margin: -20px -20px 20px -20px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .owner-entry { border: 1px solid #d1c4e9; border-radius: 5px; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9; position: relative; }
        .owner-entry-header { background-color: #ede7f6; padding: 8px 12px; margin: -15px -15px 15px -15px; border-radius: 4px 4px 0 0; font-weight: bold; color: #6a1b9a; display: flex; justify-content: space-between; align-items: center; }
        .remove-btn { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .remove-btn:hover { background-color: #c82333; }
        .add-owner-btn { background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        .add-owner-btn:hover { background-color: #218838; }
        .add-neighbor-btn { background-color: #6a1b9a; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-bottom: 20px; }
        .add-neighbor-btn:hover { background-color: #4a148c; }
        .draft-badge { display: inline-block; background: #ffc107; color: #333; padding: 5px 10px; border-radius: 4px; font-size: 14px; font-weight: bold; margin-left: 10px; }
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
        <h2>MAILING ADDRESS FORM
            <?php if ($draft_id): ?>
            <span class="draft-badge">EDITING DRAFT #<?php echo $draft_id; ?></span>
            <?php endif; ?>
        </h2>
    </div>
    <p><a href="client_new_form.php">← Back to form selector</a></p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm">
        <form method="post" id="adjacentPropertyForm">
            <input type="hidden" name="action" id="actionInput" value="submit">
            <?php if ($draft_id): ?>
            <input type="hidden" name="draft_id" value="<?php echo $draft_id; ?>">
            <?php endif; ?>

            <input type="hidden" name="p_form_datetime_resolved" value="<?php echo date('Y-m-d H:i:s'); ?>">
            <input type="hidden" name="p_correction_form_id" value="">
            <input type="hidden" name="num_neighbors" id="num_neighbors" value="1">

            <div id="neighbors_container">
                <!-- Initial neighbor -->
                <div class="neighbor-card" data-neighbor-index="0">
                    <div class="neighbor-card-header">
                        <h5 class="mb-0">Adjacent Property #1</h5>
                        <button type="button" class="remove-btn remove-neighbor-btn" style="display: none;">Remove Property</button>
                    </div>
                    <input type="hidden" name="num_owners[0]" class="num-owners-input" value="1">
                    <div class="form-group">
                        <label>PVA MAP Code No.:</label>
                        <input type="text" class="form-control" name="p_PVA_map_code[0]" required>
                    </div>
                    <div class="form-group">
                        <label>Location of Property:</label>
                        <input type="text" class="form-control" name="p_apof_neighbor_property_location[0]" required>
                    </div>
                    <div class="form-group">
                        <label>Deed Book:</label>
                        <input type="text" class="form-control" name="p_apof_neighbor_property_deed_book[0]">
                    </div>
                    <div class="form-group">
                        <label>Page No.:</label>
                        <input type="text" class="form-control" name="p_apof_property_street_pg_number[0]">
                    </div>

                    <h6 class="mt-4 mb-3">Property Owner(s)</h6>
                    <div class="owners-container">
                        <!-- Initial owner -->
                        <div class="owner-entry" data-owner-index="0">
                            <div class="owner-entry-header">
                                <span>Owner #1</span>
                                <button type="button" class="remove-btn remove-owner-btn" style="display: none;">Remove</button>
                            </div>
                            <div class="form-group">
                                <label>Name of Property Owner:</label>
                                <input type="text" class="form-control" name="p_adjacent_property_owner_name[0][0]" required>
                            </div>
                            <div class="form-group">
                                <label>Mailing Address - Street:</label>
                                <input type="text" class="form-control" name="p_adjacent_property_owner_street[0][0]">
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>City:</label>
                                        <input type="text" class="form-control" name="p_adjacent_property_owner_city[0][0]">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>State:</label>
                                        <input type="text" class="form-control" name="p_adjacent_state_code[0][0]" maxlength="2">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Zip Code:</label>
                                        <input type="text" class="form-control" name="p_adjacent_property_owner_zip[0][0]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="add-owner-btn">+ Add Another Owner for This Property</button>
                </div>
            </div>

            <button type="button" class="add-neighbor-btn" id="add_neighbor_btn">+ Add Another Adjacent Property</button>

            <div class="text-center mt-4">
                <div class="button-group d-flex gap-2 justify-content-center flex-wrap">
                    <button class="btn btn-warning btn-lg" type="button" onclick="submitForm('save_draft')">Save as Draft</button>
                    <button class="btn btn-primary btn-lg" type="button" onclick="submitForm('submit')">Submit All Adjacent Properties</button>
                </div>
                <p class="text-center text-muted mt-3" style="font-size: 13px;">* You can save a draft with incomplete information.</p>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const neighborsContainer = document.getElementById('neighbors_container');
    const addNeighborBtn = document.getElementById('add_neighbor_btn');
    const numNeighborsInput = document.getElementById('num_neighbors');
    let neighborCounter = 0;

    // Add new neighbor property
    addNeighborBtn.addEventListener('click', function() {
        neighborCounter++;
        const newNeighborCard = createNeighborCard(neighborCounter);
        neighborsContainer.appendChild(newNeighborCard);
        numNeighborsInput.value = parseInt(numNeighborsInput.value) + 1;
        updateRemoveButtons();
    });

    // Delegate event for adding owners
    neighborsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-owner-btn')) {
            const neighborCard = e.target.closest('.neighbor-card');
            const neighborIndex = neighborCard.dataset.neighborIndex;
            const ownersContainer = neighborCard.querySelector('.owners-container');
            const numOwnersInput = neighborCard.querySelector('.num-owners-input');
            const currentOwnerCount = ownersContainer.querySelectorAll('.owner-entry').length;
            const newOwner = createOwnerEntry(neighborIndex, currentOwnerCount);
            ownersContainer.appendChild(newOwner);
            numOwnersInput.value = parseInt(numOwnersInput.value) + 1;
            updateOwnerRemoveButtons(neighborCard);
        }
    });

    // Delegate event for removing owners
    neighborsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-owner-btn')) {
            const neighborCard = e.target.closest('.neighbor-card');
            const numOwnersInput = neighborCard.querySelector('.num-owners-input');
            e.target.closest('.owner-entry').remove();
            numOwnersInput.value = parseInt(numOwnersInput.value) - 1;
            updateOwnerRemoveButtons(neighborCard);
            renumberOwners(neighborCard);
        }
    });

    // Delegate event for removing neighbors
    neighborsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-neighbor-btn')) {
            e.target.closest('.neighbor-card').remove();
            numNeighborsInput.value = parseInt(numNeighborsInput.value) - 1;
            updateRemoveButtons();
            renumberNeighbors();
        }
    });

    function createNeighborCard(index) {
        const card = document.createElement('div');
        card.className = 'neighbor-card';
        card.dataset.neighborIndex = index;
        card.innerHTML = `
            <div class="neighbor-card-header">
                <h5 class="mb-0">Adjacent Property #${index + 1}</h5>
                <button type="button" class="remove-btn remove-neighbor-btn">Remove Property</button>
            </div>
            <input type="hidden" name="num_owners[${index}]" class="num-owners-input" value="1">
            <div class="form-group">
                <label>PVA MAP Code No.:</label>
                <input type="text" class="form-control" name="p_PVA_map_code[${index}]" required>
            </div>
            <div class="form-group">
                <label>Location of Property:</label>
                <input type="text" class="form-control" name="p_apof_neighbor_property_location[${index}]" required>
            </div>
            <div class="form-group">
                <label>Deed Book:</label>
                <input type="text" class="form-control" name="p_apof_neighbor_property_deed_book[${index}]">
            </div>
            <div class="form-group">
                <label>Page No.:</label>
                <input type="text" class="form-control" name="p_apof_property_street_pg_number[${index}]">
            </div>
            <h6 class="mt-4 mb-3">Property Owner(s)</h6>
            <div class="owners-container">
                ${createOwnerEntryHTML(index, 0)}
            </div>
            <button type="button" class="add-owner-btn">+ Add Another Owner for This Property</button>
        `;
        return card;
    }

    function createOwnerEntry(neighborIndex, ownerIndex) {
        const div = document.createElement('div');
        div.className = 'owner-entry';
        div.dataset.ownerIndex = ownerIndex;
        div.innerHTML = createOwnerEntryHTML(neighborIndex, ownerIndex);
        return div;
    }

    function createOwnerEntryHTML(neighborIndex, ownerIndex) {
        return `
            <div class="owner-entry-header">
                <span>Owner #${ownerIndex + 1}</span>
                <button type="button" class="remove-btn remove-owner-btn" style="${ownerIndex === 0 ? 'display: none;' : ''}">Remove</button>
            </div>
            <div class="form-group">
                <label>Name of Property Owner:</label>
                <input type="text" class="form-control" name="p_adjacent_property_owner_name[${neighborIndex}][${ownerIndex}]" required>
            </div>
            <div class="form-group">
                <label>Mailing Address - Street:</label>
                <input type="text" class="form-control" name="p_adjacent_property_owner_street[${neighborIndex}][${ownerIndex}]">
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>City:</label>
                        <input type="text" class="form-control" name="p_adjacent_property_owner_city[${neighborIndex}][${ownerIndex}]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>State:</label>
                        <input type="text" class="form-control" name="p_adjacent_state_code[${neighborIndex}][${ownerIndex}]" maxlength="2">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Zip Code:</label>
                        <input type="text" class="form-control" name="p_adjacent_property_owner_zip[${neighborIndex}][${ownerIndex}]">
                    </div>
                </div>
            </div>
        `;
    }

    function updateRemoveButtons() {
        const neighborCards = neighborsContainer.querySelectorAll('.neighbor-card');
        neighborCards.forEach((card, index) => {
            const removeBtn = card.querySelector('.remove-neighbor-btn');
            removeBtn.style.display = neighborCards.length > 1 ? 'block' : 'none';
        });
    }

    function updateOwnerRemoveButtons(neighborCard) {
        const ownerEntries = neighborCard.querySelectorAll('.owner-entry');
        ownerEntries.forEach((entry, index) => {
            const removeBtn = entry.querySelector('.remove-owner-btn');
            removeBtn.style.display = ownerEntries.length > 1 ? 'block' : 'none';
        });
    }

    function renumberNeighbors() {
        const neighborCards = neighborsContainer.querySelectorAll('.neighbor-card');
        neighborCards.forEach((card, index) => {
            card.querySelector('.neighbor-card-header h5').textContent = `Adjacent Property #${index + 1}`;
        });
    }

    function renumberOwners(neighborCard) {
        const ownerEntries = neighborCard.querySelectorAll('.owner-entry');
        ownerEntries.forEach((entry, index) => {
            entry.querySelector('.owner-entry-header span').textContent = `Owner #${index + 1}`;
        });
    }

    // -------- Preload draft data into dynamic controls (same pattern as example) --------
    const draftData = <?php echo json_encode($draft_data ?: null); ?>;
    if (draftData) {
        // Determine number of neighbors from pva_map_codes
        const countNeighbors = Array.isArray(draftData.pva_map_codes) ? draftData.pva_map_codes.length : 0;
        // Ensure the num_neighbors hidden reflects actual count
        numNeighborsInput.value = Math.max(1, countNeighbors);
        neighborCounter = 0; // initial card is index 0

        function fillNeighbor(card, nIdx) {
            // Fill property-level fields
            const pva = (draftData.pva_map_codes && draftData.pva_map_codes[nIdx]) || '';
            const loc = (draftData.neighbor_property_locations && draftData.neighbor_property_locations[nIdx]) || '';
            const deed = (draftData.neighbor_property_deed_books && draftData.neighbor_property_deed_books[nIdx]) || '';
            const page = (draftData.property_street_pg_numbers && draftData.property_street_pg_numbers[nIdx]) || '';
            card.querySelector(`input[name="p_PVA_map_code[${nIdx}]"]`).value = pva || '';
            card.querySelector(`input[name="p_apof_neighbor_property_location[${nIdx}]"]`).value = loc || '';
            card.querySelector(`input[name="p_apof_neighbor_property_deed_book[${nIdx}]"]`).value = deed || '';
            card.querySelector(`input[name="p_apof_property_street_pg_number[${nIdx}]"]`).value = page || '';

            // Owners arrays are keyed by neighbor index
            const ownersContainer = card.querySelector('.owners-container');
            // Remove default owner if we will add more
            ownersContainer.innerHTML = '';

            const names = (draftData.property_owner_names && draftData.property_owner_names[nIdx]) || [];
            const streets = (draftData.property_owner_streets && draftData.property_owner_streets[nIdx]) || [];
            const cities = (draftData.property_owner_cities && draftData.property_owner_cities[nIdx]) || [];
            const states = (draftData.property_owner_state_codes && draftData.property_owner_state_codes[nIdx]) || [];
            const zips = (draftData.property_owner_zips && draftData.property_owner_zips[nIdx]) || [];

            const ownersCount = Math.max(1, (Array.isArray(names) ? names.length : 0));
            // Set hidden num_owners
            const numOwnersInput = card.querySelector('.num-owners-input');
            numOwnersInput.value = ownersCount;

            for (let o = 0; o < ownersCount; o++) {
                const ownerEntry = createOwnerEntry(nIdx, o);
                ownersContainer.appendChild(ownerEntry);
                ownerEntry.querySelector(`input[name="p_adjacent_property_owner_name[${nIdx}][${o}]"]`).value = (names[o] || '');
                ownerEntry.querySelector(`input[name="p_adjacent_property_owner_street[${nIdx}][${o}]"]`).value = (streets[o] || '');
                ownerEntry.querySelector(`input[name="p_adjacent_property_owner_city[${nIdx}][${o}]"]`).value = (cities[o] || '');
                ownerEntry.querySelector(`input[name="p_adjacent_state_code[${nIdx}][${o}]"]`).value = (states[o] || '');
                ownerEntry.querySelector(`input[name="p_adjacent_property_owner_zip[${nIdx}][${o}]"]`).value = (zips[o] || '');
            }
            updateOwnerRemoveButtons(card);
        }

        // Fill initial neighbor card
        const firstCard = neighborsContainer.querySelector('.neighbor-card');
        fillNeighbor(firstCard, 0);

        // Add and fill remaining neighbors
        for (let i = 1; i < countNeighbors; i++) {
            neighborCounter = i;
            const card = createNeighborCard(i);
            neighborsContainer.appendChild(card);
            fillNeighbor(card, i);
        }
        updateRemoveButtons();
        renumberNeighbors();
    }
});

function submitForm(action) {
    document.getElementById('actionInput').value = action;
    document.getElementById('adjacentPropertyForm').submit();
}
</script>
</body>
</html>
