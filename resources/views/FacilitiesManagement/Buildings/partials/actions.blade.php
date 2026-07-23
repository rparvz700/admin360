<div class="btn-group btn-group-sm" role="group" aria-label="Building actions">
    <a href="{{ route('buildings.show', $building->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-building')
        <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-alt-warning">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
</div>
@can('delete-building')
    <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" style="display:inline-block;"
        id="deleteForm{{ $building->id }}">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm btn-alt-danger delete-button" data-building-id="{{ $building->id }}">
            <i class="fa fa-trash"></i>
        </button>
    </form>
@endcan
