<?php

namespace App\Http\Controllers;

use App\DataTables\CylinderBrandDataTable;
use App\Http\Requests\CylinderBrand\StoreCylinderBrandRequest;
use App\Http\Requests\CylinderBrand\UpdateCylinderBrandRequest;
use App\Models\CylinderBrand;
use Illuminate\Support\Facades\Storage;

class CylinderBrandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(CylinderBrand::class, 'cylinder_brand');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CylinderBrandDataTable $dataTable)
    {
        return $dataTable->render('cylinder-brands.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cylinderBrand = new CylinderBrand;
        $cylinderBrand->is_active = true;

        return view('cylinder-brands.create', compact('cylinderBrand'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCylinderBrandRequest $request)
    {
        CylinderBrand::create($request->validated());

        $data = $request->validated();

        // Check if the request has an image and is valid
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('cylinder_brand', 'public');
            $data['image'] = $imagePath;
        }

        CylinderBrand::create($data);

        return redirect()->route('cylinder-brands.index')->with('success', 'Cylinder brand added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CylinderBrand $cylinderBrand)
    {
        return view('cylinder-brands.edit', compact('cylinderBrand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCylinderBrandRequest $request, CylinderBrand $cylinderBrand)
    {
        $data = $request->validated();

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($cylinderBrand->image) {
                Storage::disk('public')->delete($cylinderBrand->image);
            }

            $imagePath = $request->file('image')->store('cylinder_brand', 'public');
            $data['image'] = $imagePath;
        } elseif ($request->has('delete_image')) {
            if ($cylinderBrand->image) {
                Storage::disk('public')->delete($cylinderBrand->image);
            }
            $data['image'] = null;
        }

        $cylinderBrand->update($data);

        return redirect()->route('cylinder-brands.index')->with('success', 'Cylinder brand updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CylinderBrand $cylinderBrand)
    {
        if ($cylinderBrand->image) {
            Storage::disk('public')->delete($cylinderBrand->image);
        }

        $cylinderBrand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cylinder brand deleted successfully!',
        ]);
    }
}
