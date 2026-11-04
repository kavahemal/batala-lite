<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $cylinderBrand->name ?? '') }}">
        @error('name')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-6 mb-3">
        <label for="is_active" class="form-label">Is Active</label>
        <div class="form-check form-switch">
            <input class="form-check-input float-none" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ ($cylinderBrand->is_active == 1) ? 'checked' : '' }} style="cursor: pointer;">
        </div>
    </div>

    <div class="col-sm-6 mb-3">
        <label for="image" class="form-label">Brand Image</label>
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.gif">
        @error('image')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
        @if($cylinderBrand->image)
        <div class="mt-2">
            <img src="{{ asset('storage/' . $cylinderBrand->image) }}" alt="Current Image" class="img-fluid border rounded-3" style="max-width: 150px;">
        </div>
        <div class="mt-2 form-check">
            <input class="form-check-input" type="checkbox" name="delete_image" id="delete_image" value="1">
            <label class="form-check-label" for="delete_image">Delete image</label>
        </div>
        @endif
    </div>
</div>