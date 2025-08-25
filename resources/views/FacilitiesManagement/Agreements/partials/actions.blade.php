<a href="{{ route('agreements.edit', $agreement->id) }}" class="btn btn-sm btn-warning">Edit</a>
<form action="{{ route('agreements.destroy', $agreement->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>
