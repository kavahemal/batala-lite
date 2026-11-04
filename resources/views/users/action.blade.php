<div class="d-flex justify-content-center gap-2">
    @can('users.edit')
    <a href="{{ route('users.edit', $id) }}" class="btn btn-info">Edit</a>
    @endcan
    @can('users.destroy')
    <button type="button" class="btn btn-danger btn-delete" data-url="{{ route('users.destroy', $id) }}" data-dt-reload="true">Delete</button>
    @endcan
</div>