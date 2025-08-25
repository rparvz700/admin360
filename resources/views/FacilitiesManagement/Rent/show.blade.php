
@extends('Partials.app', ['activeMenu' => 'rent'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Rent Details
@endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Rent Details</h3>
        </div>
        <div class="block-content fs-sm data-content">
            <!-- Base Rent Section -->
            <section class="mb-4 p-3 border rounded bg-light">
                <h5 class="mb-3">Base Rent</h5>
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Agreement ID</label>
                        <div class="form-control-plaintext">{{ $base->agreement_id }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Base Rent</label>
                        <div class="form-control-plaintext">{{ $base->base_rent }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">VAT</label>
                        <div class="form-control-plaintext">{{ $base->vat }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Tax</label>
                        <div class="form-control-plaintext">{{ $base->tax }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Is At Source</label>
                        <div class="form-control-plaintext">{{ $base->is_at_source ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Rent Type</label>
                        <div class="form-control-plaintext">{{ $base->rent_type }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">Start Date</label>
                        <div class="form-control-plaintext">{{ $base->start_date }}</div>
                    </div>
                    <div class="col-md-6 col-sm-12 mb-4">
                        <label class="form-label">End Date</label>
                        <div class="form-control-plaintext">{{ $base->end_date }}</div>
                    </div>
                    <div class="col-md-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <div class="form-control-plaintext">{{ $base->remarks }}</div>
                    </div>
                </div>
            </section>

            <!-- Rent Increments Section -->
            <section class="mb-4 p-3 border rounded bg-light">
                <h5 class="mb-3">Rent Increments</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Percentage</th>
                            <th>Method Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($base->increments as $inc)
                        <tr>
                            <td>{{ $inc->increment_start_date }}</td>
                            <td>{{ $inc->increment_end_date }}</td>
                            <td>{{ $inc->increment_amount }}</td>
                            <td>{{ $inc->increment_percentage }}</td>
                            <td>{{ $inc->method_description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <!-- Security Deposits Section -->
            <section class="mb-4 p-3 border rounded bg-light">
                <h5 class="mb-3">Security Deposits</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Total</th>
                            <th>Absorbable</th>
                            <th>Non-Absorbable</th>
                            <th>Absorb Start</th>
                            <th>Absorb End</th>
                            <th>Absorb Amount</th>
                            <th>Absorb %</th>
                            <th>Absorb Freq</th>
                            <th>Method Desc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($base->securityDeposits as $dep)
                        <tr>
                            <td>{{ $dep->security_deposit_total }}</td>
                            <td>{{ $dep->security_deposit_absorbable }}</td>
                            <td>{{ $dep->security_deposit_non_absorbable }}</td>
                            <td>{{ $dep->absorb_start_date }}</td>
                            <td>{{ $dep->absorb_end_date }}</td>
                            <td>{{ $dep->absorb_amount }}</td>
                            <td>{{ $dep->absorb_amount_percentage }}</td>
                            <td>{{ $dep->absorb_frequency }}</td>
                            <td>{{ $dep->method_description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <a href="{{ route('rent.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
