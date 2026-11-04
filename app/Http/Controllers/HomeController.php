<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\StockMovementType;
use App\Models\Booking;
use App\Models\CylinderType;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Sizes with this many (or fewer) cylinders available to book show under "Low stock"
     */
    protected const LOW_STOCK = 5;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * Each block is only built when the user may see that area (same permissions as the menus).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(StockService $stock)
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();
        $data = ['typeLabels' => $this->typeLabels()];

        if ($user->can('booking.index')) {
            $todays = Booking::whereDate('booking_date', $today)->with('items')->get();
            $open = Booking::where('status', BookingStatus::PENDING);

            $data['bookings'] = (object) [
                'today' => $todays->count(),
                'todayCylinders' => $todays->flatMap->items->sum('quantity'),
                'open' => (clone $open)->count(),
                // Due = scheduled date, or the booking date when none is scheduled
                'overdue' => (clone $open)->whereRaw('COALESCE(scheduled_date, booking_date) < ?', [$today->toDateString()])->count(),
                'short' => (clone $open)->where('is_short_stock', true)->count(),
            ];
        }

        if ($user->can('stock-report.index')) {
            // Every active size, including ones with no stock movement at all (0 available)
            $available = $stock->available();
            $data['lowStock'] = CylinderType::active()->pluck('id')
                ->mapWithKeys(fn ($typeId) => [$typeId => $available[$typeId] ?? 0])
                ->filter(fn ($qty) => $qty <= self::LOW_STOCK)
                ->sort();
            $data['lowStockLimit'] = self::LOW_STOCK;
        }

        if ($user->can('booking.index') || $user->can('purchase.index')) {
            $data['lastDays'] = $this->lastDays($today);
        }

        return view('home', $data);
    }

    /**
     * Activity per day for the last 7 days (today first): bookings, cylinders purchased / delivered
     */
    protected function lastDays(Carbon $today): array
    {
        $from = $today->copy()->subDays(6)->toDateString();

        // DATE(): the day key is Y-m-d on MySQL and SQLite alike (SQLite keeps the time part Eloquent writes)
        $bookings = Booking::where('booking_date', '>=', $from)
            ->groupByRaw('DATE(booking_date)')->selectRaw('DATE(booking_date) as day, COUNT(*) as total')->pluck('total', 'day');

        $purchased = StockMovement::join('purchases', fn ($join) => $join->on('purchases.id', '=', 'stock_movements.source_id')
            ->where('stock_movements.source_type', Purchase::class))
            ->where('stock_movements.movement_type', StockMovementType::PURCHASE_IN)
            ->whereNull('purchases.deleted_at')
            ->where('purchases.purchase_date', '>=', $from)
            ->groupByRaw('DATE(purchases.purchase_date)')->selectRaw('DATE(purchases.purchase_date) as day, SUM(stock_movements.quantity) as total')->pluck('total', 'day');

        // Delivered on the day the booking was marked delivered
        $delivered = StockMovement::where('movement_type', StockMovementType::DELIVERY_OUT)
            ->where('created_at', '>=', $from)
            ->groupByRaw('DATE(created_at)')->selectRaw('DATE(created_at) as day, ABS(SUM(quantity)) as total')->pluck('total', 'day');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $today->copy()->subDays($i);
            $key = $day->toDateString();
            $days[] = (object) [
                'date' => $day,
                'bookings' => (int) ($bookings[$key] ?? 0),
                'purchased' => (int) ($purchased[$key] ?? 0),
                'delivered' => (int) ($delivered[$key] ?? 0),
            ];
        }

        return $days;
    }

    /**
     * "HP Gas 14.2 kg" per cylinder type id
     */
    protected function typeLabels()
    {
        return CylinderType::with('cylinderBrand')->get()
            ->mapWithKeys(fn (CylinderType $type) => [$type->id => trim(($type->cylinderBrand?->name ?? '').' '.$type->weight.' kg')]);
    }
}
