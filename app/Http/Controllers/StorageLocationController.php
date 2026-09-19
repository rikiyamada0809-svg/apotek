<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorageLocationController extends Controller
{
    public function create(Warehouse $warehouse): View
    {
        return view('storage-locations.create', compact('warehouse'));
    }

    public function store(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:rak,kulkas,lemari_narkotika'],
            'temperature_range' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $warehouse->storageLocations()->create($validated);

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Lokasi penyimpanan berhasil ditambahkan.');
    }

    public function edit(Warehouse $warehouse, StorageLocation $location): View
    {
        return view('storage-locations.edit', compact('warehouse', 'location'));
    }

    public function update(Request $request, Warehouse $warehouse, StorageLocation $location): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:rak,kulkas,lemari_narkotika'],
            'temperature_range' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $location->update($validated);

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Lokasi penyimpanan berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse, StorageLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('warehouses.show', $warehouse)
            ->with('success', 'Lokasi penyimpanan berhasil dihapus.');
    }
}
