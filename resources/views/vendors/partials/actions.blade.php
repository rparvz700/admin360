<div class="dropdown d-inline-block">
    <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" id="vendorActions{{ $vendor->id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end fs-sm py-1" aria-labelledby="vendorActions{{ $vendor->id }}">
        <!-- View Details -->
        <a class="dropdown-item py-1" href="{{ route('vendors.show', $vendor->id) }}">
            <i class="fa fa-eye text-info me-2"></i> View Details
        </a>

        <!-- Edit Vendor -->
        @can('edit-vendor')
            <a class="dropdown-item py-1" href="{{ route('vendors.edit', $vendor->id) }}">
                <i class="fa fa-pencil-alt text-warning me-2"></i> Edit Vendor
            </a>
        @endcan

        <!-- Delete Vendor -->
        @can('delete-vendor')
            <div class="dropdown-divider my-1"></div>
            <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" id="vendorDeleteForm{{ $vendor->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="dropdown-item text-danger py-1 js-vendor-delete" data-vendor-id="{{ $vendor->id }}">
                    <i class="fa fa-trash-alt me-2"></i> Delete Vendor
                </button>
            </form>
        @endcan
    </div>
</div>
