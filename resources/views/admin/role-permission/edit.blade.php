@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Permissions for Role: <b>{{ ucfirst($role->name) }}</b></h2>
        <a class="btn btn-info" href="{{ route('role-permissions.index') }}"><i class="me-1" data-feather="arrow-left" style="margin-top: -2px;"></i> Back</a>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- <div class="card-header">
                <h5 class="card-title mb-0">Empty card</h5>
            </div> -->
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-center">View</th>
                            <th class="text-center">Create</th>
                            <th class="text-center">Edit</th>
                            <th class="text-center">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissionsConfig as $resource => $menu)
                        <tr>
                            @if(!isset($menu['permissions']))
                            <th colspan="5">- {{ $menu['title'] }}</th>
                            @else
                            <td>{{ $menu['title'] }}</td>
                            @foreach($menu['permissions'] as $action => $permission)
                            <td class="text-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input float-none permission-checkbox" type="checkbox" role="switch"
                                        data-resource="{{ $resource }}"
                                        data-action="{{ $action }}"
                                        data-permission="{{ $permission }}"
                                        value="{{ $permission }}" style="cursor: pointer;"
                                        @if(in_array($permission, $assignedPermissions)) checked @endif>
                                </div>
                            </td>
                            @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('.permission-checkbox').change(function() {
        var _this = $(this);
        var _checked = $(this).is(':checked');
        $(this).blur();
        $.ajax({
            url: "{{ route('role-permissions.updatePermissions', $role->id) }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                permission: $(this).val(),
                status: +_checked,
            },
            success: function(response) {
                if (response.status) {
                    showToast(response.message);
                } else {
                    showToast((response.message || 'Error updating permission'), 'error');
                    _this.prop('checked', !_checked);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    });
</script>
@endpush