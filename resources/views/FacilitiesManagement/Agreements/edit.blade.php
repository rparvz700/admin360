@extends('Partials.app', ['activeMenu' => 'agreements'])

@section('title')
    {{ config('app.name') }}
@endsection

@section('page_title')
    Edit Agreement
@endsection

@section('content')
    <div class="content">
        <div class="agreement-page-header">
            <div>
                <div class="agreement-eyebrow">Facilities Management</div>
                <h2>Edit Agreement</h2>
                <p>Update agreement details and manage supporting documents.</p>
            </div>
            <div class="agreement-header-actions">
                <span class="badge {{ $agreement->status == 1 ? 'bg-success' : 'bg-danger' }}">
                    {{ $agreement->status == 1 ? 'Active' : 'Inactive' }}
                </span>
                <a href="{{ route('agreements.show', $agreement) }}" class="btn btn-alt-secondary">
                    <i class="fa fa-eye me-1"></i> View
                </a>
            </div>
        </div>

        <div class="block block-rounded agreement-shell">
            <div class="block-header block-header-default agreement-block-header">
                <div>
                    <h3 class="block-title">{{ $agreement->agreement_ref_no }}</h3>
                    <div class="text-muted fs-sm">Last updated {{ optional($agreement->updated_at)->format('Y-m-d H:i') }}</div>
                </div>
            </div>
            <div class="block-content fs-sm data-content">
                <form action="{{ route('agreements.update', $agreement->id) }}" method="POST" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('FacilitiesManagement.Agreements.partials.form', [
                        'agreement' => $agreement,
                        'documents' => $documents,
                        'mode' => 'edit',
                    ])

                    <div class="agreement-action-bar">
                        <a href="{{ route('agreements.index') }}" class="btn btn-alt-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check me-1"></i> Update Agreement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
