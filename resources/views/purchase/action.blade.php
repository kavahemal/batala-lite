<div class="d-flex justify-content-center gap-2">
    @can('purchase.index')
    <a href="{{ route('purchase.show', $id) }}" class="btn btn-primary">View</a>
    @endcan
    @can('purchase.edit')
    <a href="{{ route('purchase.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('purchase.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('purchase.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
</div>