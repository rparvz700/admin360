@php
    $hasInvoices = ($row->invoice_id || (isset($row->invoices) && $row->invoices->count() > 0));
    $latestInvoiceId = $row->invoice_id ?? ($row->invoices->first()->id ?? null);
@endphp

<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="rentActions{{ $row->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="rentActions{{ $row->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('rent.show', $row->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Edit Rent -->
        @can('edit-rent')
            <a class="dropdown-item py-1" href="{{ route('rent.edit', $row->id) }}">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Rent
            </a>

            <!-- Invoice Link -->
            @if($hasInvoices)
                <a class="dropdown-item py-1" href="{{ route('invoices.show', $latestInvoiceId) }}">
                    <i class="fa fa-file-invoice text-success me-2"></i> View Latest Invoice
                </a>
            @else
                <a class="dropdown-item py-1" href="{{ route('invoices.create', ['rent_id' => $row->id]) }}">
                    <i class="fa fa-file-invoice text-primary me-2"></i> Create Invoice
                </a>
            @endif
        @endcan

        <!-- Delete Rent -->
        @can('delete-rent')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('rent.destroy', $row->id) }}" method="POST" id="deleteForm{{ $row->id }}" onsubmit="return confirm('Do you want to delete this rent record?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger py-1">
                    <i class="fa fa-trash-alt me-2"></i> Delete Rent
                </button>
            </form>
        @endcan
    </div>
</div>
