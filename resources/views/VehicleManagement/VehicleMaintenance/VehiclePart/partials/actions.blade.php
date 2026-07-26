<a href="{{ route('maintenance.parts.show', $part->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-vehicle-part')
    <a href="{{ route('maintenance.parts.edit', $part->id) }}" class="btn btn-sm btn-primary">Edit</a>
@endcan
@can('delete-vehicle-part')
    <form action="{{ route('maintenance.parts.destroy', $part->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan