<div class="row">
    <div class="col-sm-4 mb-3">
        <label for="role" class="form-label">Role</label>
        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
            <option value="">Select</option>
            @foreach($roles as $role)
            <option value="{{ $role->id }}" {{ (old('role', $user->role ?? '') == $role->id) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
        @error('role')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}">
        @error('name')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}">
        @error('email')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" value="">
        @error('password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="is_active" class="form-label">Is Active</label>
        <div class="form-check form-switch">
            <input class="form-check-input float-none" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ ($user->is_active == 1) ? 'checked' : '' }} style="cursor: pointer;">
        </div>
    </div>
</div>