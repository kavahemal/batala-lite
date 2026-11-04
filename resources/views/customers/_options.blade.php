{{-- Options for <select class="customer-select">: name is the text, data-mobile + data-no are searchable too (custom.js) --}}
<option value=""></option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}" data-mobile="{{ $customer->mobile_number }}" data-no="{{ $customer->customer_no }}" {{ $selected == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
@endforeach
