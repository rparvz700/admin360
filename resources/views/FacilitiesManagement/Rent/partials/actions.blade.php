<a href="{{ route('rent.show', $row->id) }}" class="btn btn-info btn-sm">View</a>
<a href="{{ route('rent.edit', $row->id) }}" class="btn btn-warning btn-sm">Edit</a>
<form action="{{ route('rent.destroy', $row->id) }}" method="POST" style="display:inline-block">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
</form>
