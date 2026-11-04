<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name ?? '') }}">
        @error('name')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-6 mb-3">
        <label for="mobile_number" class="form-label">Mobile Number</label>
        <input type="text" name="mobile_number" id="mobile_number" class="form-control mobile-input @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number', $customer->mobile_number ?? '') }}" inputmode="numeric" pattern="[0-9]{10}" title="10-digit mobile number" autocomplete="off">
        @error('mobile_number')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
        {{-- A mobile belongs to one customer only; checked live, the save is blocked server-side too --}}
        <div class="form-text text-danger fw-bold d-none" id="mobile-duplicate"></div>
    </div>

    <div class="col-sm-12 mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="4">{{ old('address', $customer->address ?? '') }}</textarea>
        @error('address')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="col-sm-4">
        <label for="is_active" class="form-label">Is Active</label>
        <div class="form-check form-switch">
            <input class="form-check-input float-none" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(session()->hasOldInput() ? old('is_active') : $customer->is_active) style="cursor: pointer;">
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Live error when another customer already has this mobile number (save is blocked server-side too)
    $(function() {
        var checkUrl = @json(route('customers.check-mobile'));
        var exceptId = @json($customer->id ?? null);

        function showTaken(text) {
            var $input = $('#mobile_number');
            $('#mobile-duplicate').toggleClass('d-none', !text).text(text || '');
            // Only clear the red border we set, not a server-side error
            if (text) {
                $input.addClass('is-invalid').data('taken', true);
            } else if ($input.data('taken')) {
                $input.removeClass('is-invalid').data('taken', false);
            }
        }

        function checkMobile() {
            var mobile = $('#mobile_number').val().trim();
            if (!/^\d{10}$/.test(mobile)) {
                showTaken('');
                return;
            }

            $.getJSON(checkUrl, { mobile: mobile, except: exceptId }, function(others) {
                showTaken(others.length ? 'This mobile is already used by ' + others[0].name + ' (' + others[0].customer_no + ').' : '');
            });
        }

        $(document).on('input', '#mobile_number', function() {
            $(this).siblings('.invalid-feedback').remove(); // old server error no longer applies
            checkMobile();
        });
        // After a failed save the server error is already shown
        if (!$('#mobile_number').hasClass('is-invalid')) checkMobile();
    });
</script>
@endpush
