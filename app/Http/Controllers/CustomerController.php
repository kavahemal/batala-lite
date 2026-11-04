<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = new Customer;
        $customer->is_active = true;

        return view('customers.create', compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    /**
     * Other customers with this mobile number (?mobile=, ?except= customer being edited), for the form's live error
     */
    public function checkMobile(Request $request)
    {
        abort_unless($request->user()->can('customers.create') || $request->user()->can('customers.edit'), 403);

        $mobile = (string) $request->get('mobile');
        if (! preg_match('/^\d{10}$/', $mobile)) {
            return response()->json([]);
        }

        return response()->json(Customer::sameMobile($mobile, $request->integer('except') ?: null)->get()
            ->map(fn (Customer $customer) => ['customer_no' => $customer->customer_no, 'name' => $customer->name]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // $this->authorize('delete', $customer);
        $customer->delete();

        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully!',
        ]);
    }
}
