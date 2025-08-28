@extends('Partials.app', ['activeMenu' => 'drivers'])
@section('title') Driver Details @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Driver Details</h3>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th>ID</th><td>{{ $driver->id }}</td></tr>
                    <tr><th>HR ID</th><td>{{ $driver->hr_id }}</td></tr>
                    <tr><th>Name</th><td>{{ $driver->name }}</td></tr>
                    <tr><th>Sur Name</th><td>{{ $driver->sur_name }}</td></tr>
                    <tr><th>Email</th><td>{{ $driver->email }}</td></tr>
                    <tr><th>Phone</th><td>{{ $driver->phone }}</td></tr>
                    <tr><th>Gender</th><td>{{ $driver->gender }}</td></tr>
                    <tr><th>Blood Group</th><td>{{ $driver->blood_group }}</td></tr>
                    <tr><th>Marital Status</th><td>{{ $driver->marital_status }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $driver->date_of_birth }}</td></tr>
                    <tr><th>Joining Date</th><td>{{ $driver->joining_date }}</td></tr>
                    <tr><th>Employment Contract</th><td>{{ $driver->employment_contract }}</td></tr>
                    <tr><th>Contract Renewed</th><td>{{ $driver->contract_renewed ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>Confirmation Date</th><td>{{ $driver->confirmation_date }}</td></tr>
                    <tr><th>Contract End Date</th><td>{{ $driver->contract_end_date }}</td></tr>
                    <tr><th>Passport No</th><td>{{ $driver->passport_no }}</td></tr>
                    <tr><th>Designation</th><td>{{ $driver->designation }}</td></tr>
                    <tr><th>Department</th><td>{{ $driver->department }}</td></tr>
                    <tr><th>Division</th><td>{{ $driver->division }}</td></tr>
                    <tr><th>Office Location</th><td>{{ $driver->office_location }}</td></tr>
                    <tr><th>Subcenter</th><td>{{ $driver->subcenter }}</td></tr>
                    <tr><th>Job Location</th><td>{{ $driver->job_location }}</td></tr>
                    <tr><th>Supervisor Name</th><td>{{ $driver->supervisor_name }}</td></tr>
                    <tr><th>Supervisor Email</th><td>{{ $driver->supervisor_email }}</td></tr>
                    <tr><th>Supervisor HR ID</th><td>{{ $driver->supervisor_hr_id }}</td></tr>
                    <tr><th>Supervisor Company</th><td>{{ $driver->supervisor_company }}</td></tr>
                    <tr><th>Bill Reviewer Name</th><td>{{ $driver->bill_reviewer_name }}</td></tr>
                    <tr><th>Bill Reviewer Email</th><td>{{ $driver->bill_reviewer_email }}</td></tr>
                    <tr><th>Bill Reviewer HR ID</th><td>{{ $driver->bill_reviewer_hr_id }}</td></tr>
                    <tr><th>Bill Reviewer Company</th><td>{{ $driver->bill_reviewer_company }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
