@php($suggestions = app(\App\Services\DriverTruckService::class)->suggestions())
<div class="col-sm-3 mb-3">
    <label for="truck_no" class="form-label">Truck no <small class="fst-italic">(GJ03AA1234)</small> <span class="text-danger">*</span></label>
    <input type="text" name="truck_no" id="truck_no" list="truck-suggestions" autocomplete="off" class="form-control truck-no @error('truck_no') is-invalid @enderror" value="{{ old('truck_no', $model->truck?->truck_no) }}">
    <datalist id="truck-suggestions">
        @foreach($suggestions['trucks'] as $truck)
        <option value="{{ $truck->truck_no }}" data-name="{{ $truck->driver?->name }}" data-mobile="{{ $truck->driver?->mobile }}"></option>
        @endforeach
    </datalist>
    @error('truck_no')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="col-sm-3 mb-3">
    <label for="driver_name" class="form-label">Driver name <span class="text-danger">*</span></label>
    <input type="text" name="driver_name" id="driver_name" list="driver-suggestions" autocomplete="off" class="form-control driver-name @error('driver_name') is-invalid @enderror" value="{{ old('driver_name', $model->driver?->name) }}">
    <datalist id="driver-suggestions">
        @foreach($suggestions['drivers'] as $driver)
        <option value="{{ $driver->label() }}" data-name="{{ $driver->name }}" data-mobile="{{ $driver->mobile }}"></option>
        @endforeach
    </datalist>
    @error('driver_name')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="col-sm-3 mb-3">
    <label for="driver_mobile" class="form-label">Driver mobile @if($mobileRequired)<span class="text-danger">*</span>@endif</label>
    <input type="text" name="driver_mobile" id="driver_mobile" inputmode="numeric" autocomplete="off" class="form-control driver-mobile mobile-input @error('driver_mobile') is-invalid @enderror" value="{{ old('driver_mobile', $model->driver?->mobile) }}">
    @error('driver_mobile')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
