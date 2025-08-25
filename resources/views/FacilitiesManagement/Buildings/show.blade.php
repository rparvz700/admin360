@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title', 'Building Details')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Building Details</h3>
        </div>
        <div class="block-content">
            <p><strong>ID:</strong> {{ $building->id }}</p>
            <p><strong>Name:</strong> {{ $building->name }}</p>
            <a href="{{ route('buildings.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
@endsection
