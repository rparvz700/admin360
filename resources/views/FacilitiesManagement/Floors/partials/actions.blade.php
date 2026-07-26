<div class="btn-group btn-group-sm" role="group" aria-label="Floor actions">
    <a href="{{ route('floors.show', $floor->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-floor')
        <a href="{{ route('floors.edit', $floor->id) }}" class="btn btn-alt-warning">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
</div>
@can('delete-floor')
    <form action="{{ route('floors.destroy', $floor->id) }}" method="POST" style="display:inline-block;"
        onsubmit="return confirm('Do you want to delete this floor?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-alt-danger delete-button" data-floor-id="{{ $floor->id }}">
            <i class="fa fa-trash"></i>
        </button>
    </form>
@endcan
