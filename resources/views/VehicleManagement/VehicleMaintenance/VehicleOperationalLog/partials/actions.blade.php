<a href="{{ route('maintenance.operational-logs.show', $log->id) }}" class="btn btn-sm btn-info">View</a>
<form action="{{ route('maintenance.operational-logs.destroy', $log->id) }}" method="POST" style="display:inline-block;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
</form>