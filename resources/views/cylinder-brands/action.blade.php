<div class="d-flex justify-content-center gap-2">
    @can('cylinder-brands.edit')
    <a href="{{ route('cylinder-brands.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('cylinder-brands.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('cylinder-brands.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
</div>