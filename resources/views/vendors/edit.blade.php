@extends('Partials.app', ['activeMenu' => 'vendors'])

@section('title') Edit Vendor @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title"><i class="fa fa-pencil-alt text-warning me-2"></i> Edit Vendor: {{ $vendor->name }}</h3>
            <div class="block-options">
                <a href="{{ route('vendors.index') }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('vendors.update', $vendor->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('vendors.form')

                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                    <a href="{{ route('vendors.index') }}" class="btn btn-alt-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select categories...',
                allowClear: true
            });
        });
    </script>
@endsection
