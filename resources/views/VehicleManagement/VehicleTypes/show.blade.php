@extends('Partials.app', ['activeMenu' => 'vehicle-types'])
@section('title') Vehicle Type Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle Type Details</h3>
            <a href="{{ route('vehicle-types.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th>ID</th><td>{{ $type->id }}</td></tr>
                    <tr><th>Type Name</th><td>{{ $type->type_name }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
