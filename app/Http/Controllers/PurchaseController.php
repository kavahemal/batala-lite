<?php

namespace App\Http\Controllers;

use App\DataTables\PurchaseDataTable;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Models\CylinderBrand;
use App\Models\CylinderType;
use App\Models\Purchase;
use App\Services\DriverTruckService;
use App\Services\StockService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Purchase::class, 'purchase');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PurchaseDataTable $dataTable)
    {
        return $dataTable->render('purchase.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [];
        $data['purchase'] = new Purchase;
        $data['purchase']->purchase_date = Date::today();
        $data['cylinderBrands'] = CylinderBrand::active()->pluck('name', 'id');
        $data['cylinderTypes'] = CylinderType::active()->get();

        return view('purchase.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request, DriverTruckService $trucks)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $trucks) {
            $purchase = Purchase::create([
                ...$trucks->resolve(Arr::only($data, ['truck_no', 'driver_name', 'driver_mobile'])),
                'purchase_date' => $data['purchase_date'],
                'remarks' => $data['remarks'],
            ]);

            $purchase->syncStockFromData($data);
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return redirect()->route('purchase.index')->with('success', 'Purchase added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        return view('purchase.view', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $data = [];
        $data['purchase'] = $purchase;
        $data['cylinderBrands'] = CylinderBrand::active()->pluck('name', 'id');
        $data['cylinderTypes'] = CylinderType::active()->get();

        return view('purchase.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase, DriverTruckService $trucks)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $purchase, $trucks) {
            $purchase->update([
                ...$trucks->resolve(Arr::only($data, ['truck_no', 'driver_name', 'driver_mobile'])),
                'purchase_date' => $data['purchase_date'],
                'remarks' => $data['remarks'],
            ]);

            $purchase->syncStockFromData($data);
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return redirect()->route('purchase.index')->with('success', 'Purchase updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase->movements()->delete();
            $purchase->delete();
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return response()->json([
            'status' => true,
            'message' => 'Purchase deleted successfully!',
        ]);
    }
}
