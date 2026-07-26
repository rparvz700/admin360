<a href="{{ route('asset-categories.show', $category->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-asset-category')
    <a href="{{ route('asset-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
@endcan
@can('delete-asset-category')
    <form action="{{ route('asset-categories.destroy', $category->id) }}" method="POST" style="display:inline-block;"
        onsubmit="return confirm('Are you sure you want to delete this category?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger delete-button"
            data-category-id="{{ $category->id }}">Delete</button>
    </form>
@endcan
