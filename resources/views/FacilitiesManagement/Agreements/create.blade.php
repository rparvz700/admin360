@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Add Agreement
@endsection

@section('content')
    <div class="content">
        <div class="agreement-page-header">
            <div>
                <div class="agreement-eyebrow">Facilities Management</div>
                <h2>Create Agreement</h2>
                <p>Register a new agreement with dates, status, and operational remarks.</p>
            </div>
            <a href="{{ route('agreements.index') }}" class="btn btn-alt-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="block block-rounded agreement-shell">
            <div class="block-header block-header-default agreement-block-header">
                <div>
                    <h3 class="block-title">Agreement Details</h3>
                    <div class="text-muted fs-sm">Fields marked with <span class="text-danger">*</span> are required.</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('agreements.store') }}" method="POST" autocomplete="off">
                    @csrf
                    @include('FacilitiesManagement.Agreements.partials.form', [
                        'agreement' => $agreement,
                        'mode' => 'create',
                    ])

                    <div class="agreement-action-bar">
                        <a href="{{ route('agreements.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Save Agreement
                        </button>
                        <button type="submit" name="submit_action" value="save_and_add_attachment"
                            class="btn btn-alt-primary">
                            <i class="fa fa-paperclip me-1"></i> Save and Add Attachment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
