$(document).ready(function() {
   // Load custom fields into the form
    function loadCustomFieldsForForm(values = []) {
        $('#customFieldMain').hide();
        $.get(urls.customFieldsIndex, function(response) {
            let html = '';
            let count = 0;
            response.fields.forEach((field, idx) => {
                const existingValue = values.find(v => v.custom_field_id === field.id);
                const value = existingValue ? existingValue.value : '';
                // Determine input type based on custom field type
                let inputType = 'text';
                if (field.type === 'number') {
                    inputType = 'number';
                } else if (field.type === 'date') {
                    inputType = 'date';
                }
                if (count % 2 === 0) html += '<div class="row">';
                html += `
                    <div class="col-md-6 mb-3">
                        <label for="custom_field_${field.id}" class="form-label">${field.label}</label>
                        <input type="${inputType}" name="custom_fields[${field.id}]" id="custom_field_${field.id}" class="form-control" value="${value}">
                    </div>
                `;
                count++;
                if (count % 2 === 0 || idx === response.fields.length - 1) html += '</div>';
            });
            if(html != '')  $('#customFieldMain').show();
            $('#customFieldsContainer').html(html);
        });
    }
    window.loadCustomFieldsForForm  = loadCustomFieldsForForm ;
    // Custom Fields Modal Logic
    $('#manageCustomFieldsBtn').click(function() {
        loadCustomFieldsForManagement();
        $('#customFieldsModal').modal('show');
    });

    function loadCustomFieldsForManagement() {
        $.get(urls.customFieldsIndex, function(response) {
            let rows = '';
            if(response.fields.length > 0) {
            response.fields.forEach(field => {
                rows += `
                    <tr data-id="${field.id}">
                        <td>${field.label}</td>
                        <td>${field.type}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-cf-btn"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        } else {
            rows += `<tr class='text-center'><td colspan='3'>${noData}</td></tr>`;
        }
            $('#customFieldsTableBody').html(rows);
        });
    }

    $('#customFieldForm').submit(function(e) {
        e.preventDefault();
        // Remove previous errors
        $('#customFieldForm .is-invalid').removeClass('is-invalid');
        $('#customFieldForm .invalid-feedback').remove();
        // Show please wait and disable button
        $(this).find('.please-wait-message').show();
        $(this).find('button[type=submit]').prop('disabled', true);
        $.ajax({
            url: urls.customFieldsStore,
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#customFieldForm')[0].reset();
                loadCustomFieldsForManagement();
                showCustomFieldMessage(response.message, true);
            },
            error: function(xhr) {
                // Remove previous errors
                $('#customFieldForm .is-invalid').removeClass('is-invalid');
                $('#customFieldForm .invalid-feedback').remove();
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        const field = $(`#customFieldForm [name="${key}"]`);
                        field.addClass('is-invalid');
                        field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                    });
                } else {
                    showCustomFieldMessage('An unexpected error occurred.', false);
                }
            },
            complete: function() {
                $('#customFieldForm').find('.please-wait-message').hide();
                $('#customFieldForm').find('button[type=submit]').prop('disabled', false);
            }
        });
    });

    // Show styled message for custom field actions
    function showCustomFieldMessage(message, isSuccess, divId = "customFieldMessage") {
        const color = isSuccess ? 'success' : 'danger';
        $('#'+divId).html(`<div class="alert alert-${color} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
        setTimeout(() => {
            $('#'+divId+' .alert').alert('close');
        }, 4000);
    }
    window.showCustomFieldMessage = showCustomFieldMessage;

    let customFieldIdToDelete = null;
    $(document).on('click', '.delete-cf-btn', function() {
        customFieldIdToDelete = $(this).closest('tr').data('id');
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'), {
            backdrop: 'static',
            keyboard: false
        });
        deleteModal.show();
    });
    $(document).on('click', '#confirmDeleteBtn', function() {
        if (!customFieldIdToDelete) return;
        $.ajax({
            url: `${urls.customFieldsBase}/${customFieldIdToDelete}`,
            method: 'DELETE',
            success: function(response) {
                loadCustomFieldsForManagement();
                showCustomFieldMessage(response.message, true);
            },
            complete: function() {
                customFieldIdToDelete = null;
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                if (deleteModal) deleteModal.hide();
            }
        });
    });

    $('#customFieldsModal').on('hidden.bs.modal', function () {
        $('#customFieldForm .is-invalid').removeClass('is-invalid');
        $('#customFieldForm .invalid-feedback').remove();
    });
});