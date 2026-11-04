<?php

namespace App\Http\Controllers;

use App\DataTables\BookingDataTable;
use App\Enums\BookingStatus;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\CylinderBrand;
use App\Models\CylinderType;
use App\Services\BookingService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Booking::class, 'booking');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(BookingDataTable $dataTable)
    {
        $statuses = BookingStatus::getStatuses();

        return $dataTable->render('booking.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $booking = new Booking;
        $booking->booking_date = Carbon::today();

        return view('booking.create', $this->formData($booking));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $booking = Booking::create([
                'status' => $data['status'],
                'is_short_stock' => $request->isShortStock(),
                'customer_id' => $data['customer_id'],
                'booking_date' => $data['booking_date'],
                'scheduled_date' => $data['scheduled_date'],
                'remarks' => $data['remarks'],
            ]);

            $booking->syncStockFromData($data);
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return redirect()->route('booking.index')->with('success', 'Booking added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        return view('booking.view', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        return view('booking.edit', $this->formData($booking));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $booking) {
            $booking->update([
                'status' => $data['status'],
                'is_short_stock' => $request->isShortStock(),
                'customer_id' => $data['customer_id'],
                'booking_date' => $data['booking_date'],
                'scheduled_date' => $data['scheduled_date'],
                'remarks' => $data['remarks'],
            ]);

            $booking->syncStockFromData($data);
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return redirect()->route('booking.index')->with('success', 'Booking updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        DB::transaction(function () use ($booking) {
            $booking->items()->delete();
            $booking->delete();
        });

        // Availability changed: re-flag short-stock bookings
        app(StockService::class)->refreshShortStock();

        return response()->json([
            'status' => true,
            'message' => 'Booking deleted successfully!',
        ]);
    }

    /**
     * Booking handed over: its cylinders leave the godown (see BookingService::markDelivered)
     */
    public function markDelivered(Booking $booking, BookingService $service)
    {
        $this->authorize('markDelivered', $booking);

        if (! $service->markDelivered($booking)) {
            return redirect()->route('booking.show', $booking)->with('error', "{$booking->booking_no} is no longer pending.");
        }

        return redirect()->route('booking.show', $booking)->with('success', "{$booking->booking_no} marked delivered.");
    }

    /**
     * Shared data for the create/edit form
     */
    protected function formData(Booking $booking): array
    {
        return [
            'booking' => $booking,
            // Active customers, plus the booking's current customer on edit even if inactive now
            'customers' => Customer::where(fn ($query) => $query->active()->orWhere('id', $booking->customer_id))->orderBy('name')->get(),
            // delivered is set by "Mark delivered" only
            'statues' => [BookingStatus::PENDING, BookingStatus::CANCELLED],
            'cylinderBrands' => CylinderBrand::active()->pluck('name', 'id'),
            'cylinderTypes' => CylinderType::active()->get(),
            // Free full cylinders per type; the booking's own quantity isn't counted against itself
            'available' => app(StockService::class)->available($booking->id),
        ];
    }
}
