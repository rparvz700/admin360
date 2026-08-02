<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="buildingActions{{ $building->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="buildingActions{{ $building->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('buildings.show', $building->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Edit Building -->
        @can('edit-building')
            <a class="dropdown-item py-1" href="{{ route('buildings.edit', $building->id) }}">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Building
            </a>
        @endcan

        <!-- Delete Building -->
        @can('delete-building')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" id="deleteForm{{ $building->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="dropdown-item text-danger py-1 delete-button" data-building-id="{{ $building->id }}">
                    <i class="fa fa-trash-alt me-2"></i> Delete Building
                </button>
            </form>
        @endcan
    </div>
</div>
