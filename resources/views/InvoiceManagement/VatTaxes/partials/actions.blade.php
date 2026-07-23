<a href="{{ route('vat-taxes.show', $vatTax->id) }}" class="btn btn-info btn-sm">Show</a>
@can('edit-vat-tax')
    <a href="{{ route('vat-taxes.edit', $vatTax->id) }}" class="btn btn-warning btn-sm">Edit</a>
@endcan
@can('delete-vat-tax')
    <form action="{{ route('vat-taxes.destroy', $vatTax->id) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@endcan
