@extends('Partials.app', ['activeMenu' => 'buildings'])

@section('title')
    {{ config('app.name') }} 
@endsection

@section('page_title')
    Add Building
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
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
                            @if ($errors->has('code'))
                                <div class="text-danger">
                                    <small>{{ $errors->first('code') }}</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="site_name">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="{{ old('site_name') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="{{ old('country') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="division">Division</label>
                            <input type="text" class="form-control" id="division" name="division" value="{{ old('division') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="district">District</label>
                            <input type="text" class="form-control" id="district" name="district" value="{{ old('district') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="upazila">Upazila</label>
                            <input type="text" class="form-control" id="upazila" name="upazila" value="{{ old('upazila') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="area">Area</label>
                            <input type="text" class="form-control" id="area" name="area" value="{{ old('area') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="lat">Latitude</label>
                            <input type="text" class="form-control" id="lat" name="lat" value="{{ old('lat') }}">
                        </div>
                        <div class="col-md-6 col-sm-12 mb-4">
                            <label class="form-label" for="long">Longitude</label>
                            <input type="text" class="form-control" id="long" name="long" value="{{ old('long') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
