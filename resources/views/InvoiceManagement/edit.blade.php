@extends('Partials.app', ['activeMenu' => 'maintenance'])
@section('title') Edit Invoice @endsection
@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit Invoice - {{ $invoice->invoice_number }}</h3>
            <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            @if($invoice->payment_status === 'paid')
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    This invoice is marked as <strong>Paid</strong>. Editing may affect financial records.
                </div>
            @endif
            <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                @method('PUT')
                @include('InvoiceManagement.form', ['invoice' => $invoice, 'vendors' => $vendors])
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection