<div class="row">
    @include('partials.truck-driver-fields', ['model' => $purchase, 'mobileRequired' => false])

    <div class="col-sm-3 mb-3">
        <label for="purchase_date" class="form-label">Purchase date <span class="text-danger">*</span></label>
        <input type="text" name="purchase_date" id="purchase_date" class="form-control flat-datepicker @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', $purchase->purchase_date) }}">
        @error('purchase_date')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-12 mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4">{{ old('remarks', $purchase->remarks ?? '') }}</textarea>
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

        <table class="table table-bordered" id="items">
            <thead class="table-info">
                <tr>
                    <th width="80"></th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th width="40" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- Submitted rows win over saved rows, so a failed save keeps what was typed --}}
                @if(old('brand_id') !== null)
                @foreach(old('brand_id', []) as $index => $brandId)
                @include('purchase._item', [
                'index' => $index,
                'oldBrandId' => old('brand_id.' . $index),
                'oldCylinderTypeId' => old('cylinder_type_id.' . $index),
                'oldQuantity' => old('quantity.' . $index),
                ])
                @endforeach
                @else
                @foreach($purchase->items as $index => $value)
                @include('purchase._item', [
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
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        calcItem();
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

    // Row numbers + total quantity
    function calcItem() {
        var itemQuantity = 0;
        $('#items tbody tr').each(function(index) {
            var $row = $(this);
            $row.find('td:first').text(index + 1);
            var quantity = parseInt($row.find('.item-quantity').val(), 10);
            if (isNaN(quantity)) {
                $row.find('.item-quantity').val('');
                quantity = 0;
            }
            itemQuantity += quantity;
        });
        $('#items tfoot th:eq(1)').text(itemQuantity);
    }
</script>
@endpush
