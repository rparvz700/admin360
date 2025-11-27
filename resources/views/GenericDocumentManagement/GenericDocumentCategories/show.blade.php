@extends('Partials.app', ['activeMenu' => 'generic-document-categories'])
@section('title') Generic Document Category Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Generic Document Category Details</h3>
            <a href="{{ route('generic-document-categories.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
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
