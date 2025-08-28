<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label" for="hr_id">HR ID<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="hr_id" name="hr_id" value="{{ old('hr_id', $driver->hr_id ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="name">Name<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $driver->name ?? '') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="sur_name">Sur Name</label>
            <input type="text" class="form-control" id="sur_name" name="sur_name" value="{{ old('sur_name', $driver->sur_name ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $driver->email ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="phone">Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $driver->phone ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="gender">Gender</label>
            <input type="text" class="form-control" id="gender" name="gender" value="{{ old('gender', $driver->gender ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="blood_group">Blood Group</label>
            <input type="text" class="form-control" id="blood_group" name="blood_group" value="{{ old('blood_group', $driver->blood_group ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="marital_status">Marital Status</label>
            <input type="text" class="form-control" id="marital_status" name="marital_status" value="{{ old('marital_status', $driver->marital_status ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="date_of_birth">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $driver->date_of_birth ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="joining_date">Joining Date</label>
            <input type="date" class="form-control" id="joining_date" name="joining_date" value="{{ old('joining_date', $driver->joining_date ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="employment_contract">Employment Contract</label>
            <input type="text" class="form-control" id="employment_contract" name="employment_contract" value="{{ old('employment_contract', $driver->employment_contract ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="image_path">Image Path</label>
            <input type="text" class="form-control" id="image_path" name="image_path" value="{{ old('image_path', $driver->image_path ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label" for="contract_renewed">Contract Renewed</label>
            <select class="form-control" id="contract_renewed" name="contract_renewed">
                <option value="0" {{ old('contract_renewed', $driver->contract_renewed ?? 0) == 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ old('contract_renewed', $driver->contract_renewed ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="confirmation_date">Confirmation Date</label>
            <input type="date" class="form-control" id="confirmation_date" name="confirmation_date" value="{{ old('confirmation_date', $driver->confirmation_date ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="contract_end_date">Contract End Date</label>
            <input type="date" class="form-control" id="contract_end_date" name="contract_end_date" value="{{ old('contract_end_date', $driver->contract_end_date ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="passport_no">Passport No</label>
            <input type="text" class="form-control" id="passport_no" name="passport_no" value="{{ old('passport_no', $driver->passport_no ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="designation">Designation</label>
            <input type="text" class="form-control" id="designation" name="designation" value="{{ old('designation', $driver->designation ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="department">Department</label>
            <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $driver->department ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="division">Division</label>
            <input type="text" class="form-control" id="division" name="division" value="{{ old('division', $driver->division ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="office_location">Office Location</label>
            <input type="text" class="form-control" id="office_location" name="office_location" value="{{ old('office_location', $driver->office_location ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="subcenter">Subcenter</label>
            <input type="text" class="form-control" id="subcenter" name="subcenter" value="{{ old('subcenter', $driver->subcenter ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="job_location">Job Location</label>
            <input type="text" class="form-control" id="job_location" name="job_location" value="{{ old('job_location', $driver->job_location ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="supervisor_name">Supervisor Name</label>
            <input type="text" class="form-control" id="supervisor_name" name="supervisor_name" value="{{ old('supervisor_name', $driver->supervisor_name ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="supervisor_email">Supervisor Email</label>
            <input type="email" class="form-control" id="supervisor_email" name="supervisor_email" value="{{ old('supervisor_email', $driver->supervisor_email ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="supervisor_hr_id">Supervisor HR ID</label>
            <input type="text" class="form-control" id="supervisor_hr_id" name="supervisor_hr_id" value="{{ old('supervisor_hr_id', $driver->supervisor_hr_id ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="supervisor_company">Supervisor Company</label>
            <input type="text" class="form-control" id="supervisor_company" name="supervisor_company" value="{{ old('supervisor_company', $driver->supervisor_company ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="bill_reviewer_name">Bill Reviewer Name</label>
            <input type="text" class="form-control" id="bill_reviewer_name" name="bill_reviewer_name" value="{{ old('bill_reviewer_name', $driver->bill_reviewer_name ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="bill_reviewer_email">Bill Reviewer Email</label>
            <input type="email" class="form-control" id="bill_reviewer_email" name="bill_reviewer_email" value="{{ old('bill_reviewer_email', $driver->bill_reviewer_email ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="bill_reviewer_hr_id">Bill Reviewer HR ID</label>
            <input type="text" class="form-control" id="bill_reviewer_hr_id" name="bill_reviewer_hr_id" value="{{ old('bill_reviewer_hr_id', $driver->bill_reviewer_hr_id ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label" for="bill_reviewer_company">Bill Reviewer Company</label>
            <input type="text" class="form-control" id="bill_reviewer_company" name="bill_reviewer_company" value="{{ old('bill_reviewer_company', $driver->bill_reviewer_company ?? '') }}">
        </div>
    </div>
</div>
