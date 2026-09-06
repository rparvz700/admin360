@if ($errors->any())
    <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="fa fa-exclamation-circle mt-1"></i>
        <div>
            <div class="fw-semibold">Please review the driver information:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Section 1: Personal Identity -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-id-card me-1 text-primary"></i> 1. Personal Identity
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="hr_id">HR ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                            <input type="text" class="form-control font-monospace" id="hr_id" name="hr_id" value="{{ old('hr_id', $driver->hr_id ?? '') }}" placeholder="e.g. 0542 or 1234" required>
                        </div>
                        <div class="form-text fs-xs text-muted">Leading zero will be added automatically if under 4 characters.</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="name">First / Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $driver->name ?? '') }}" placeholder="Enter driver's first or full name" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="sur_name">Surname / Nickname</label>
                        <input type="text" class="form-control" id="sur_name" name="sur_name" value="{{ old('sur_name', $driver->sur_name ?? '') }}" placeholder="Enter surname">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="nid">National ID (NID)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-address-card"></i></span>
                            <input type="text" class="form-control" id="nid" name="nid" value="{{ old('nid', $driver->nid ?? '') }}" placeholder="e.g. 19851234567890">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="gender">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            @php $currentGender = old('gender', $driver->gender ?? ''); @endphp
                            <option value="Male" {{ strcasecmp($currentGender, 'Male') === 0 ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ strcasecmp($currentGender, 'Female') === 0 ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ strcasecmp($currentGender, 'Other') === 0 ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="blood_group">Blood Group</label>
                        @php 
                            $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                            $currentBlood = old('blood_group', $driver->blood_group ?? '');
                        @endphp
                        <select class="form-select" id="blood_group" name="blood_group">
                            <option value="">Select Blood Group</option>
                            @foreach($bloodGroups as $bg)
                                <option value="{{ $bg }}" {{ strcasecmp($currentBlood, $bg) === 0 ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="marital_status">Marital Status</label>
                        @php 
                            $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];
                            $currentMarital = old('marital_status', $driver->marital_status ?? '');
                        @endphp
                        <select class="form-select" id="marital_status" name="marital_status">
                            <option value="">Select Marital Status</option>
                            @foreach($maritalStatuses as $ms)
                                <option value="{{ $ms }}" {{ strcasecmp($currentMarital, $ms) === 0 ? 'selected' : '' }}>{{ $ms }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $driver->date_of_birth ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="passport_no">Passport Number</label>
                        <input type="text" class="form-control" id="passport_no" name="passport_no" value="{{ old('passport_no', $driver->passport_no ?? '') }}" placeholder="Enter passport number">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Contact Information -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-phone-alt me-1 text-primary"></i> 2. Contact & Emergency Details
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Primary Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-phone text-primary"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $driver->phone ?? '') }}" placeholder="e.g. 01711XXXXXX">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="emergency_contact">Emergency Contact Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-phone-square text-danger"></i></span>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact', $driver->emergency_contact ?? '') }}" placeholder="e.g. 01811XXXXXX (Kin/Family)">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $driver->email ?? '') }}" placeholder="e.g. driver@summitcommunications.net">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="image_path">Photo / Avatar Path</label>
                        <input type="text" class="form-control" id="image_path" name="image_path" value="{{ old('image_path', $driver->image_path ?? '') }}" placeholder="media/avatars/driver.jpg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Employment & Placement -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-briefcase me-1 text-primary"></i> 3. Employment & Placement
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="employment_contract">Employment Contract</label>
                        <input type="text" class="form-control" id="employment_contract" name="employment_contract" value="{{ old('employment_contract', $driver->employment_contract ?? '') }}" placeholder="e.g. Permanent, Contractual">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="contract_renewed">Contract Renewed?</label>
                        <select class="form-select" id="contract_renewed" name="contract_renewed">
                            <option value="0" {{ old('contract_renewed', $driver->contract_renewed ?? 0) == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ old('contract_renewed', $driver->contract_renewed ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="joining_date">Joining Date</label>
                        <input type="date" class="form-control" id="joining_date" name="joining_date" value="{{ old('joining_date', $driver->joining_date ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="confirmation_date">Confirmation Date</label>
                        <input type="date" class="form-control" id="confirmation_date" name="confirmation_date" value="{{ old('confirmation_date', $driver->confirmation_date ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="contract_end_date">Contract End Date</label>
                        <input type="date" class="form-control" id="contract_end_date" name="contract_end_date" value="{{ old('contract_end_date', $driver->contract_end_date ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="designation">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation" value="{{ old('designation', $driver->designation ?? 'Driver') }}" placeholder="e.g. Senior Driver">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="department">Department</label>
                        <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $driver->department ?? '') }}" placeholder="e.g. Administration & Fleet">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="division">Division</label>
                        <input type="text" class="form-control" id="division" name="division" value="{{ old('division', $driver->division ?? '') }}" placeholder="e.g. Operations">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="office_location">Office Location</label>
                        <input type="text" class="form-control" id="office_location" name="office_location" value="{{ old('office_location', $driver->office_location ?? '') }}" placeholder="e.g. Head Office">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="subcenter">Subcenter</label>
                        <input type="text" class="form-control" id="subcenter" name="subcenter" value="{{ old('subcenter', $driver->subcenter ?? '') }}" placeholder="e.g. Dhaka Central">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="job_location">Job Location</label>
                        <input type="text" class="form-control" id="job_location" name="job_location" value="{{ old('job_location', $driver->job_location ?? '') }}" placeholder="e.g. Tejgaon Hub">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Supervision & Billing Reviewer -->
    <div class="col-12">
        <div class="block block-rounded block-bordered mb-0 shadow-sm">
            <div class="block-header block-header-default bg-body-light py-2">
                <h3 class="block-title fs-sm fw-bold text-uppercase">
                    <i class="fa fa-user-tie me-1 text-primary"></i> 4. Supervision & Billing Approval
                </h3>
            </div>
            <div class="block-content pt-3 pb-2">
                <div class="row">
                    <!-- Supervisor Details -->
                    <div class="col-md-6 border-end-md pb-3">
                        <h4 class="fs-sm fw-semibold text-muted text-uppercase mb-3">
                            <i class="fa fa-chevron-circle-right me-1 text-info"></i> Direct Supervisor
                        </h4>
                        <div class="mb-3">
                            <label class="form-label" for="supervisor_name">Supervisor Name</label>
                            <input type="text" class="form-control" id="supervisor_name" name="supervisor_name" value="{{ old('supervisor_name', $driver->supervisor_name ?? '') }}" placeholder="Supervisor's full name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="supervisor_hr_id">Supervisor HR ID</label>
                            <input type="text" class="form-control font-monospace" id="supervisor_hr_id" name="supervisor_hr_id" value="{{ old('supervisor_hr_id', $driver->supervisor_hr_id ?? '') }}" placeholder="e.g. 0123">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="supervisor_email">Supervisor Email</label>
                            <input type="email" class="form-control" id="supervisor_email" name="supervisor_email" value="{{ old('supervisor_email', $driver->supervisor_email ?? '') }}" placeholder="supervisor@summitcommunications.net">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="supervisor_company">Supervisor Company</label>
                            <input type="text" class="form-control" id="supervisor_company" name="supervisor_company" value="{{ old('supervisor_company', $driver->supervisor_company ?? '') }}" placeholder="Company name">
                        </div>
                    </div>

                    <!-- Bill Reviewer Details -->
                    <div class="col-md-6 pb-3">
                        <h4 class="fs-sm fw-semibold text-muted text-uppercase mb-3">
                            <i class="fa fa-chevron-circle-right me-1 text-success"></i> Bill Reviewer
                        </h4>
                        <div class="mb-3">
                            <label class="form-label" for="bill_reviewer_name">Bill Reviewer Name</label>
                            <input type="text" class="form-control" id="bill_reviewer_name" name="bill_reviewer_name" value="{{ old('bill_reviewer_name', $driver->bill_reviewer_name ?? '') }}" placeholder="Reviewer's full name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="bill_reviewer_hr_id">Bill Reviewer HR ID</label>
                            <input type="text" class="form-control font-monospace" id="bill_reviewer_hr_id" name="bill_reviewer_hr_id" value="{{ old('bill_reviewer_hr_id', $driver->bill_reviewer_hr_id ?? '') }}" placeholder="e.g. 0456">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="bill_reviewer_email">Bill Reviewer Email</label>
                            <input type="email" class="form-control" id="bill_reviewer_email" name="bill_reviewer_email" value="{{ old('bill_reviewer_email', $driver->bill_reviewer_email ?? '') }}" placeholder="reviewer@summitcommunications.net">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="bill_reviewer_company">Bill Reviewer Company</label>
                            <input type="text" class="form-control" id="bill_reviewer_company" name="bill_reviewer_company" value="{{ old('bill_reviewer_company', $driver->bill_reviewer_company ?? '') }}" placeholder="Company name">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
