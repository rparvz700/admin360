@extends('Partials.app', ['activeMenu' => 'generic-document-attributes'])
@section('title') Add Generic Document Attribute @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add Generic Document Attribute</h3>
            <a href="{{ route('generic-document-attributes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('generic-document-attributes.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('GenericDocumentManagement.GenericDocumentAttributes.form', ['attribute' => null, 'categories' => $categories])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
