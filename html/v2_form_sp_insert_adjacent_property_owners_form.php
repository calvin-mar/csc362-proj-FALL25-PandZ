<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


require_once 'config.php';
require_once 'form_insert_functions.php';
require_once 'form_update_functions.php';
requireLogin();
if (getUserType() != 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = getUserId();
// Get relevant data if starting from a draft already
$draft_data = null;
$draft_id = null;

if (isset($_GET['draft_id'])) {
    echo "Loading draft ID: " . htmlspecialchars($_GET['draft_id']);
    $draft_id = (int)$_GET['draft_id'];
    
    // Fetch neighbor properties
    $sql_neighbors = "
        SELECT n.neighbor_id, n.PVA_map_code, n.apof_neighbor_property_location, 
               n.apof_neighbor_property_deed_book, n.apof_property_street_pg_number
        FROM adjacent_neighbors an
        JOIN apof_neighbors n ON an.neighbor_id = n.neighbor_id
        WHERE an.form_id = ?
        ORDER BY an.neighbor_id ASC
    ";
    $stmt = $conn->prepare($sql_neighbors);
    $stmt->bind_param('i', $draft_id);
    $stmt->execute();
    $neighbors_result = $stmt->get_result();
    $stmt->close();
    
    $neighbors = [];
    $neighbor_ids = [];
    while ($neighbor_row = $neighbors_result->fetch_assoc()) {
        $neighbors[] = $neighbor_row;
        $neighbor_ids[] = $neighbor_row['neighbor_id'];
    }
    
    if (!empty($neighbor_ids)) {
        // Fetch owners for each neighbor
        $placeholders = implode(',', array_fill(0, count($neighbor_ids), '?'));
        $sql_owners = "
            SELECT an.neighbor_id, apo.adjacent_property_owner_first_name, 
                   apo.adjacent_property_owner_last_name, a.address_street, 
                   a.address_city, a.state_code, a.address_zip_code,
                   ROW_NUMBER() OVER (PARTITION BY an.neighbor_id ORDER BY ano.adjacent_neighbor_owner_id) as owner_index
            FROM adjacent_neighbors an
            JOIN adjacent_neighbor_owners ano ON an.neighbor_id = ano.neighbor_id
            JOIN adjacent_property_owners apo ON ano.adjacent_property_owner_id = apo.adjacent_property_owner_id
            LEFT JOIN addresses a ON apo.address_id = a.address_id
            WHERE an.neighbor_id IN ({$placeholders})
            ORDER BY an.neighbor_id ASC, ano.adjacent_neighbor_owner_id ASC
        ";
        $stmt = $conn->prepare($sql_owners);
        $types = str_repeat('i', count($neighbor_ids));
        $stmt->bind_param($types, ...$neighbor_ids);
        $stmt->execute();
        $owners_result = $stmt->get_result();
        $stmt->close();
        
        // Build hierarchical data structure
        $draft_data = [
            'pva_map_codes' => [],
            'neighbor_property_locations' => [],
            'neighbor_property_deed_books' => [],
            'property_street_pg_numbers' => [],
            'property_owner_names' => new stdClass(),
            'property_owner_streets' => new stdClass(),
            'property_owner_cities' => new stdClass(),
            'property_owner_state_codes' => new stdClass(),
            'property_owner_zips' => new stdClass(),
        ];
        
        // Add neighbor data
        foreach ($neighbors as $idx => $neighbor) {
            $draft_data['pva_map_codes'][] = $neighbor['PVA_map_code'] ?? '';
            $draft_data['neighbor_property_locations'][] = $neighbor['apof_neighbor_property_location'] ?? '';
            $draft_data['neighbor_property_deed_books'][] = $neighbor['apof_neighbor_property_deed_book'] ?? '';
            $draft_data['property_street_pg_numbers'][] = $neighbor['apof_property_street_pg_number'] ?? '';
            
            // Initialize owner arrays for this neighbor index
            $draft_data['property_owner_names']->{$idx} = [];
            $draft_data['property_owner_streets']->{$idx} = [];
            $draft_data['property_owner_cities']->{$idx} = [];
            $draft_data['property_owner_state_codes']->{$idx} = [];
            $draft_data['property_owner_zips']->{$idx} = [];
        }
        
        // Add owner data
        $owners_result->data_seek(0);
        $current_neighbor_idx = -1;
        $current_neighbor_id = null;
        while ($owner_row = $owners_result->fetch_assoc()) {
            // Find index of this neighbor
            foreach ($neighbors as $idx => $n) {
                if ($n['neighbor_id'] == $owner_row['neighbor_id']) {
                    $current_neighbor_idx = $idx;
                    break;
                }
            }
            
            if ($current_neighbor_idx >= 0) {
                $owner_name = trim(($owner_row['adjacent_property_owner_first_name'] ?? '') . ' ' . ($owner_row['adjacent_property_owner_last_name'] ?? ''));
                $draft_data['property_owner_names']->{$current_neighbor_idx}[] = $owner_name;
                $draft_data['property_owner_streets']->{$current_neighbor_idx}[] = $owner_row['address_street'] ?? '';
                $draft_data['property_owner_cities']->{$current_neighbor_idx}[] = $owner_row['address_city'] ?? '';
                $draft_data['property_owner_state_codes']->{$current_neighbor_idx}[] = $owner_row['state_code'] ?? '';
                $draft_data['property_owner_zips']->{$current_neighbor_idx}[] = $owner_row['address_zip_code'] ?? '';
            }
        }
    } else {
        $error = "Draft not found or you don't have permission to access it.";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract form data
        $formData = extractAdjacentPropertyOwnersFormData($_POST);

        // Decide action (save draft should not require full validation)
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // Validate form data only for final submission
        if ($action !== 'save_draft') {
            $errors = validateAdjacentPropertyOwnersFormData($formData);
            if (!empty($errors)) {
                throw new Exception(implode(' ', $errors));
            }
        }
        
        // Insert application
        if($_POST["action"] === "submit_final"){
            if($draft_id){
                $result = updateAdjacentPropertyOwnersFormApplication($conn, $draft_id, $formData);
                $new_form_id = $draft_id;
            }
            else{
                $result = insertAdjacentPropertyOwnersFormApplication($conn, $formData);
                $new_form_id = $result['form_id'];
            }
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            
            
            // Link form to client
            $link_sql = "INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)";
            $link_stmt = $conn->prepare($link_sql);
            $link_stmt->bind_param("ii", $new_form_id, $client_id);
            $link_stmt->execute();
            $link_stmt->close();
            
            $success = 'Form submitted successfully for all adjacent properties!';
        } else{
            $conn->query("Call submit_draft()");
            if($draft_id){
                $result = updateAdjacentPropertyOwnersFormApplication($conn, $draft_id, $formData);
                $new_form_id = $draft_id;
            }
            else{
                $result = insertAdjacentPropertyOwnersFormApplication($conn, $formData);
                $new_form_id = $result['form_id'];
            }
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            // Link form to client only if it's a new draft (not an update)
            if (!$draft_id) {
                $link_sql = "INSERT INTO incomplete_client_forms (form_id, client_id) VALUES (?, ?)";
                $link_stmt = $conn->prepare($link_sql);
                $link_stmt->bind_param("ii", $new_form_id, $client_id);
                $link_stmt->execute();
                $link_stmt->close();
            }
            $conn->query("Call draft_submitted()");
            $success = 'Form submitted successfully for all adjacent properties!';
        }
        
    } catch (Exception $e) {
        error_log("Error in adjacent property form submission: " . $e->getMessage());
        $error = 'An error occurred while submitting the form: ' . $e->getMessage();
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
    .neighbor-card {
        border: 2px solid #6a1b9a;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        background-color: #fff;
        position: relative;
    }
    .neighbor-card-header {
        background-color: #6a1b9a;
        color: white;
        padding: 10px 15px;
        margin: -20px -20px 20px -20px;
        border-radius: 6px 6px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .owner-entry {
        border: 1px solid #d1c4e9;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #f9f9f9;
        position: relative;
    }
    .owner-entry-header {
        background-color: #ede7f6;
        padding: 8px 12px;
        margin: -15px -15px 15px -15px;
        border-radius: 4px 4px 0 0;
        font-weight: bold;
        color: #6a1b9a;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .remove-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    .remove-btn:hover {
        background-color: #c82333;
    }
    .add-owner-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
    }
    .add-owner-btn:hover {
        background-color: #218838;
    }
    .add-neighbor-btn {
        background-color: #6a1b9a;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-bottom: 20px;
    }
    .add-neighbor-btn:hover {
        background-color: #4a148c;
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

  <p>Applicants are required to furnish the Danville-Boyle County Planning & Zoning Commission with the names and mailing address of the owners of all adjacent property. Adjacent property is defined as being property across roads, streets, interstates, rivers, streams, etc., as well as abutting the subject property. The applicant may rely on the records maintained by the Boyle County Property Valuation Administrator to determine the identity and address of the adjacent property owners. Instructions for completing this form:</p>
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
    <?php if ($draft_data): ?>
        <div id="draft_data_json" style="display: none;">
            <?php echo json_encode($draft_data); ?>
        </div>
    <?php endif; ?>
    <form method="post" id="adjacentPropertyForm">
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

        <div class="text-center mt-4 button-group">
            <button class="btn btn-warning btn-lg" type="submit" name="action" value="save_draft" formnovalidate>Save as Draft</button>
            <button class="btn btn-primary btn-lg" type="submit" name="action"  value="submit_final">Submit All Adjacent Properties</button>
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

    // Helper functions defined first
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

    function populateFormFromDraft(draftData) {
        console.log('Populating form from draft:', draftData);
        // Start with the initial neighbor card (index 0) that's already in HTML
        const initialCard = neighborsContainer.querySelector('.neighbor-card[data-neighbor-index="0"]');
        if (!initialCard) {
            console.error('Initial card not found');
            return;
        }

        const pvaMapCodes = draftData['pva_map_codes'] || [];
        const locations = draftData['neighbor_property_locations'] || [];
        const deedBooks = draftData['neighbor_property_deed_books'] || [];
        const pageNumbers = draftData['property_street_pg_numbers'] || [];
        const ownerNames = draftData['property_owner_names'] || {};
        const ownerStreets = draftData['property_owner_streets'] || {};
        const ownerCities = draftData['property_owner_cities'] || {};
        const ownerStates = draftData['property_owner_state_codes'] || {};
        const ownerZips = draftData['property_owner_zips'] || {};

        console.log('PVA Codes:', pvaMapCodes);
        console.log('Owner Names:', ownerNames);

        // Fill in the first neighbor (index 0)
        if (pvaMapCodes.length > 0) {
            initialCard.querySelector('input[name="p_PVA_map_code[0]"]').value = pvaMapCodes[0] || '';
            initialCard.querySelector('input[name="p_apof_neighbor_property_location[0]"]').value = locations[0] || '';
            initialCard.querySelector('input[name="p_apof_neighbor_property_deed_book[0]"]').value = deedBooks[0] || '';
            initialCard.querySelector('input[name="p_apof_property_street_pg_number[0]"]').value = pageNumbers[0] || '';

            // Fill in owners for the first neighbor
            const ownersContainer = initialCard.querySelector('.owners-container');
            ownersContainer.innerHTML = ''; // Clear initial owner entry

            const ownersList = ownerNames[0] || [];
            console.log('Owners for neighbor 0:', ownersList);
            
            ownersList.forEach((ownerName, ownerIndex) => {
                const ownerCard = createOwnerEntry(0, ownerIndex);
                ownersContainer.appendChild(ownerCard);

                ownerCard.querySelector(`input[name="p_adjacent_property_owner_name[0][${ownerIndex}]"]`).value = ownerName || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_street[0][${ownerIndex}]"]`).value = (ownerStreets[0] && ownerStreets[0][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_city[0][${ownerIndex}]"]`).value = (ownerCities[0] && ownerCities[0][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_state_code[0][${ownerIndex}]"]`).value = (ownerStates[0] && ownerStates[0][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_zip[0][${ownerIndex}]"]`).value = (ownerZips[0] && ownerZips[0][ownerIndex]) || '';
            });

            initialCard.querySelector('.num-owners-input').value = ownersList.length || 1;
            updateOwnerRemoveButtons(initialCard);
        }

        // Add additional neighbor cards (starting from index 1)
        for (let neighborIndex = 1; neighborIndex < pvaMapCodes.length; neighborIndex++) {
            neighborCounter++;
            const card = createNeighborCard(neighborCounter);
            card.dataset.neighborIndex = neighborCounter;

            card.querySelector(`input[name="p_PVA_map_code[${neighborCounter}]"]`).value = pvaMapCodes[neighborIndex] || '';
            card.querySelector(`input[name="p_apof_neighbor_property_location[${neighborCounter}]"]`).value = locations[neighborIndex] || '';
            card.querySelector(`input[name="p_apof_neighbor_property_deed_book[${neighborCounter}]"]`).value = deedBooks[neighborIndex] || '';
            card.querySelector(`input[name="p_apof_property_street_pg_number[${neighborCounter}]"]`).value = pageNumbers[neighborIndex] || '';

            const ownersContainer = card.querySelector('.owners-container');
            ownersContainer.innerHTML = '';

            const ownersList = ownerNames[neighborIndex] || [];
            ownersList.forEach((ownerName, ownerIndex) => {
                const ownerCard = createOwnerEntry(neighborCounter, ownerIndex);
                ownersContainer.appendChild(ownerCard);

                ownerCard.querySelector(`input[name="p_adjacent_property_owner_name[${neighborCounter}][${ownerIndex}]"]`).value = ownerName || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_street[${neighborCounter}][${ownerIndex}]"]`).value = (ownerStreets[neighborIndex] && ownerStreets[neighborIndex][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_city[${neighborCounter}][${ownerIndex}]"]`).value = (ownerCities[neighborIndex] && ownerCities[neighborIndex][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_state_code[${neighborCounter}][${ownerIndex}]"]`).value = (ownerStates[neighborIndex] && ownerStates[neighborIndex][ownerIndex]) || '';
                ownerCard.querySelector(`input[name="p_adjacent_property_owner_zip[${neighborCounter}][${ownerIndex}]"]`).value = (ownerZips[neighborIndex] && ownerZips[neighborIndex][ownerIndex]) || '';
            });

            card.querySelector('.num-owners-input').value = ownersList.length || 1;
            updateOwnerRemoveButtons(card);

            neighborsContainer.appendChild(card);
        }

        // Update form state
        numNeighborsInput.value = pvaMapCodes.length || 1;
        updateRemoveButtons();
    }

    // Auto-fill form from saved draft data if available
    const draftDataElement = document.getElementById('draft_data_json');
    if (draftDataElement) {
        try {
            const draftData = JSON.parse(draftDataElement.textContent);
            populateFormFromDraft(draftData);
        } catch (e) {
            console.error('Error parsing draft data:', e);
        }
    }

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
});
</script>
</body>
</html>