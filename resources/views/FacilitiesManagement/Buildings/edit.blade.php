@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Edit Building
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="building-page-header">
            <div>
                <div class="building-eyebrow">Facilities Management</div>
                <h2>Edit Building</h2>
                <p>Maintain building identity, administrative location, and coordinates.</p>
            </div>
            <div class="building-header-actions">
                <span class="badge bg-primary">{{ $building->code }}</span>
                <a href="{{ route('buildings.index') }}" class="btn btn-alt-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="block block-rounded building-shell">
            <div class="block-header block-header-default building-block-header">
                <div>
                    <h3 class="block-title">{{ $building->site_name ?: 'Building Profile' }}</h3>
                    <div class="text-muted fs-sm">Last updated {{ optional($building->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('buildings.update', $building->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('FacilitiesManagement.Buildings.partials.form', [
                        'building' => $building,
                        'districts' => $districts,
                        'mode' => 'edit',
                    ])

                    <div class="building-action-bar">
                        <a href="{{ route('buildings.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Update Building
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

        const allUpazilas = @json($upazillas);
        const selectedUpazila = @json(old('upazila', $building->upazila));

        $(document).ready(function() {
            function loadUpazilas(district, preselected = null) {
                let $upazila = $('#upazila');
                $upazila.empty().append('<option value="">Select Upazila</option>');

                if (!district) {
                    $upazila.prop('disabled', true).trigger('change');
                    return;
                }

                let filtered = allUpazilas.filter(function(item) {
                    return item.district.trim().toLowerCase() === district.trim().toLowerCase();
                });

                $.each(filtered, function(index, item) {
                    $upazila.append(new Option(item.upazilla, item.upazilla, false, false));
                });

                $upazila.prop('disabled', false);

                if (preselected) {
                    $upazila.val(preselected);
                }

                $upazila.trigger('change');
            }

            $('#district').on('change', function() {
                loadUpazilas($(this).val());
            });

            loadUpazilas($('#district').val(), selectedUpazila);
        });
    </script>
@endsection
