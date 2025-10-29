@extends('Partials.app', ['activeMenu' => 'vehicle-documents'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('scripts')
    <link href="{{ asset('js/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        window.allDocumentAttributes = @json($attributes);
        window.oldAttributeValues = @json($oldAttributeValues);
    </script>
    <script src="{{ asset('js/vehicle-document-attribute-fields.js') }}"></script>
@endsection

@section('page_title')
    Edit Vehicle Document
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit Vehicle Document</h3>
                <a href="{{ route('vehicle-documents.index') }}" class="btn btn-sm btn-secondary">Back to List</a>
            </div>
            <div class="block-content">
                <form action="{{ route('vehicle-documents.update', $doc->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-8">
                            @include('VehicleManagement.VehicleDocument.form', ['doc' => $doc, 'vehicles' => $vehicles, 'categories' => $categories])
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('vehicle-documents.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@endsection
