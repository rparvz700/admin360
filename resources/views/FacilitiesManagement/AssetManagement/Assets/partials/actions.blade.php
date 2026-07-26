<div class="btn-group btn-group-sm" role="group" aria-label="Asset actions">
    <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    @can('edit-asset')
        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-alt-warning">
            <i class="fa fa-pencil-alt"></i>
        </a>
    @endcan
    @can('delete-asset')
        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display:inline-block;"
            onsubmit="return confirm('Do you want to delete this asset?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-alt-danger">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
