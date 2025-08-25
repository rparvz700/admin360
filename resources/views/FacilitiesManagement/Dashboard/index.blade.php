{{-- @extends('Partials.app', ['activeMenu' => $activeMenu]) --}}
@extends('Partials.app')

@section('title')
    {{ env('APP_NAME') }}
@endsection


@section('content')
    <!-- Hero -->
    <div class="content">
        <div
            class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1 mb-1 mb-md-0">
                <h1 class="h3 fw-bold mb-1">
                    Dashboard
                </h1>
                <h2 class="h6 fw-medium fw-medium text-muted mb-0">
                    Welcome <a class="fw-semibold" href="#"></a>, everything looks great.
                </h2>
            </div>

        </div>
    </div>

    <!-- Cards Row -->
    <div class="row">
        <div class="col-md-3 col-6 mb-4">
            <a class="block block-rounded block-link-pop text-center h-100" href="#">
                <div class="block-content py-4">
                    <div class="fs-2 fw-bold text-primary mb-2">
                        <i class="fas fa-building fa-2x mb-2"></i><br>
                        {{ $buildingsCount }}
                    </div>
                    <div class="fw-semibold text-muted">Buildings</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-4">
            <a class="block block-rounded block-link-pop text-center h-100" href="#">
                <div class="block-content py-4">
                    <div class="fs-2 fw-bold text-success mb-2">
                        <i class="fas fa-layer-group fa-2x mb-2"></i><br>
                        {{ $floorsCount }}
                    </div>
                    <div class="fw-semibold text-muted">Floors</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-4">
            <a class="block block-rounded block-link-pop text-center h-100" href="#">
                <div class="block-content py-4">
                    <div class="fs-2 fw-bold text-info mb-2">
                        <i class="fas fa-snowflake fa-2x mb-2"></i><br>
                        {{ $airConditionsCount }}
                    </div>
                    <div class="fw-semibold text-muted">Air Conditions</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-4">
            <a class="block block-rounded block-link-pop text-center h-100" href="#">
                <div class="block-content py-4">
                    <div class="fs-2 fw-bold text-danger mb-2">
                        <i class="fas fa-file-contract fa-2x mb-2"></i><br>
                        {{ $agreementsExpiring }}
                    </div>
                    <div class="fw-semibold text-muted">Agreements Expiring This Month</div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6 mb-4">
            <a class="block block-rounded block-link-pop text-center h-100" href="#">
                <div class="block-content py-4">
                    <div class="fs-2 fw-bold text-secondary mb-2">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i><br>
                        {{ $pendingPayments }}
                    </div>
                    <div class="fw-semibold text-muted">Pending Payments</div>
                </div>
            </a>
        </div>
    </div>
    <!-- END Cards Row -->

    <!-- END Page Content -->
@endsection

@section('scripts')
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/plugins/chart.js/chart.umd.js') }}"></script>

    <!-- Page JS Code -->
    <script src="{{ asset('js/pages/be_pages_dashboard.min.js') }}"></script>
@endsection
