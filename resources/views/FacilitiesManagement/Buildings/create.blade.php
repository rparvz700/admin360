@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Building
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/building-location-map.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="building-page-header">
            <div>
                <div class="building-eyebrow">Facilities Management</div>
                <h2>Create Building</h2>
                <p>Add a site with location details for facilities and asset tracking.</p>
            </div>
            <a href="{{ route('buildings.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded building-shell">
            <div class="block-header block-header-default building-block-header">
                <div>
                    <h3 class="block-title">Building Profile</h3>
                    <div class="text-muted fs-sm">Fields marked with <span class="text-danger">*</span> are required.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('buildings.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @include('FacilitiesManagement.Buildings.partials.form', [
                        'building' => null,
                        'divisions' => $divisions,
                        'districts' => $districts,
                        'mode' => 'create',
                    ])

                    <div class="building-action-bar">
                        <a href="{{ route('buildings.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Building
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/building-location-map.js') }}"></script>
    <script>
        One.helpersOnLoad(['jq-select2']);

        const allUpazilas = @json($upazillas);
        const allDistricts = @json($districts);
        const selectedDivision = @json(old('division'));
        const selectedDistrict = @json(old('district'));
        const selectedUpazila = @json(old('upazila'));

        $(document).ready(function() {
            function getDivisionName(item) {
                return item.division || item.division_name || item.name || '';
            }

            function getDistrictName(item) {
                return item.district || item.district_name || item.name || '';
            }

            function getUpazilaName(item) {
                return item.upazilla || item.upazila || item.name || '';
            }

            function loadDistricts(division, preselected = null) {
                let $district = $('#district');
                $district.empty().append('<option value="">Select District</option>');
                loadUpazilas('', null);

                if (!division) {
                    $district.prop('disabled', true).trigger('change');
                    return;
                }

                const hasDivisionData = allDistricts.some(function(item) {
                    return getDivisionName(item);
                });
                let filtered = allDistricts.filter(function(item) {
                    if (!hasDivisionData) return true;
                    return getDivisionName(item).trim().toLowerCase() === division.trim().toLowerCase();
                });

                $.each(filtered, function(index, item) {
                    const district = getDistrictName(item);
                    if (district) {
                        $district.append(new Option(district, district, false, false));
                    }
                });

                $district.prop('disabled', false);

                if (preselected) {
                    $district.val(preselected);
                }

                $district.trigger('change');
            }

            function loadUpazilas(district, preselected = null) {
                let $upazila = $('#upazila');
                $upazila.empty().append('<option value="">Select Upazila</option>');

                if (!district) {
                    $upazila.prop('disabled', true).trigger('change');
                    return;
                }

                let filtered = allUpazilas.filter(function(item) {
                    return (item.district || '').trim().toLowerCase() === district.trim().toLowerCase();
                });

                $.each(filtered, function(index, item) {
                    const upazila = getUpazilaName(item);
                    if (upazila) {
                        $upazila.append(new Option(upazila, upazila, false, false));
                    }
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

            $('#division').on('change', function() {
                loadDistricts($(this).val());
            });

            if (selectedDivision) {
                $('#division').val(selectedDivision).trigger('change.select2');
            }

            loadDistricts($('#division').val(), selectedDistrict);
            if (selectedDistrict) {
                loadUpazilas(selectedDistrict, selectedUpazila);
            }
        });
    </script>
@endsection
