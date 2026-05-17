@extends('Partials.app', ['activeMenu' => 'vat-taxes'])

@section('title') Add VAT/TAX @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Add VAT/TAX</h3>
            <a href="{{ route('vat-taxes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <form action="{{ route('vat-taxes.store') }}" method="POST" autocomplete="off">
                @csrf
                @include('InvoiceManagement.VatTaxes.form', ['vatTax' => null])
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection
