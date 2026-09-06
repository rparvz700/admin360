<div class="btn-group btn-group-sm" role="group" aria-label="Vehicle Type Actions">
    <a href="{{ route('vehicle-types.show', $type->id) }}" 
       class="btn btn-alt-info" 
       data-bs-toggle="tooltip" 
       title="View Details">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-vehicle-type')
        <a href="{{ route('vehicle-types.edit', $type->id) }}" 
           class="btn btn-alt-warning" 
           data-bs-toggle="tooltip" 
           title="Edit Type">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
    @can('delete-vehicle-type')
        <form action="{{ route('vehicle-types.destroy', $type->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Are you sure you want to delete vehicle type {{ addslashes($type->type_name) }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-alt-danger rounded-0 rounded-end" 
                    data-bs-toggle="tooltip" 
                    title="Delete Type">
                <i class="fa fa-trash-alt"></i>
            </button>
        </form>
    @endcan
</div>
