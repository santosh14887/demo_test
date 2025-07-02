@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
<div id="contactManager" 
     data-contacts-index="{{ route('contacts.index') }}"
     data-contacts-store="{{ route('contacts.store') }}"
     data-contacts-base="{{ url('contacts') }}"
     data-custom-fields-index="{{ route('custom-fields.index') }}"
     data-custom-fields-store="{{ route('custom-fields.store') }}"
     data-custom-fields-base="{{ url('custom-fields') }}"
     data-assets-public-path = "{{ asset('uploads')}}">

    <h1 class="mb-4">Contacts</h1>
    <div id="contactIndexMessage"></div>
    <!-- Actions and Filters -->
    <div class="d-flex justify-content-between mb-4">
        <div>
            <button class="btn btn-primary" id="createContactBtn"><i class="fas fa-plus"></i> Add Contact</button>
            <button class="btn btn-secondary" id="manageCustomFieldsBtn"><i class="fas fa-cog"></i> Manage Custom Fields</button>
        </div>
        <div class="d-flex">
            <input type="text" id="filterName" class="form-control me-2" placeholder="Filter by Name...">
            <input type="text" id="filterEmail" class="form-control me-2" placeholder="Filter by Email...">
            <select id="filterGender" class="form-select">
                <option value="">All Genders</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>

    <!-- Contacts Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="contactsTableBody">
            <!-- Loaded via AJAX -->
        </tbody>
    </table>
</div>

@include('contacts.popup')
@endsection

@push('scripts')
    <script src="{{ asset('js/contacts.js') }}"></script>
    <script src="{{ asset('js/contacts-merge.js') }}"></script>
    <script src="{{ asset('js/custom-fields.js') }}"></script>
    
@endpush 