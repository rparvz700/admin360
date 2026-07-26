<a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-info btn-sm">Show</a>
@can('edit-driver')
    <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning btn-sm">Edit</a>
@endcan
@can('delete-driver')
    <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan
