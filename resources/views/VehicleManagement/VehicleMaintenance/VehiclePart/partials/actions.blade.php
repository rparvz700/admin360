<a href="{{ route('maintenance.parts.show', $part->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('maintenance.parts.edit', $part->id) }}" class="btn btn-sm btn-primary">Edit</a>
<form action="{{ route('maintenance.parts.destroy', $part->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>