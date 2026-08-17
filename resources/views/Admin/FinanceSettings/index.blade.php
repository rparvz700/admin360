@extends('Partials.app', ['activeMenu' => 'admin.finance-settings'])

@section('title')
    Finance Settings - {{ config('app.name') }}
@endsection

@section('page_title')
    Finance & Discount Rate Settings
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded border shadow-sm">
            <div class="block-header block-header-default bg-light">
                <h3 class="block-title fs-sm font-semibold text-uppercase text-muted">
                    <i class="fa fa-sliders-h me-1"></i> Global Finance Configuration
                </h3>
            </div>
            <div class="block-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.finance-settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <p class="text-muted fs-sm">Configure global financial parameters used across Property Management calculations (e.g. NPV discount rates).</p>
                    </div>

                    @foreach ($settings as $index => $setting)
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <input type="hidden" name="settings[{{ $index }}][id]" value="{{ $setting->id }}">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h5 class="card-title fs-base mb-1">{{ $setting->label }}</h5>
                                        @if ($setting->description)
                                            <p class="card-text fs-xs text-muted mb-0">{{ $setting->description }}</p>
                                        @endif
                                        <div class="mt-1">
                                            <span class="badge bg-secondary fs-xs">Key: {{ $setting->key }}</span>
                                            <span class="badge bg-info-light text-info fs-xs">Group: {{ strtoupper($setting->group) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-5 mt-2 mt-md-0">
                                        <label class="form-label fs-xs font-semibold">Configured Value</label>
                                        <div class="input-group">
                                            <input type="number" step="0.0001" min="0" max="100" 
                                                name="settings[{{ $index }}][value_numeric]" 
                                                class="form-control fw-bold text-primary" 
                                                value="{{ old("settings.{$index}.value_numeric", (float) $setting->value_numeric) }}" required>
                                            <span class="input-group-text font-semibold fs-xs">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mb-3">
                        <a href="{{ route('facilities.npv.index') }}" class="btn btn-alt-secondary">
                            <i class="fa fa-calculator me-1"></i> Go to NPV Calculation
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Save Finance Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
