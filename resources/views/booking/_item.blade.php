<tr>
    <td class="text-center fw-bold"></td>
    <td>
        <select name="brand_id[]" class="form-select item-brand @error('brand_id.' . $index) is-invalid @enderror">
            <option value="">Select</option>
            @foreach($cylinderBrands as $brandId => $brandName)
            <option value="{{ $brandId }}" {{ $oldBrandId == $brandId ? 'selected' : '' }}>{{ $brandName }}</option>
            @endforeach
        </select>
        @error('brand_id.' . $index)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </td>
    <td>
        <select name="cylinder_type_id[]" class="form-select item-type @error('cylinder_type_id.' . $index) is-invalid @enderror">
            <option value="">Select</option>
            @foreach($cylinderTypes as $brandType)
            <option value="{{ $brandType->id }}" class="d-none" data-brand_id="{{ $brandType->brand_id }}" {{ $oldCylinderTypeId == $brandType->id ? 'selected' : '' }} >{{ $brandType->weight }} kg</option>
            @endforeach
        </select>
        @error('cylinder_type_id.' . $index)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </td>
    <td>
        <input type="text" name="quantity[]" class="form-control item-quantity @error('quantity.' . $index) is-invalid @enderror" value="{{ $oldQuantity }}">
        @error('quantity.' . $index)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </td>
    <td class="item-available"></td>
    <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm rounded-3 item-remove">X</button>
    </td>
</tr>