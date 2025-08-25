<div class="btn-group">
    @if (!empty($editUrl))
        <a href="{{ $editUrl }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Edit/View">
            <i class="fa fa-fw fa-pencil-alt"></i>
        </a>
    @endif

    <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="View">
        <i class="fa fa-fw fa-eye"></i>
    </button>

    @if (!empty($approveUrl))
        <button type="button" class="btn btn-sm btn-alt-primary approveDeposit" data-bs-toggle="tooltip"
            title="Approve" data-targe="{{ $approveUrl }}">
            <i class="fa fa-fw fa-check"></i>
        </button>
    @endif

</div>
