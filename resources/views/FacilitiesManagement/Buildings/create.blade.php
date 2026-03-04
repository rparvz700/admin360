@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Building
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Building</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('buildings.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="code">Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code"
                                value="{{ old('code') }}" required>
                            @if ($errors->has('code'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('code') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="site_name">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name"
                                value="{{ old('site_name') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                value="{{ old('country') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="division">Division</label>
                            <input type="text" class="form-control" id="division" name="division"
                                value="{{ old('division') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="district">District</label>
                            <select class="form-control js-select2" id="district" name="district">
                                <option value="">Select District</option>
                                @foreach ($districts as $district)
                                    <option value="{{ $district['district'] }}">
                                        {{ $district['district'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="upazila">Upazila</label>
                            <select class="form-control js-select2" id="upazila" name="upazila">
                                <option value="">Select Upazila</option>
                                {{-- @foreach ($upazillas as $upazilla)
                                    <option value="{{ $upazilla['upazilla'] }}">
                                        {{ $upazilla['upazilla'] }}
                                    </option>
                                @endforeach --}}
                            </select>
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="area">Area</label>
                            <input type="text" class="form-control" id="area" name="area"
                                value="{{ old('area') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                value="{{ old('address') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="lat">Latitude</label>
                            <input type="text" class="form-control" id="lat" name="lat"
                                value="{{ old('lat') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="long">Longitude</label>
                            <input type="text" class="form-control" id="long" name="long"
                                value="{{ old('long') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
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

        $(document).ready(function() {

            $('#district').on('change', function() {

                let selectedDistrict = $(this).val();

                let $upazila = $('#upazila');

                // Reset dropdown
                $upazila.empty().append('<option value="">Select Upazila</option>');

                if (selectedDistrict === '') {
                    $upazila.trigger('change');
                    return;
                }

                // Filter upazilas
                let filtered = allUpazilas.filter(function(item) {
                    return item.district === selectedDistrict;
                });

                // Append filtered options
                $.each(filtered, function(index, item) {
                    $upazila.append(
                        $('<option>', {
                            value: item.upazilla,
                            text: item.upazilla
                        })
                    );
                });

                // Refresh select2
                $upazila.trigger('change');
            });

        });
    </script>
@endsection
