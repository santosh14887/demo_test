@extends('layouts.app')
@section('content')
<div id="customFieldMessage"></div>
<div class="row">
    @php
    $filed_type_arr = array('text' => 'Text', 'number', 'date');
    @endphp
    <!-- Form to add new custom fields -->
    <div class="col-md-12">
        <h5>Add New Field</h5>
        <form action ="{{ route('custom-fields.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="label" class="form-label">Field Label *</label>
                <input type="text" id="label" name="label" class="form-control">
               @if( $errors->first('label') )
                <div class=" col-md-12 alert alert-danger"> {{ $errors->first('label')}} </div>
                @endif
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
            <button type="submit" class="btn btn-primary">Add Field</button>
        </form>
    </div>
</div> 
@endsection