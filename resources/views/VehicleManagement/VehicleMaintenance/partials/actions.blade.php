<a href="{{ route('maintenance.maintenances.show', $maintenance->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('maintenance.maintenances.edit', $maintenance->id) }}" class="btn btn-sm btn-primary">Edit</a>
@if(!$maintenance->approved_by)
<form action="{{ route('maintenance.maintenances.approve', $maintenance->id) }}" method="POST" style="display:inline-block;">
    @csrf
    <button type="submit" class="btn btn-sm btn-success">Approve</button>
</form>
@endif
<form action="{{ route('maintenance.maintenances.destroy', $maintenance->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>