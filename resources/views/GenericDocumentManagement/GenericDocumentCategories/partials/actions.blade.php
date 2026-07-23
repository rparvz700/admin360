<a href="{{ route('generic-document-categories.show', $category->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-generic-document-category')
    <a href="{{ route('generic-document-categories.edit', $category->id) }}" class="btn btn-sm btn-primary">Edit</a>
@endcan
@can('delete-generic-document-category')
    <form action="{{ route('generic-document-categories.destroy', $category->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan
