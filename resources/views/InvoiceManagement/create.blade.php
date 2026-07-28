


@extends('Partials.app', ['activeMenu' => isset($rent) && $rent ? 'rent' : 'maintenance'])
@section('title') Add Invoice @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">
                Add Invoice
                @if(isset($maintenance) && $maintenance)
                    <small class="text-muted fs-6 ms-2">
                        for {{ $maintenance->vehicle->registration_number ?? '' }} — {{ $maintenance->start_datetime->format('d M Y') }}
                    </small>
                @elseif(isset($rent) && $rent)
                    <small class="text-muted fs-6 ms-2">
                        for Rent Agreement: {{ $rent->agreement->agreement_ref_no ?? 'N/A' }}
                    </small>
                @endif
            </h3>
            <a href="{{ isset($maintenance) && $maintenance ? route('maintenance.maintenances.index') : (isset($rent) && $rent ? route('rent.index') : route('invoices.index')) }}"
               class="btn btn-secondary btn-sm float-end">Back</a>
        </div>
        <div class="block-content">
            
            @if(isset($maintenance) && $maintenance)
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-1"></i>
                    Creating invoice for maintenance record.
                    Vendor and amount have been pre-filled. After saving you will be redirected back to maintenance.
                </div>
            @elseif(isset($rent) && $rent)
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-1"></i>
                    Creating invoice for rent record (Agreement: {{ $rent->agreement->agreement_ref_no ?? 'N/A' }}).
                    Vendor and amount have been pre-filled. After saving you will be redirected back to rent.
                </div>
            @endif
            <form action="{{ route('invoices.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                @if(isset($maintenance->id))
                    <input type="hidden" name="maintenance_id" value="{{ $maintenance->id }}">
                @endif
                @if(isset($rent->id))
                    <input type="hidden" name="rent_id" value="{{ $rent->id }}">
                @endif
                @include('InvoiceManagement.form', [
                    'invoice'     => null,
                    'vendors'     => $vendors,
                    'maintenance' => $maintenance ?? null,
                    'rent'        => $rent ?? null,
                ])
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Invoice</button>
                    <a href="{{ isset($maintenance) && $maintenance ? route('maintenance.maintenances.index') : (isset($rent) && $rent ? route('rent.index') : route('invoices.index')) }}"
                       class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection