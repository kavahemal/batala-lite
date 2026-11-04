<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CylinderType;
use App\Services\StockService;

class StockReportController extends Controller
{
    /**
     * Godown stock per brand + size: full, reserved by pending bookings, available to book
     */
    public function index(StockService $stock)
    {
        $full = $stock->godown();
        $reserved = $stock->reserved();

        // Active types, plus inactive ones that still have stock or bookings
        $typeIds = $full->keys()->merge($reserved->keys());

        $rows = CylinderType::with('cylinderBrand')
            ->where(fn ($query) => $query->active()->orWhereIn('id', $typeIds))
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp($a->cylinderBrand?->name ?? '', $b->cylinderBrand?->name ?? ''),
                fn ($a, $b) => (float) $a->weight <=> (float) $b->weight,
            ])
            ->map(fn (CylinderType $type) => (object) [
                'brand' => $type->cylinderBrand?->name,
                'weight' => $type->weight,
                'full' => $full[$type->id] ?? 0,
                'reserved' => $reserved[$type->id] ?? 0,
                'available' => ($full[$type->id] ?? 0) - ($reserved[$type->id] ?? 0),
            ])
            ->values();

        return view('reports.stock', compact('rows'));
    }
}
