// contacts-merge.js
// Handles the contact merge workflow

$(document).ready(function() {
    // Store current master and secondary IDs
    let currentMasterId = null;
    let currentSecondaryId = null;

    // When merge button is clicked, set secondaryId and open modal
    $(document).on('click', '.mergeContactBtn', function() {
        currentSecondaryId = $(this).data('contact-id');
        openMergeModal(currentSecondaryId);
    });

    // Function to open the merge modal and load UI
    function openMergeModal(secondaryContactId) {
        // Show modal and loading spinner
        $('#mergeContactsModal').modal('show');
        $('#mergeContactsModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        // Fetch contacts list for selection (excluding the secondary)
        $.ajax({
            url: '/contacts/list/list-for-merge',
            method: 'GET',
            data: { exclude: secondaryContactId },
            success: function(data) {
                renderContactSelection(secondaryContactId, data.contacts);
            },
            error: function() {
                $('#mergeContactsModalBody').html('<div class="alert alert-danger">Failed to load contacts.</div>');
            }
        });
    }

    // Render the UI to select the master contact
    function renderContactSelection(secondaryContactId, contacts) {
        let html = '<h5>Select a master contact to merge into:</h5>';
        html += '<select id="secondaryContactSelect" class="form-select mb-3">';
        html += '<option value="">-- Select Contact --</option>';
        contacts.forEach(function(contact) {
            html += `<option value="${contact.id}">${contact.name} (${contact.email})</option>`;
        });
        html += '</select>';
        html += '<button class="btn btn-primary" id="proceedToMergeBtn" disabled>Next</button>';
        $('#mergeContactsModalBody').html(html);
    }

    // Enable Next button when a contact is selected
    $(document).on('change', '#secondaryContactSelect', function() {
        $('#proceedToMergeBtn').prop('disabled', !$(this).val());
    });

    // Proceed to master selection and confirmation
    $(document).on('click', '#proceedToMergeBtn', function() {
        currentMasterId = $('#secondaryContactSelect').val();
        if (!currentMasterId) return;
        // Fetch both contacts' data for preview
        $.ajax({
            url: '/contacts/list/merge-preview',
            method: 'GET',
            data: { primary: currentMasterId, secondary: currentSecondaryId },
            success: function(data) {
                renderMergePreview(data.primary, data.secondary);
            },
            error: function() {
                $('#mergeContactsModalBody').html('<div class="alert alert-danger">Failed to load contact details.</div>');
            }
        });
    });

    // Render the preview and master selection UI
    function renderMergePreview(primary, secondary) {
        let html = '<h5>Master contact and merged data:</h5>';
        html += '<div class="row">';
        html += '<div class="col"><span class="badge bg-primary">Master</span><br>' + renderContactDetails(primary) + '</div>';
        html += '<div class="col"><span class="badge bg-secondary">Secondary</span><br>' + renderContactDetails(secondary) + '</div>';
        html += '</div>';
        html += '<button class="btn btn-success mt-3" id="confirmMergeBtn">Confirm Merge</button>';
        $('#mergeContactsModalBody').html(html);
    }

    // Render contact details (now includes custom fields)
    function renderContactDetails(contact) {
        let html = `Name: ${contact.name}<br>Email: ${contact.email}<br>Phone: ${contact.phone}<br>Gender: ${contact.gender}<br>`;
        if (contact.custom_field_values && contact.custom_field_values.length > 0) {
            html += '<ul>';
            contact.custom_field_values.forEach(cf => {
                html += `<li>${cf.custom_field.label}: ${cf.value}</li>`;
            });
            html += '</ul>';
        }
        return html;
    }

    // Handle merge confirmation
    $(document).on('click', '#confirmMergeBtn', function() {
        $.ajax({
            url: '/contacts/list/merge',
            method: 'POST',
            data: {
                master_id: currentMasterId,
                secondary_id: currentSecondaryId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                showCustomFieldMessage('Contacts merged successfully!', true, 'contactIndexMessage');
                loadContacts();
                $('#mergeContactsModal').modal('hide');
            },
            error: function() {
                showCustomFieldMessage('Failed to merge contacts.', false, 'contactIndexMessage');
                $('#mergeContactsModalBody').html('<div class="alert alert-danger">Failed to merge contacts.</div>');
            }
        });
    });

}); 