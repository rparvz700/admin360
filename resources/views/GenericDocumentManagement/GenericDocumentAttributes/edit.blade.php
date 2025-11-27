@extends('Partials.app', ['activeMenu' => 'generic-document-attributes'])
@section('title') Edit Generic Document Attribute @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Generic Document Attribute</h3>
            <a href="{{ route('generic-document-attributes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('generic-document-attributes.update', $attribute->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('GenericDocumentManagement.GenericDocumentAttributes.form', ['attribute' => $attribute, 'categories' => $categories])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
