<div class="btn-group btn-group-sm" role="group" aria-label="Driver Actions">
    <a href="{{ route('drivers.show', $driver->id) }}" 
       class="btn btn-alt-info" 
       data-bs-toggle="tooltip" 
       title="View Details">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-driver')
        <a href="{{ route('drivers.edit', $driver->id) }}" 
           class="btn btn-alt-warning" 
           data-bs-toggle="tooltip" 
           title="Edit Driver">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
    @can('delete-driver')
        <form action="{{ route('drivers.destroy', $driver->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Are you sure you want to delete driver {{ addslashes($driver->name) }} (HR ID: {{ $driver->hr_id }})? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-alt-danger rounded-0 rounded-end" 
                    data-bs-toggle="tooltip" 
                    title="Delete Driver">
                <i class="fa fa-trash-alt"></i>
            </button>
        </form>
    @endcan
</div>
