<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="agreementActions{{ $agreement->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="agreementActions{{ $agreement->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('agreements.show', $agreement->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Edit Agreement -->
        @can('edit-agreement')
            <a class="dropdown-item py-1" href="{{ route('agreements.edit', $agreement->id) }}">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Agreement
            </a>
        @endcan

        <!-- Delete Agreement -->
        @can('delete-agreement')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('agreements.destroy', $agreement->id) }}" method="POST" id="deleteForm{{ $agreement->id }}" onsubmit="return confirm('Do you want to delete this agreement?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger py-1">
                    <i class="fa fa-trash-alt me-2"></i> Delete Agreement
                </button>
            </form>
        @endcan
    </div>
</div>
