@extends('Partials.app', ['activeMenu' => 'change-password'])

@section('title') Change Password - {{ config('app.name') }} @endsection

@section('page_title')
    Change Password
@endsection

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title"><i class="fa fa-key text-primary me-2"></i> Change Password</h3>
                </div>
                <div class="block-content">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <i class="fa fa-exclamation-triangle me-1"></i> Please fix the errors below.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update-password') }}" method="POST" autocomplete="off">
                        @csrf
                        
                        <!-- Current Password -->
                        <div class="mb-4">
                            <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" placeholder="Enter your current password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Your current account password for security verification.</small>
                        </div>

                        <hr class="my-4">

                        <!-- New Password -->
                        <div class="mb-4">
                            <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Enter new password (min. 8 characters)" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Re-enter new password" required>
                        </div>

                        <div class="mb-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
