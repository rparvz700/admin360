@can('edit-project')
    <a href="{{ route('projects.edit', $row->id) }}" class="btn btn-sm btn-warning">Edit</a>
@endcan
@can('delete-project')
    <form action="{{ route('projects.destroy', $row->id) }}" method="POST" style="display:inline-block;"
        onsubmit="return confirm('Are you sure you want to delete this project?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger delete-button" data-row-id="{{ $row->id }}">Delete</button>
    </form>
@endcan
