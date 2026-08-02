<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="floorActions{{ $floor->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="floorActions{{ $floor->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('floors.show', $floor->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Edit Floor -->
        @can('edit-floor')
            <a class="dropdown-item py-1" href="{{ route('floors.edit', $floor->id) }}">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Floor
            </a>
        @endcan

        <!-- Delete Floor -->
        @can('delete-floor')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('floors.destroy', $floor->id) }}" method="POST" id="deleteForm{{ $floor->id }}" onsubmit="return confirm('Do you want to delete this floor?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger py-1 delete-button" data-floor-id="{{ $floor->id }}">
                    <i class="fa fa-trash-alt me-2"></i> Delete Floor
                </button>
            </form>
        @endcan
    </div>
</div>
