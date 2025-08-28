<a href="{{ route('vehicle-types.show', $type->id) }}" class="btn btn-info btn-sm">Show</a>
<a href="{{ route('vehicle-types.edit', $type->id) }}" class="btn btn-warning btn-sm">Edit</a>
<form action="{{ route('vehicle-types.destroy', $type->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
</form>
