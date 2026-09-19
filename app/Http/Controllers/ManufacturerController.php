<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManufacturerController extends Controller
{
    public function index(Request $request): View
    {
        $manufacturers = Manufacturer::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('country', 'like', "%{$search}%");
        })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('manufacturers.index', compact('manufacturers'));
    }

    public function create(): View
    {
        return view('manufacturers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:manufacturers,name'],
            'country' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Manufacturer::create($validated);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Pabrik berhasil ditambahkan.');
    }

    public function edit(Manufacturer $manufacturer): View
    {
        return view('manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, Manufacturer $manufacturer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:manufacturers,name,'.$manufacturer->id],
            'country' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $manufacturer->update($validated);

        return redirect()->route('manufacturers.index')
            ->with('success', 'Pabrik berhasil diperbarui.');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        if ($manufacturer->products()->exists()) {
            return back()->with('error', 'Pabrik tidak bisa dihapus karena memiliki produk.');
        }

        $manufacturer->delete();

        return redirect()->route('manufacturers.index')
            ->with('success', 'Pabrik berhasil dihapus.');
    }
}
