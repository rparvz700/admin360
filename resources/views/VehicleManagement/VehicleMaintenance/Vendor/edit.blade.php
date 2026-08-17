@extends('Partials.app', ['activeMenu' => 'vendors'])

@section('title') Edit Vendor @endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Vendor
                <span class="fs-sm fw-normal text-muted ms-1">&mdash; {{ $vendor->name }}</span>
            </h3>
            <div class="block-options">
                <a href="{{ route('maintenance.vendors.show', $vendor->id) }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-eye me-1"></i> View Details
                </a>
                <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
        <div class="block-content">
            <form action="{{ route('maintenance.vendors.update', $vendor->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                @include('VehicleManagement.VehicleMaintenance.Vendor.form', ['vendor' => $vendor])

                <div class="border-top pt-3 mb-4">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Vendor</button>
                    <a href="{{ route('maintenance.vendors.index') }}" class="btn btn-secondary ms-2">Cancel</a>
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
            $('.select2').select2({ width: '100%' });
        });
    </script>
@endsection
