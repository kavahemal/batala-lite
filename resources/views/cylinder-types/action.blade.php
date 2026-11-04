<div class="d-flex justify-content-center gap-2">
    @can('cylinder-types.edit')
    <a href="{{ route('cylinder-types.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('cylinder-types.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('cylinder-types.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
</div>