@extends('Partials.app', ['activeMenu' => 'floors'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Edit Floor
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="floor-page-header">
            <div>
                <div class="floor-eyebrow">Facilities Management</div>
                <h2>Edit Floor</h2>
                <p>Update floor metrics, assignment details, and status.</p>
            </div>
            <div class="floor-header-actions">
                <span class="badge {{ $floor->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $floor->status ?: 'N/A' }}
                </span>
                <a href="{{ route('floors.show', $floor->id) }}" class="btn btn-alt-secondary">
                    <i class="fa fa-eye me-1"></i> View
                </a>
            </div>
        </div>

        <div class="block block-rounded floor-shell">
            <div class="block-header block-header-default floor-block-header">
                <div>
                    <h3 class="block-title">{{ $floor->floor_label ?: 'Floor Profile' }}</h3>
                    <div class="text-muted fs-sm">Last updated {{ optional($floor->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('floors.update', $floor->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('FacilitiesManagement.Floors.partials.form', [
                        'floor' => $floor,
                        'buildings' => $buildings,
                        'agreements' => $agreements,
                        'projects' => $projects,
                    ])

                    <div class="floor-action-bar">
                        <a href="{{ route('floors.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Update Floor
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
