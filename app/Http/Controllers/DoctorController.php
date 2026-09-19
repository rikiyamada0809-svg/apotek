<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $doctors = Doctor::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sip_number', 'like', "%{$search}%")
                ->orWhere('specialization', 'like', "%{$search}%");
        })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'clinic_address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Doctor::create($validated);

        return redirect()->route('doctors.index')
            ->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function edit(Doctor $doctor): View
    {
        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sip_number' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'clinic_address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $doctor->update($validated);

        return redirect()->route('doctors.index')
            ->with('success', 'Dokter berhasil diperbarui.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
            ->with('success', 'Dokter berhasil dihapus.');
    }
}
