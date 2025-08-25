<a href="{{ route('asset-categories.show', $category->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('asset-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('asset-categories.destroy', $category->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger delete-button" data-category-id="{{ $category->id }}">Delete</button>
</form>
