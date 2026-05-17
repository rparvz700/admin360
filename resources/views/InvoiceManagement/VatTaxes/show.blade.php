@extends('Partials.app', ['activeMenu' => 'vat-taxes'])

@section('title') VAT/TAX Details @endsection

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">VAT/TAX Details</h3>
            <a href="{{ route('vat-taxes.index') }}" class="btn btn-secondary btn-sm float-end">Back to List</a>
        </div>
        <div class="block-content">
            <table class="table table-bordered">
                <tbody>
                    <tr><th width="30%">ID</th><td>{{ $vatTax->id }}</td></tr>
                    <tr><th>Type</th><td>{{ $vatTax->type }}</td></tr>
                    <tr><th>VAT</th><td>{{ $vatTax->vat !== null ? number_format((float) $vatTax->vat, 2) : '-' }}</td></tr>
                    <tr><th>TAX</th><td>{{ $vatTax->tax !== null ? number_format((float) $vatTax->tax, 2) : '-' }}</td></tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if ($vatTax->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Created At</th><td>{{ optional($vatTax->created_at)->format('d M Y h:i A') }}</td></tr>
                    <tr><th>Updated At</th><td>{{ optional($vatTax->updated_at)->format('d M Y h:i A') }}</td></tr>
                </tbody>
            </table>
            <a href="{{ route('vat-taxes.edit', $vatTax->id) }}" class="btn btn-warning">Edit</a>
        </div>
    </div>
</div>
@endsection
