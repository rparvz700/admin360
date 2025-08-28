<a href="{{ route('vehicle-documents.show', $doc->id) }}" class="btn btn-sm btn-info">View</a>
<a href="{{ route('vehicle-documents.edit', $doc->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('vehicle-documents.destroy', $doc->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>
