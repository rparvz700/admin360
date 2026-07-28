<div class="btn-group btn-group-sm" role="group" aria-label="Rent actions">
    <a href="{{ route('rent.show', $row->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-rent')
        <a href="{{ route('rent.edit', $row->id) }}" class="btn btn-alt-warning">
            <i class="fa fa-pencil-alt"></i>
        </a>
        @if($row->invoice_id)
            <a href="{{ route('invoices.show', $row->invoice_id) }}" class="btn btn-alt-success" title="View Invoice">
                <i class="fa fa-file-invoice"></i>
            </a>
        @else
            <a href="{{ route('invoices.create', ['rent_id' => $row->id]) }}" class="btn btn-alt-secondary" title="Create Invoice">
                <i class="fa fa-file-invoice"></i>
            </a>
        @endif
    @endcan
</div>
@can('delete-rent')
    <form action="{{ route('rent.destroy', $row->id) }}" method="POST" style="display:inline-block"
        onsubmit="return confirm('Do you want to delete this rent record?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-alt-danger btn-sm">
            <i class="fa fa-trash"></i>
        </button>
    </form>
@endcan
