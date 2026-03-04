<a href="{{ route('projects.edit', $row->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('projects.destroy', $row->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger delete-button" data-row-id="{{ $row->id }}">Delete</button>
</form>
