<a href="{{ route('generic-documents.show', $doc->id) }}" class="btn btn-sm btn-info"><i class="si si-eye"></i></a>
<a href="{{ route('generic-documents.edit', $doc->id) }}" class="btn btn-sm btn-warning"><i class="far fa-pen-to-square"></i></a>
<form action="{{ route('generic-documents.destroy', $doc->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="far fa-trash-can"></i></button>
</form>
