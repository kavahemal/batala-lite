<?php

namespace App\Http\Controllers;

use App\DataTables\CylinderTypeDataTable;
use App\Http\Requests\CylinderType\StoreCylinderTypeRequest;
use App\Http\Requests\CylinderType\UpdateCylinderTypeRequest;
use App\Models\CylinderBrand;
use App\Models\CylinderType;

class CylinderTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CylinderType::class, 'cylinder_type');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CylinderTypeDataTable $dataTable)
    {
        return $dataTable->render('cylinder-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cylinderType = new CylinderType;
        $cylinderType->is_active = true;
        $brands = CylinderBrand::orderBy('name')->get();

        return view('cylinder-types.create', compact('cylinderType', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCylinderTypeRequest $request)
    {
        CylinderType::create($request->validated());

        return redirect()->route('cylinder-types.index')->with('success', 'Cylinder type added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CylinderType $cylinderType)
    {
        $brands = CylinderBrand::orderBy('name')->get();

        return view('cylinder-types.edit', compact('cylinderType', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCylinderTypeRequest $request, CylinderType $cylinderType)
    {
        $cylinderType->update($request->validated());

        return redirect()->route('cylinder-types.index')->with('success', 'Cylinder type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CylinderType $cylinderType)
    {
        $cylinderType->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cylinder type deleted successfully!',
        ]);
    }
}
