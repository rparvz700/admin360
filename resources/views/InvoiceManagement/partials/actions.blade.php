<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="invoiceActions{{ $invoice->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="invoiceActions{{ $invoice->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('invoices.show', $invoice->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Record Payment -->
        @if($invoice->payment_status !== 'paid')
            @can('edit-invoice')
                <a class="dropdown-item py-1 btn-record-payment" href="javascript:void(0)"
                   data-invoice-id="{{ $invoice->id }}"
                   data-invoice-number="{{ $invoice->invoice_number }}"
                   data-outstanding="{{ number_format($invoice->getOutstandingAmount(), 2, '.', '') }}"
                   data-total="{{ number_format($invoice->total_amount, 2, '.', '') }}">
                    <i class="fa fa-credit-card text-success me-2"></i> Record Payment
                </a>
            @endcan
        @endif

        <!-- PDF Export -->
        <a class="dropdown-item py-1" href="{{ route('invoices.print', $invoice->id) }}" target="_blank">
            <i class="fa fa-file-pdf text-danger me-2"></i> Save / Print PDF
        </a>

        <!-- Edit Invoice -->
        @can('edit-invoice')
            @if($invoice->payment_status !== 'paid')
                <a class="dropdown-item py-1" href="{{ route('invoices.edit', $invoice->id) }}">
                    <i class="fa fa-pencil-alt text-primary me-2"></i> Edit Invoice
                </a>
            @endif
        @endcan

        <!-- View Uploaded Voucher File -->
        @if($invoice->invoice_file_path)
            <a class="dropdown-item py-1" href="{{ asset('storage/' . $invoice->invoice_file_path) }}" target="_blank">
                <i class="fa fa-paperclip text-secondary me-2"></i> View Attachment
            </a>
        @endif

        @can('delete-invoice')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger py-1" onclick="return confirm('Are you sure you want to delete this invoice?')">
                    <i class="fa fa-trash-alt me-2"></i> Delete Invoice
                </button>
            </form>
        @endcan
    </div>
</div>