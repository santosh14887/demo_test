$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    window.noData = 'No data found';
    // Get URLs from data attributes
    const urls = $('#contactManager').data();
    window.urls = urls;
    // Load initial contacts
    loadContacts();

    // Function to load contacts
    function loadContacts(filters = {}) {
        $.ajax({
            url: urls.contactsIndex,
            method: 'GET',
            data: filters,
            success: function(response) {
                let rows = '';
                if(response.contacts.length > 0) {
                    response.contacts.forEach(contact => {
                        rows += `
                            <tr data-id="${contact.id}">
                                <td>${contact.id}</td>
                                <td>${contact.name}</td>
                                <td>${contact.email}</td>
                                <td>${contact.phone || ''}</td>
                                <td>${contact.gender || ''}</td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-btn"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i></button>
                                    <button class="btn btn-sm btn-success mergeContactBtn" data-contact-id="${contact.id}"><i class="fas fa-code-merge"></i></button>
                                    <span class="merged-data-link-placeholder" data-contact-id="${contact.id}"></span>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    rows += `<tr class='text-center'><td colspan='6'>${noData}</td></tr>`;
                }

                $('#contactsTableBody').html(rows);
                // After rendering, check for merged data for each contact
                response.contacts.forEach(contact => {
                    $.get(`/contacts/${contact.id}/merged-data`, function(data) {
                        if (data && (data.merged_emails.length || data.merged_phones.length || data.merged_custom_fields.length)) {
                            $(`.mergeContactBtn[data-contact-id='${contact.id}']`).remove();
                            $(`.merged-data-link-placeholder[data-contact-id='${contact.id}']`).html(`<a href="#" class="btn btn-info btn-sm show-merged-data" data-contact-id="${contact.id}">Merged Data</a>`);
                        }
                    });
                });
            }
        });
    }

    // Make loadContacts globally accessible
    window.loadContacts = loadContacts;

    // Listen for custom event to reload contacts
    // $(document).on('contacts:reload', function() {
    //     loadContacts();
    // });

    // Filtering
    $('#filterName, #filterEmail, #filterGender').on('keyup change', function() {
        loadContacts({
            name: $('#filterName').val(),
            email: $('#filterEmail').val(),
            gender: $('#filterGender').val()
        });
    });

    // Open Create Contact Modal
    $('#createContactBtn').click(function() {
        $('#contactForm')[0].reset();
        $(".profile-image-div").html('');
        $(".additional-image-div").html('');
        $('#contactModalLabel').text('Add Contact');
        $('#contactForm').attr('data-id', '');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        loadCustomFieldsForForm();
        $('#contactModal').modal('show');
    });
    
    // Open Contact Edit Modal
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).closest('tr').data('id');
        $.get(`${urls.contactsBase}/${id}`, function(response) {
            const contact = response.contact;
            let profile_image = contact.profile_image;
            let additional_file = contact.additional_file;
            $('#contactForm')[0].reset();
            $('#contactModalLabel').text('Edit Contact');
            $('#contactForm').attr('data-id', id);
            $('#name').val(contact.name);
            $('#email').val(contact.email);
            $('#phone').val(contact.phone);
            (profile_image) ? $(".profile-image-div").html(`<img style="width:50px" src = "${urls.assetsPublicPath}/${profile_image}">`) : '';
            (additional_file) ? $(".additional-image-div").html(`<a target="_blank" href ="${urls.assetsPublicPath}/${additional_file}">Download</a>`) : '';
            $('input[name="gender"][value="' + contact.gender + '"]').prop('checked', true);
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            loadCustomFieldsForForm(contact.custom_field_values);
            $('#contactModal').modal('show');
        });
    });

    // Save Contact (Create/Update)
    $('#contactForm').submit(function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        $(this).find('.please-wait-message').show();
        $(this).find('button[type=submit]').prop('disabled', true);

        const id = $(this).attr('data-id');
        const url = id ? `${urls.contactsBase}/${id}` : urls.contactsStore;
        const method = 'POST';
        let formData = new FormData(this);
        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#contactModal').modal('hide');
                loadContacts();
                showCustomFieldMessage(response.message, true,'contactIndexMessage');
            },
            error: function(response) {
                if (response.status === 422) {
                    const errors = response.responseJSON.errors;    
                    $.each(errors, function(key, value) {
                        const field = $(`#contactForm [name="${key}"]`);
                        field.addClass('is-invalid');
                        field.after(`<div class="invalid-feedback d-block">${value[0]}</div>`);
                    });
                } else {
                    showCustomFieldMessage('An unexpected error occurred.', false,'contactMessage');
                }
            },
            complete: function() {
                $('#contactForm').find('.please-wait-message').hide();
                $('#contactForm').find('button[type=submit]').prop('disabled', false);
            }
        });
    });

    // Delete Contact
    let contactIdToDelete = null;
    $(document).on('click', '.delete-btn', function() {
        contactIdToDelete = $(this).closest('tr').data('id');
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'), {
            backdrop: 'static',
            keyboard: false
        });
        deleteModal.show();
    });
    $(document).on('click', '#confirmDeleteBtn', function() {
        if (!contactIdToDelete) return;
        $.ajax({
            url: `${urls.contactsBase}/${contactIdToDelete}`,
            method: 'DELETE',
            success: function(response) {
                loadContacts();
                showCustomFieldMessage(response.message, true,'contactMessage');
            },
            complete: function() {
                contactIdToDelete = null;
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                if (deleteModal) deleteModal.hide();
            }
        });
    });

    $('#contactModal').on('hidden.bs.modal', function () {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
    


    // Handle click on Merged Data button
    $(document).on('click', '.mergedDataBtn', function() {
        const contactId = $(this).data('contact-id');
        $.get(`/contacts/${contactId}/merged-data`, function(response) {
            let html = '';
            if (response.merges && response.merges.length > 0) {
                response.merges.forEach(merge => {
                    html += `<h6>Merged with Contact ID: ${merge.master_contact_id}</h6>`;
                    html += `<strong>Emails:</strong> <pre>${JSON.stringify(merge.merged_emails, null, 2)}</pre>`;
                    html += `<strong>Phones:</strong> <pre>${JSON.stringify(merge.merged_phones, null, 2)}</pre>`;
                    html += `<strong>Custom Fields:</strong> <pre>${JSON.stringify(merge.merged_custom_fields, null, 2)}</pre>`;
                    html += '<hr>';
                });
            } else {
                html = '<div class="alert alert-info">No merged data found.</div>';
            }
            $('#mergedDataModalBody').html(html);
            $('#mergedDataModal').modal('show');
        });
    });

    $(document).on('click', '.show-merged-data', function(e) {
        e.preventDefault();
        var contactId = $(this).data('contact-id');
        var url = `/contacts/${contactId}/merged-data`;
        $('#mergedDataModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#mergedDataModal').modal('show');
        $.get(url, function(data) {
            let html = '';
            if ((data.merged_emails && data.merged_emails.length) || (data.merged_phones && data.merged_phones.length) || (data.merged_custom_fields && data.merged_custom_fields.length)) {
                if (data.merged_emails && data.merged_emails.length) {
                    html += '<h6>Emails</h6><ul>';
                    data.merged_emails.forEach(function(item) {
                        html += `<li>${item.value}${item.is_final ? ' <span class=\'badge bg-success\'>Final</span>' : ''}</li>`;
                    });
                    html += '</ul>';
                }
                if (data.merged_phones && data.merged_phones.length) {
                    html += '<h6>Phones</h6><ul>';
                    data.merged_phones.forEach(function(item) {
                        html += `<li>${item.value}${item.is_final ? ' <span class=\'badge bg-success\'>Final</span>' : ''}</li>`;
                    });
                    html += '</ul>';
                }
                if (data.merged_custom_fields && data.merged_custom_fields.length) {
                    html += '<h6>Custom Fields</h6><ul>';
                    data.merged_custom_fields.forEach(function(item) {
                        html += `<li>${item.field_label}: ${item.values}${item.conflict ? ' <span class=\'badge bg-warning\'>Conflict</span>' : ''}</li>`;
                    });
                    html += '</ul>';
                }
            } else {
                html = '<div class="alert alert-info">No merged data found.</div>';
            }
            $('#mergedDataModalBody').html(html);
        }).fail(function(xhr) {
            $('#mergedDataModalBody').html('<div class="alert alert-danger">' + (xhr.responseJSON?.message || 'Failed to load merged data.') + '</div>');
        });
    });

    // Handle click on merged data link
    $(document).on('click', '.show-merged-data', function(e) {
        e.preventDefault();
        var contactId = $(this).data('contact-id');
        var url = `/contacts/${contactId}/merged-data`;
        $('#mergedDataModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#mergedDataModal').modal('show');
        $.get(url, function(data) {
            let html = '';
            if (data.merged_emails && data.merged_emails.length) {
                html += '<h6>Emails</h6><ul>';
                data.merged_emails.forEach(function(item) {
                    html += `<li>${item.value}${item.is_final ? ' <span class=\'badge bg-success\'>Final</span>' : ''}</li>`;
                });
                html += '</ul>';
            }
            if (data.merged_phones && data.merged_phones.length) {
                html += '<h6>Phones</h6><ul>';
                data.merged_phones.forEach(function(item) {
                    html += `<li>${item.value}${item.is_final ? ' <span class=\'badge bg-success\'>Final</span>' : ''}</li>`;
                });
                html += '</ul>';
            }
            if (data.merged_custom_fields && data.merged_custom_fields.length) {
                html += '<h6>Custom Fields</h6><ul>';
                data.merged_custom_fields.forEach(function(item) {
                    html += `<li>${item.field_label}: ${item.values}${item.conflict ? ' <span class=\'badge bg-warning\'>Conflict</span>' : ''}</li>`;
                });
                html += '</ul>';
            }
            if (!html) html = '<div class="alert alert-info">No merged data found.</div>';
            $('#mergedDataModalBody').html(html);
        }).fail(function(xhr) {
            $('#mergedDataModalBody').html('<div class="alert alert-danger">' + (xhr.responseJSON?.message || 'Failed to load merged data.') + '</div>');
        });
    });
}); 