<div class="btn-group btn-group-sm" role="group" aria-label="Rent actions">
    <a href="{{ route('rent.show', $row->id) }}" class="btn btn-alt-info">
        <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('rent.edit', $row->id) }}" class="btn btn-alt-warning">
        <i class="fa fa-pencil-alt"></i>
    </a>
</div>
<form action="{{ route('rent.destroy', $row->id) }}" method="POST" style="display:inline-block"
    onsubmit="return confirm('Do you want to delete this rent record?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-alt-danger btn-sm">
        <i class="fa fa-trash"></i>
    </button>
</form>
