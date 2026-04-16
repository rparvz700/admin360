@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    Property Setup Wizard
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('js/plugins/select2/css/select2.min.css') }}">
    <style>
        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 1px solid #ebedef;
            padding-bottom: 20px;
        }

        .step {
            text-align: center;
            flex: 1;
            position: relative;
            color: #ccc;
        }

        .step.active {
            color: #0665d0;
            font-weight: bold;
        }

        .step.completed {
            color: #28a745;
        }

        .step-number {
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: #eee;
            display: inline-block;
            margin-bottom: 5px;
        }

        .step.active .step-number {
            background: #0665d0;
            color: #fff;
        }

        .step.completed .step-number {
            background: #28a745;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Complete Property Setup</h3>
            </div>
            <div class="block-content">
                <div class="step-indicator">
                    <div class="step active" id="ind-1"><span class="step-number">1</span><br>Agreement</div>
                    <div class="step" id="ind-2"><span class="step-number">2</span><br>Building</div>
                    <div class="step" id="ind-3"><span class="step-number">3</span><br>Floor</div>
                    <div class="step" id="ind-4"><span class="step-number">4</span><br>Rent & Deposits</div>
                </div>

                <form action="{{ route('wizard.property.store') }}" method="POST" id="wizardForm" autocomplete="off">
                    @csrf

                    <!-- STEP 1: Agreement -->
                    <div class="wizard-step active" id="step-1">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Agreement Reference No <span class="text-danger">*</span></label>
                                <input type="text" name="agreement_ref_no" class="form-control" required
                                    value="{{ old('agreement_ref_no') }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Agreement Date</label>
                                <input type="date" name="agreement_date" class="form-control"
                                    value="{{ old('agreement_date') }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ old('from_date') }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ old('to_date') }}">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="agreement_status" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Remarks</label>
                                <textarea name="agreement_remarks" class="form-control">{{ old('agreement_remarks') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Building -->
                    <div class="wizard-step" id="step-2">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Building Code <span class="text-danger">*</span></label>
                                <input type="text" name="building_code" class="form-control" required
                                    value="{{ old('building_code') }}">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Site Name</label>
                                <input type="text" name="site_name" class="form-control">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">District</label>
                                <select class="form-control js-select2" id="district" name="district">
                                    <option value="">Select District</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district['district'] }}">{{ $district['district'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Upazila</label>
                                <select class="form-control js-select2" id="upazila" name="upazila"></select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="lat" class="form-control">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="long" class="form-control">
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Floor -->
                    <div class="wizard-step" id="step-3">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Floor Label</label>
                                <input type="text" name="floor_label" class="form-control"
                                    placeholder="e.g. 1st Floor">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Floor Area (sft)</label>
                                <input type="number" step="0.01" name="floor_area_sft" class="form-control">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Car Parking</label>
                                <input type="number" name="car_parking" class="form-control">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">DG Space (sft)</label>
                                <input type="number" step="0.01" name="dg_space_sft" class="form-control">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Store Space (sft)</label>
                                <input type="number" step="0.01" name="store_space_sft" class="form-control">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Premises Type</label>
                                {{-- <input type="text" name="premises_type" class="form-control"> --}}
                                <select class="form-control" id="premises_type" name="premises_type">
                                    <option value="">Select Premises Type</option>
                                    <option value="Office Room">Office Room</option>
                                    <option value="PoP Room">PoP Room</option>
                                    <option value="DG Room">DG Room</option>
                                    <option value="Store Room">Store Room</option>
                                    <option value="Power Room">Power Room</option>
                                    <option value="Client Room">Client Room</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label" for="project">Project</label>
                                <select class="form-control" id="project" name="project_id">
                                    <option value="">Select project</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Rent -->
                    <div class="wizard-step" id="step-4">
                        <section class="mb-4 p-3 border rounded bg-light">
                            <h5 class="mb-3">Base Rent</h5>
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Base Rent <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="base_rent" id="base_rent"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Rent Type</label>
                                    <select class="form-control" name="rent_type">
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Half Yearly">Half Yearly</option>
                                        <option value="Yearly">Yearly</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Is At Source? <span class="text-danger">*</span></label>
                                    <select class="form-control" name="is_at_source" required>
                                        <option value="">Select</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="mb-4 p-3 border rounded bg-light">
                            <h5>Rent Increments</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="incrementsTable">
                                    <thead>
                                        <tr>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Amount</th>
                                            <th>%</th>
                                            <th>Method Desc</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="addIncrement">Add
                                Increment</button>
                        </section>

                        <section class="mb-4 p-3 border rounded bg-light">
                            <h5>Security Deposits</h5>
                            <div class="row mb-3">
                                <div class="col-md-4"><label>Total</label><input type="number" step="0.01"
                                        name="security_deposit_total" class="form-control"></div>
                                <div class="col-md-4"><label>Absorbable</label><input type="number" step="0.01"
                                        name="security_deposit_absorbable" class="form-control"></div>
                                <div class="col-md-4"><label>Non-Absorbable</label><input type="number" step="0.01"
                                        name="security_deposit_non_absorbable" class="form-control"></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="depositsTable">
                                    <thead>
                                        <tr>
                                            <th>Absorb Amount</th>
                                            <th>Absorb %</th>
                                            <th>Absorb Start</th>
                                            <th>Absorb End</th>
                                            <th>Method Desc</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="addDeposit">Add Deposit</button>
                        </section>
                    </div>

                    <!-- Buttons -->
                    <div class="block-content block-content-full block-content-sm bg-body-light mt-4 text-end">
                        <button type="button" class="btn btn-secondary" id="btn-prev" onclick="changeStep(-1)"
                            disabled>Previous</button>
                        <button type="button" class="btn btn-primary" id="btn-next"
                            onclick="changeStep(1)">Next</button>
                        <button type="submit" class="btn btn-success d-none" id="btn-submit">Save Everything</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function changeStep(n) {
            if (n === 1 && !validateCurrentStep()) return;

            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`ind-${currentStep}`).classList.remove('active');
            if (n === 1) document.getElementById(`ind-${currentStep}`).classList.add('completed');

            currentStep += n;

            document.getElementById(`step-${currentStep}`).classList.add('active');
            document.getElementById(`ind-${currentStep}`).classList.add('active');

            document.getElementById('btn-prev').disabled = (currentStep === 1);
            if (currentStep === totalSteps) {
                document.getElementById('btn-next').classList.add('d-none');
                document.getElementById('btn-submit').classList.remove('d-none');
            } else {
                document.getElementById('btn-next').classList.remove('d-none');
                document.getElementById('btn-submit').classList.add('d-none');
            }
        }

        function validateCurrentStep() {
            const activeStep = document.getElementById(`step-${currentStep}`);
            const inputs = activeStep.querySelectorAll('[required]');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                One.helpers('jq-notify', {
                    type: 'danger',
                    icon: 'fa fa-times me-1',
                    message: 'Please fill required fields!'
                });
            }
            return valid;
        }

        $(document).ready(function() {
            $('.js-select2').select2({
                width: '100%'
            });

            const allUpazilas = @json($upazillas);
            $('#district').on('change', function() {
                let selectedDistrict = $(this).val();
                let $upazila = $('#upazila');
                $upazila.empty().append('<option value="">Select Upazila</option>');
                let filtered = allUpazilas.filter(item => item.district === selectedDistrict);
                filtered.forEach(item => $upazila.append(new Option(item.upazilla, item.upazilla)));
            });

            let incIdx = 0;
            $('#addIncrement').click(function() {
                $('#incrementsTable tbody').append(`
                    <tr>
                        <td><input type="date" name="increments[${incIdx}][increment_start_date]" class="form-control" required></td>
                        <td><input type="date" name="increments[${incIdx}][increment_end_date]" class="form-control"></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_amount]" class="form-control inc-amount" required></td>
                        <td><input type="number" step="0.01" name="increments[${incIdx}][increment_percentage]" class="form-control inc-percent"></td>
                        <td><input type="text" name="increments[${incIdx}][method_description]" class="form-control"></td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">X</button></td>
                    </tr>
                `);
                incIdx++;
            });

            let depIdx = 0;
            $('#addDeposit').click(function() {
                $('#depositsTable tbody').append(`
                    <tr>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][absorb_amount]" class="form-control abs-amount"></td>
                        <td><input type="number" step="0.01" name="deposits[${depIdx}][absorb_amount_percentage]" class="form-control abs-percent"></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_start_date]" class="form-control"></td>
                        <td><input type="date" name="deposits[${depIdx}][absorb_end_date]" class="form-control"></td>
                        <td><input type="text" name="deposits[${depIdx}][method_description]" class="form-control"></td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()">X</button></td
                    </tr>
                `);
                depIdx++;
            });

            // Logic to calculate percentages for both Increments and Deposits
            $(document).on('input', '.inc-amount, .abs-amount', function() {
                let base = parseFloat($('#base_rent').val()) || 0;
                let amt = parseFloat($(this).val()) || 0;
                let targetClass = $(this).hasClass('inc-amount') ? '.inc-percent' : '.abs-percent';
                if (base > 0) $(this).closest('tr').find(targetClass).val(((amt / base) * 100).toFixed(2));
            });

            $(document).on('input', '.inc-percent, .abs-percent', function() {
                let base = parseFloat($('#base_rent').val()) || 0;
                let percent = parseFloat($(this).val()) || 0;
                let targetClass = $(this).hasClass('inc-percent') ? '.inc-amount' : '.abs-amount';
                if (base > 0) $(this).closest('tr').find(targetClass).val(((percent / 100) * base).toFixed(
                    2));
            });
        });
    </script>
@endsection
