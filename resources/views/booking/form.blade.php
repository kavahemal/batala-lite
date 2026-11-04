<div class="row">
    <div class="col-sm-4 mb-3">
        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
        <select name="customer_id" id="customer_id" class="form-select customer-select @error('customer_id') is-invalid @enderror">
            @include('customers._options', ['selected' => old('customer_id', $booking->customer_id)])
        </select>
        @error('customer_id')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
            <option value="">Select</option>
            @foreach($statues as $status)
            <option value="{{ $status->value }}" {{ (old('status', $booking->status?->value) == $status->value) ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="booking_date" class="form-label">Booking date <span class="text-danger">*</span></label>
        <input type="text" name="booking_date" id="booking_date" class="form-control flat-datepicker @error('booking_date') is-invalid @enderror" value="{{ old('booking_date', $booking->booking_date) }}">
        @error('booking_date')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4 mb-3">
        <label for="scheduled_date" class="form-label">Scheduled date</label>
        <input type="text" name="scheduled_date" id="scheduled_date" class="form-control flat-datepicker @error('scheduled_date') is-invalid @enderror" value="{{ old('scheduled_date', $booking->scheduled_date) }}">
        @error('scheduled_date')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-12 mb-3">
        <label for="remarks" class="form-label">Remarks <span class="text-danger d-none" id="remarks-required">* (required: some cylinders are short of stock)</span></label>
        <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4">{{ old('remarks', $booking->remarks ?? '') }}</textarea>
        @error('remarks')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-12 mt-2">
        <h3 class="mb-3">Cylinders
            <button type="button" class="btn btn-info btn-sm rounded-3 add-item">Add</button>
        </h3>

        @error('brand_id')
        <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div class="alert alert-warning py-2 d-none" id="short-stock-alert">
            Some cylinders are more than the available stock. You can still book - add a note in <strong>Remarks</strong>.
        </div>

        <table class="table table-bordered" id="items">
            <thead class="table-info">
                <tr>
                    <th width="80"></th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th width="260">Available</th>
                    <th width="40" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- Submitted rows win over saved rows, so a failed save keeps what was typed --}}
                @if(old('brand_id') !== null)
                    @foreach(old('brand_id', []) as $index => $brandId)
                        @include('booking._item', [
                            'index' => $index,
                            'oldBrandId' => old('brand_id.' . $index),
                            'oldCylinderTypeId' => old('cylinder_type_id.' . $index),
                            'oldQuantity' => old('quantity.' . $index),
                        ])
                    @endforeach
                @else
                    @foreach($booking->items as $index => $value)
                        @include('booking._item', [
                            'index' => $index,
                            'oldBrandId' => $value->brand_id,
                            'oldCylinderTypeId' => $value->cylinder_type_id,
                            'oldQuantity' => $value->quantity,
                        ])
                    @endforeach
                @endif
            </tbody>
            <tfoot class="table-info">
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Free full cylinders per cylinder type (this booking's own quantity is not counted)
    const availableStock = @json($available);

    $(document).ready(function() {
        calcItem();
    });

    $(document).on('change', '.item-type, #status', function() {
        calcItem();
    });

    $(document).on('input', '.item-quantity', function() {
        showAvailability();
    });

    $(".add-item").click(function() {
        $("#items tbody").append($("#item_blank tbody").html());
        calcItem();
    });

    $(document).on('click', '.item-remove', function() {
        $(this).closest('tr').remove();
        calcItem();
    });

    $(document).on('change', '.item-quantity', function() {
        calcItem();
    });

    function calcItem() {
        var itemQuantity = 0;
        $('#items tbody tr').each(function(index) {
            var $row = $(this);
            $row.find('td:first').text(index + 1);
            var quantity = parseInt($row.find('.item-quantity').val(), 0);
            if (isNaN(quantity)) {
                $row.find('.item-quantity').val('');
                quantity = 0;
            }
            itemQuantity += quantity;
        });
        $("#items").find('tfoot th:eq(1)').text(itemQuantity);
        showAvailability();
    }

    // Compare the total booked per type (all rows) with the free stock
    function showAvailability() {
        var bookedByType = {};
        $('#items tbody tr').each(function() {
            var typeId = $(this).find('.item-type').val();
            if (typeId) {
                bookedByType[typeId] = (bookedByType[typeId] || 0) + (parseInt($(this).find('.item-quantity').val(), 10) || 0);
            }
        });

        // Only pending bookings reserve stock
        var checkStock = $('#status').val() === 'pending';
        var isShort = false;

        $('#items tbody tr').each(function() {
            var $cell = $(this).find('.item-available');
            var typeId = $(this).find('.item-type').val();

            if (!typeId) {
                $cell.html('');
                return;
            }

            var available = availableStock[typeId] || 0;
            var short = bookedByType[typeId] - available;

            if (checkStock && short > 0) {
                isShort = true;
                $cell.html('<span class="text-warning fw-bold">&#9888; ' + Math.max(available, 0) + ' available &mdash; short by ' + short + '</span>');
            } else {
                $cell.html('<span class="text-success">&#10003; ' + available + ' available</span>');
            }
        });

        $('#short-stock-alert, #remarks-required').toggleClass('d-none', !isShort);
    }
</script>
@endpush