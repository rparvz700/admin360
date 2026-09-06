<div class="btn-group">
    <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-sm btn-alt-info" data-bs-toggle="tooltip" title="View Details">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-vehicle')
        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-alt-warning" data-bs-toggle="tooltip" title="Edit Vehicle">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
    @can('delete-vehicle')
        <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete vehicle {{ $vehicle->registration_number }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-alt-danger" data-bs-toggle="tooltip" title="Delete Vehicle">
                <i class="fa fa-trash-alt"></i>
            </button>
        </form>
    @endcan
</div>

