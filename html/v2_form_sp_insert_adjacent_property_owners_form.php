<?php
/**
 * Refactored Adjacent Property Owners Form Application Form Handler
 * Replace the existing POST handling in form_sp_insert_adjacent_property_owners_form.php
 * with this code block (lines 15-133 in original file)
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extract form data
        $formData = extractAdjacentPropertyOwnersFormData($_POST);
        
        // Validate form data
        $errors = validateAdjacentPropertyOwnersFormData($formData);
        
        if (!empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }
        
        // Insert application
        $result = insertAdjacentPropertyOwnersFormApplication($conn, $formData);
        
        if (!$result['success']) {
            throw new Exception($result['message']);
        }
        
        $new_form_id = $result['form_id'];
        
        // Link form to client
        $link_sql = "INSERT INTO client_forms (form_id, client_id) VALUES (?, ?)";
        $link_stmt = $conn->prepare($link_sql);
        $link_stmt->bind_param("ii", $new_form_id, $client_id);
        $link_stmt->execute();
        $link_stmt->close();
        
        $success = 'Form submitted successfully for all adjacent properties!';
        
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

        <div class="form-group mt-4">
            <button class="btn btn-primary btn-lg btn-block" type="submit">Submit All Adjacent Properties</button>
        </div>
    </form>
  </div>
</div>

<script>
/**
 * Client side dynamic form logic
 */
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
    //Build a new adjacent property block for the given neighbor index
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
    // Creat DOM node for ann owner entry for given neighbor
    function createOwnerEntry(neighborIndex, ownerIndex) {
        const div = document.createElement('div');
        div.className = 'owner-entry';
        div.dataset.ownerIndex = ownerIndex;
        div.innerHTML = createOwnerEntryHTML(neighborIndex, ownerIndex);
        return div;
    }
    // Return the inner HTML string for a signle owner entry block.
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
    // Show or hide Remove Property buttons depending on how many neighbor cards exist.
    function updateRemoveButtons() {
        const neighborCards = neighborsContainer.querySelectorAll('.neighbor-card');
        neighborCards.forEach((card, index) => {
            const removeBtn = card.querySelector('.remove-neighbor-btn');
            removeBtn.style.display = neighborCards.length > 1 ? 'block' : 'none';
        });
    }
    //Show/hide remove buttons for owners within a single neighbor
    function updateOwnerRemoveButtons(neighborCard) {
        const ownerEntries = neighborCard.querySelectorAll('.owner-entry');
        ownerEntries.forEach((entry, index) => {
            const removeBtn = entry.querySelector('.remove-owner-btn');
            removeBtn.style.display = ownerEntries.length > 1 ? 'block' : 'none';
        });
    }
    //After deleting a neighbor card, renumber headings
    function renumberNeighbors() {
        const neighborCards = neighborsContainer.querySelectorAll('.neighbor-card');
        neighborCards.forEach((card, index) => {
            card.querySelector('.neighbor-card-header h5').textContent = `Adjacent Property #${index + 1}`;
        });
    }
    //After deleting an owner, renumber the owner labels.
    function renumberOwners(neighborCard) {
        const ownerEntries = neighborCard.querySelectorAll('.owner-entry');
        ownerEntries.forEach((entry, index) => {
            entry.querySelector('.owner-entry-header span').textContent = `Owner #${index + 1}`;
        });
    }
});
</script>
</body>
</html>