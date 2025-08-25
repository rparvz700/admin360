@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Agreement Details
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Agreement Details</h3>
        </div>
        <div class="block-content fs-sm data-content">
            <section class="mb-4 p-3 border rounded bg-light">
                <h5 class="mb-3">Agreement Info</h5>
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Reference No</label>
                        <div class="form-control-plaintext">{{ $agreement->agreement_ref_no }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Agreement Date</label>
                        <div class="form-control-plaintext">{{ $agreement->agreement_date }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">From Date</label>
                        <div class="form-control-plaintext">{{ $agreement->from_date }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">To Date</label>
                        <div class="form-control-plaintext">{{ $agreement->to_date }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Status</label>
                        <div class="form-control-plaintext">{{ $agreement->status }}</div>
                    </div>
                    <div class="col-md-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <div class="form-control-plaintext">{{ $agreement->remarks }}</div>
                    </div>
                </div>
            </section>
            <a href="{{ route('agreements.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
