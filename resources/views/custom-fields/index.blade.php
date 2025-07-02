@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
    <h1 class="mb-4">Custom Fields</h1>
    <div class="d-flex justify-content-between mb-4">
        <div>
            <a class="btn btn-primary" href="{{ route('custom-fields.create')}}"><i class="fas fa-plus"></i> Add Fields</a>
            <a class="btn btn-primary" href="{{ route('contacts.index')}}"> Contacts</a>
        </div>
        <div class="d-flex">
            <input type="text" id="filterName" class="form-control me-2" placeholder="Filter by Name...">
        </div>
    </div>

    <!-- Contacts Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Label</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($fields) && count($fields) > 0)
                @foreach($fields as $list)
                    <tr>
                        <td>{{ $list->label }}</td>
                        <td>{{ $list->type }}</td>
                        <td>
                            <a href="{{ route('custom-fields.edit', $list->id) }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('custom-fields.destroy', $list->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure?')" style="border: none; background: none; padding: 0; color: inherit; cursor: pointer;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>      
                @endforeach
            @else
            @endif
        </tbody>
    </table>
    <div class="col-md-12 pagination-wrapper"> {{ $fields->render() }} </div>
</div>
@endsection