@extends('Partials.app', ['activeMenu' => 'utility-types'])

@section('title')
    {{ config('app.name') }} - Add Utility Type
@endsection

@section('page_title')
    Add Utility Type
@endsection

@section('content')
    <div class="content">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2 text-center text-sm-start">
            <div class="flex-grow-1 mb-1 mb-sm-0">
                <div class="text-muted fs-sm">Facilities Management</div>
                <h1 class="h3 fw-bold mb-1">Add Utility Type</h1>
                <p class="text-muted mb-0">Create a new utility category for Agreements.</p>
            </div>
            <a href="{{ route('utility-types.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded block-bordered mt-4">
            <div class="block-header block-header-default">
                <h3 class="block-title">Utility Type Details</h3>
            </div>
            <div class="block-content fs-sm py-4">
                <form action="{{ route('utility-types.store') }}" method="POST">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-6 col-sm-12">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Guard Bill">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-8 col-sm-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" placeholder="Brief description of the utility type">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 col-sm-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active Status</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Utility Type
                        </button>
                        <a href="{{ route('utility-types.index') }}" class="btn btn-alt-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
