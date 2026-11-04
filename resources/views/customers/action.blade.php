<div class="d-flex justify-content-center gap-2">
    @can('customers.edit')
    <a href="{{ route('customers.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('customers.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('customers.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
</div>