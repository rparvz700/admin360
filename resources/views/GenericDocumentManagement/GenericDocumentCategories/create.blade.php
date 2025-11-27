@extends('Partials.app', ['activeMenu' => 'generic-document-categories'])
@section('title') Add Generic Document Category @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Generic Document Category</h3>
            <a href="{{ route('generic-document-categories.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('generic-document-categories.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('GenericDocumentManagement.GenericDocumentCategories.form', ['category' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
