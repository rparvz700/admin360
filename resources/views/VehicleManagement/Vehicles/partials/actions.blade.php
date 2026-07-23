<a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-vehicle')
    <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-primary">Edit</a>
@endcan
@can('delete-vehicle')
    <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan
