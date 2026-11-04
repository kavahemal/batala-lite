<div class="row">
    <div class="col-sm-4 mb-3">
        <label for="brand_id" class="form-label">Brand</label>
        <select name="brand_id" id="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
            <option value="">Select</option>
            @foreach($brands as $brand)
            <option value="{{ $brand->id }}" {{ ($cylinderType->brand_id == $brand->id) ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
        @error('brand_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="weight" class="form-label">Weight</label>
        <input type="text" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $cylinderType->weight ?? '') }}">
        @error('weight')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="is_active" class="form-label">Is Active</label>
        <div class="form-check form-switch">
            <input class="form-check-input float-none" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ ($cylinderType->is_active == 1) ? 'checked' : '' }} style="cursor: pointer;">
        </div>
    </div>

    <div class="col-sm-12 mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $cylinderType->description ?? '') }}</textarea>
    </div>
</div>