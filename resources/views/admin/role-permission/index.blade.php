@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="container-fluid p-0">
    <h2 class="mb-3">Roles</h2>

    <div class="row">
        <div class="col-12">
            <div class="card">
                {{---<div class="card-header">
                    <h5 class="card-title mb-0">Empty card</h5>
                </div>---}}
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td>{{ ucfirst($role->name) }}</td>
                                <td class="text-center"><a class="btn btn-primary" href="{{ route('role-permissions.edit', $role->id) }}">Update permissions</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection