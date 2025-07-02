<div id="customFieldMessage"></div>
<div class="row">
    @php
    $filed_type_arr = array('text' => 'Text', 'number', 'date');
    @endphp
    <!-- Form to add new custom fields -->
    <div class="col-md-4">
        <h5>Add New Field</h5>
        <form id="customFieldForm">
            <div class="mb-3">
                <label for="label" class="form-label">Field Label *</label>
                <input type="text" id="label" name="label" class="form-control" autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Field Type</label>
                <select id="type" name="type" class="form-select">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <!-- More types can be added here, e.g., select, textarea -->
                </select>
            </div>
            <div class="please-wait-message text-center text-primary mb-2" style="display:none;">Please wait...</div>
            <button type="submit" class="btn btn-primary">Add Field</button>
        </form>
    </div>
    <!-- Table of existing custom fields -->
    <div class="col-md-8">
        <h5>Existing Fields</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="customFieldsTableBody">
                <!-- Rows will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>
</div> 