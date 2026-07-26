<div class="btn-group btn-group-sm" role="group" aria-label="Agreement actions">
    <a href="{{ route('agreements.show', $agreement->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-agreement')
        <a href="{{ route('agreements.edit', $agreement->id) }}" class="btn btn-alt-warning">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
</div>
@can('delete-agreement')
    <form action="{{ route('agreements.destroy', $agreement->id) }}" method="POST" style="display:inline-block;"
        onsubmit="return confirm('Do you want to delete this agreement?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-alt-danger">
            <i class="fa fa-trash"></i>
        </button>
    </form>
@endcan
