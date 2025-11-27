@extends('Partials.app', ['activeMenu' => 'generic-document-categories'])
@section('title') Edit Generic Document Category @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Generic Document Category</h3>
            <a href="{{ route('generic-document-categories.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('generic-document-categories.update', $category->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('GenericDocumentManagement.GenericDocumentCategories.form', ['category' => $category])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
