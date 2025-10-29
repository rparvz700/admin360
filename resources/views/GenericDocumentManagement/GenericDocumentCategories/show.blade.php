@extends('Partials.app', ['activeMenu' => 'vehicle-document-categories'])
@section('title') Vehicle Document Category Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Vehicle Document Category Details</h3>
            <a href="{{ route('vehicle-document-categories.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th>ID</th><td>{{ $category->id }}</td></tr>
                    <tr><th>Category Name</th><td>{{ $category->category_name }}</td></tr>
                    <tr><th>Description</th><td>{{ $category->description }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
