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
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Edit Building</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('buildings.update', $building->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="code">Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code"
                                value="{{ old('code', $building->code) }}" required>
                            @if ($errors->has('code'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('code') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="site_name">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name"
                                value="{{ old('site_name', $building->site_name) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                value="{{ old('country', $building->country) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="division">Division</label>
                            <input type="text" class="form-control" id="division" name="division"
                                value="{{ old('division', $building->division) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="district">District</label>
                            {{-- <input type="text" class="form-control" id="district" name="district" value="{{ old('district', $building->district) }}"> --}}
                            <select class="form-control js-select2" id="district" name="district">
                                <option value="">Select District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district['district'] }}"
                                        {{ isset($building) && $building->district == $district['district'] ? 'selected' : '' }}>
                                        {{ $district['district'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="upazila">Upazila</label>
                            <select class="form-control js-select2" id="upazila" name="upazila">
                                <option value="">Select Upazila</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="area">Area</label>
                            <input type="text" class="form-control" id="area" name="area"
                                value="{{ old('area', $building->area) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="{{ old('address', $building->address) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="lat">Latitude</label>
                            <input type="text" class="form-control" id="lat" name="lat"
                                value="{{ old('lat', $building->lat) }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="long">Longitude</label>
                            <input type="text" class="form-control" id="long" name="long"
                                value="{{ old('long', $building->long) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
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
        const selectedUpazila = "{{ isset($building) ? $building->upazila : '' }}";

        $(document).ready(function() {

            function loadUpazilas(district, preselected = null) {

                let $upazila = $('#upazila');
                $upazila.empty().append('<option value="">Select Upazila</option>');

                if (!district) {
                    $upazila.prop('disabled', true);
                    $upazila.trigger('change');
                    return;
                }

                let filtered = allUpazilas.filter(function(item) {
                    return item.district.trim().toLowerCase() ===
                        district.trim().toLowerCase();
                });

                $.each(filtered, function(index, item) {
                    let option = new Option(item.upazilla, item.upazilla, false, false);
                    $upazila.append(option);
                });

                $upazila.prop('disabled', false);

                if (preselected) {
                    $upazila.val(preselected);
                }

                $upazila.trigger('change');
            }

            // On change
            $('#district').on('change', function() {
                loadUpazilas($(this).val());
            });

            // 🔥 Critical Part: On page load (Edit case)
            let initialDistrict = $('#district').val();

            if (initialDistrict) {
                loadUpazilas(initialDistrict, selectedUpazila);
            } else {
                $('#upazila').prop('disabled', true);
            }

        });
    </script>
@endsection
