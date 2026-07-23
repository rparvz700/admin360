<a href="{{ route('generic-document-attributes.show', $attribute->id) }}" class="btn btn-sm btn-info">View</a>
@can('edit-generic-document-attribute')
    <a href="{{ route('generic-document-attributes.edit', $attribute->id) }}" class="btn btn-sm btn-primary">Edit</a>
@endcan
@can('delete-generic-document-attribute')
    <form action="{{ route('generic-document-attributes.destroy', $attribute->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan
