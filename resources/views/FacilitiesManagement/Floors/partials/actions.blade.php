<a href="{{ route('floors.show', $floor->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('floors.destroy', $floor->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger delete-button" onclick="return confirm('Are you sure?')"
        data-floor-id="{{ $floor->id }}">Delete</button>
</form>
