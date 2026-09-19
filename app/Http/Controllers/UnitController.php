<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('abbreviation', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
            'abbreviation' => ['required', 'string', 'max:10', 'unique:units,abbreviation'],
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name,'.$unit->id],
            'abbreviation' => ['required', 'string', 'max:10', 'unique:units,abbreviation,'.$unit->id],
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->productUnits()->exists()) {
            return back()->with('error', 'Satuan tidak bisa dihapus karena digunakan oleh produk.');
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
