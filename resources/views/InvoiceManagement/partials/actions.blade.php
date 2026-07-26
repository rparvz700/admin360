<a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-invoice')
    @if($invoice->payment_status !== 'paid')
        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-sm btn-primary">Edit</a>
    @endif
@endcan
@if($invoice->invoice_file_path)
    <a href="{{ asset('storage/' . $invoice->invoice_file_path) }}" target="_blank" class="btn btn-sm btn-secondary">
        <i class="fa fa-file"></i>
    </a>
@endif
@can('delete-invoice')
    <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan