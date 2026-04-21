<a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('buildings.destroy', $building->id) }}" method="POST" style="display:inline-block;"
    id="deleteForm{{ $building->id }}">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-sm btn-danger delete-button"
        data-building-id="{{ $building->id }}">Delete</button>
</form>
