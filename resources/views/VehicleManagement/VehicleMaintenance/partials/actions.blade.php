<a href="{{ route('maintenance.maintenances.show', $maintenance->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('maintenance.maintenances.edit', $maintenance->id) }}" class="btn btn-sm btn-primary">Edit</a>

@if($maintenance->invoice_id)
    <a href="{{ route('invoices.show', $maintenance->invoice_id) }}" class="btn btn-sm btn-success">
        <i class="fa fa-file-invoice"></i> Invoice
    </a>
@else
    <a href="{{ route('invoices.create', ['maintenance_id' => $maintenance->id]) }}" class="btn btn-sm btn-warning">
        <i class="fa fa-plus"></i> Invoice
    </a>
@endif

@if(!$maintenance->approved_by)
<form action="{{ route('maintenance.maintenances.approve', $maintenance->id) }}" method="POST" style="display:inline-block;">
    @csrf
    <button type="submit" class="btn btn-sm btn-secondary">Approve</button>
</form>
@endif

<form action="{{ route('maintenance.maintenances.destroy', $maintenance->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>