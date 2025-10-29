<a href="{{ route('vehicle-document-attributes.show', $attribute->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('vehicle-document-attributes.edit', $attribute->id) }}" class="btn btn-sm btn-primary">Edit</a>
<form action="{{ route('vehicle-document-attributes.destroy', $attribute->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>
