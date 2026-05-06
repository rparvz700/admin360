@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Floor
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="floor-page-header">
            <div>
                <div class="floor-eyebrow">Facilities Management</div>
                <h2>Create Floor</h2>
                <p>Add a floor profile and map it to building, project, and agreement records.</p>
            </div>
            <a href="{{ route('floors.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded floor-shell">
            <div class="block-header block-header-default floor-block-header">
                <div>
                    <h3 class="block-title">Floor Profile</h3>
                    <div class="text-muted fs-sm">Fields marked with <span class="text-danger">*</span> are required.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('floors.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @include('FacilitiesManagement.Floors.partials.form', [
                        'floor' => null,
                        'buildings' => $buildings,
                        'agreements' => $agreements,
                        'projects' => $projects,
                    ])

                    <div class="floor-action-bar">
                        <a href="{{ route('floors.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Floor
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
        One.helpersOnLoad(['jq-select2']);
    </script>
@endsection
