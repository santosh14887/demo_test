<form id="contactForm" enctype="multipart/form-data">
    <!-- Standard Fields -->
     @php
     $gender_list = array('male' =>'Male', 'female' => 'Female', 'other' => 'Other');
     @endphp
    <div id="contactMessage"></div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name *</label>
            <input type="text" id="name" name="name" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" autocomplete="off">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" autocomplete="off">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Gender</label>
            <div>
                @foreach($gender_list as $gender_key =>$gender_value)
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="gender_{{ $gender_key }}" value="{{ $gender_key }}">
                    <label class="form-check-label" for="gender_{{ $gender_key }}">{{ $gender_value }}</label>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="profile_image" class="form-label">Profile Image</label>
            <input type="file" id="profile_image" name="profile_image" class="form-control">
            <div class="profile-image-div"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="additional_file" class="form-label">Additional File</label>
            <input type="file" id="additional_file" name="additional_file" class="form-control">
            <div class="additional-image-div"></div>
        </div>
    </div>

    <div id="customFieldMain">
    <hr>
        <h5 id="customFieldHeading">Custom Fields</h5>
        <div id="customFieldsContainer">
            <!-- Custom fields will be loaded here via AJAX -->
        </div>
    </div>
    <div class="please-wait-message text-center text-primary mb-2" style="display:none;">Please wait...</div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form> 