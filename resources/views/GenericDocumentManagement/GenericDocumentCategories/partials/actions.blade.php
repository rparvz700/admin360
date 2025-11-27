<a href="{{ route('generic-document-categories.show', $category->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('generic-document-categories.edit', $category->id) }}" class="btn btn-sm btn-primary">Edit</a>
<form action="{{ route('generic-document-categories.destroy', $category->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>
