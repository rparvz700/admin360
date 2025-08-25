@extends('Partials.app', ['activeMenu' => 'rent'])

@section('title')
    {{ env('APP_NAME') }}
@endsection

@section('page_title')
    Add Rent
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add Rent</h3>
            </div>
            <div class="block-content fs-sm data-content">
                <form class="mb-4" action="{{ route('rent.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <!-- Add Rent Section -->
                    <section class="mb-4 p-3 border rounded bg-light">
                        <h5 class="mb-3">Base Rent</h5>
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="agreement_id">Agreement</label>
                                <input type="text" class="form-control" id="agreement_id" name="agreement_id" value="{{ old('agreement_id') }}" required>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="base_rent">Base Rent</label>
                                <input type="number" step="0.01" class="form-control" id="base_rent" name="base_rent" value="{{ old('base_rent') }}" required>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="vat">VAT</label>
                                <input type="number" step="0.01" class="form-control" id="vat" name="vat" value="{{ old('vat') }}">
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="tax">Tax</label>
                                <input type="number" step="0.01" class="form-control" id="tax" name="tax" value="{{ old('tax') }}">
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="is_at_source">Is At Source</label>
                                <select class="form-control" id="is_at_source" name="is_at_source">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_at_source') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('is_at_source') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="rent_type">Rent Type</label>
                                <input type="text" class="form-control" id="rent_type" name="rent_type" value="{{ old('rent_type') }}">
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-4">
                                <label class="form-label" for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}">
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="remarks">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Rent Increments Section -->
                    <section class="mb-4 p-3 border rounded bg-light">
                        <h5 class="mb-3">Rent Increments</h5>
                        <table class="table table-bordered" id="incrementsTable">
                            <thead>
                                <tr>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Amount</th>
                                    <th>Percentage</th>
                                    <th>Method Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="addIncrement">Add Increment</button>
                    </section>

                    <!-- Security Deposits Section -->
                    <section class="mb-4 p-3 border rounded bg-light">
                        <h5 class="mb-3">Security Deposits</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Total</label>
                                <input type="number" step="0.01" name="security_deposit_total" class="form-control" value="{{ old('security_deposit_total') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_absorbable" class="form-control" value="{{ old('security_deposit_absorbable') }}">
                            </div>
                            <div class="col-md-4">
                                <label>Non-Absorbable</label>
                                <input type="number" step="0.01" name="security_deposit_non_absorbable" class="form-control" value="{{ old('security_deposit_non_absorbable') }}">
                            </div>
                        </div>
                        <table class="table table-bordered" id="depositsTable">
                            <thead>
                                <tr>
                                    <th>Absorb Amount</th>
                                    <th>Absorb %</th>
                                    <th>Absorb Start</th>
                                    <th>Absorb End</th>
                                    <th>Absorb Freq</th>
                                    <th>Method Desc</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="addDeposit">Add Deposit</button>
                    </section>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery) {
        $('#addIncrement').click(function() {
            $('#incrementsTable tbody').append(`
                <tr>
                    <td><input type="date" name="increments[][increment_start_date]" class="form-control" required></td>
                    <td><input type="date" name="increments[][increment_end_date]" class="form-control"></td>
                    <td><input type="number" step="0.01" name="increments[][increment_amount]" class="form-control" required></td>
                    <td><input type="number" step="0.01" name="increments[][increment_percentage]" class="form-control"></td>
                    <td><input type="text" name="increments[][method_description]" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-increment">Remove</button></td>
                </tr>
            `);
        });
        $(document).on('click', '.remove-increment', function() {
            $(this).closest('tr').remove();
        });
        $('#addDeposit').click(function() {
        $('#depositsTable tbody').append(`
            <tr>
                <td><input type="number" step="0.01" name="deposits[][absorb_amount]" class="form-control"></td>
                <td><input type="number" step="0.01" name="deposits[][absorb_amount_percentage]" class="form-control"></td>
                <td><input type="date" name="deposits[][absorb_start_date]" class="form-control"></td>
                <td><input type="date" name="deposits[][absorb_end_date]" class="form-control"></td>
                <td><input type="text" name="deposits[][absorb_frequency]" class="form-control"></td>
                <td><input type="text" name="deposits[][method_description]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-deposit">Remove</button></td>
            </tr>
        `);
        });
        $(document).on('click', '.remove-deposit', function() {
            $(this).closest('tr').remove();
        });
    }
});
</script>
@endsection
