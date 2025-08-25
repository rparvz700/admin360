<div class="btn-group" role="group">
    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-sm btn-info">View</a>
    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-sm btn-primary">Edit</a>
    <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
