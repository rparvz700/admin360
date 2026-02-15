<a href="{{ route('maintenance.vendors.show', $vendor->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('maintenance.vendors.edit', $vendor->id) }}" class="btn btn-sm btn-primary">Edit</a>
<form action="{{ route('maintenance.vendors.destroy', $vendor->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>