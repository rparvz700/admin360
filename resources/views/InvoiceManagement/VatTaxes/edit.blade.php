@extends('Partials.app', ['activeMenu' => 'vat-taxes'])

@section('title') Edit VAT/TAX @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Edit VAT/TAX</h3>
            <a href="{{ route('vat-taxes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vat-taxes.update', $vatTax->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                @include('InvoiceManagement.VatTaxes.form', ['vatTax' => $vatTax])
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection
