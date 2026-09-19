<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Shift;
use App\Models\Warehouse;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    /**
     * Show form to open a shift.
     */
    public function open(): View|RedirectResponse
    {
        $activeShift = $this->saleService->getActiveShift(auth()->id());
        if ($activeShift) {
            return redirect()->route('pos.index')
                ->with('info', 'Anda sudah memiliki sesi shift kasir yang aktif.');
        }

        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('pos.shifts.open', compact('warehouses'));
    }

    /**
     * Start a new cashier shift.
     */
    public function store(Request $request): RedirectResponse
    {
        $activeShift = $this->saleService->getActiveShift(auth()->id());
        if ($activeShift) {
            return redirect()->route('pos.index')
                ->with('info', 'Sesi shift aktif sudah berjalan.');
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $shift = Shift::create([
            'user_id' => auth()->id(),
            'warehouse_id' => $validated['warehouse_id'],
            'start_time' => now(),
            'opening_cash' => $validated['opening_cash'],
            'closing_cash_system' => $validated['opening_cash'],
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('pos.index')
            ->with('success', 'Shift kasir berhasil dibuka dengan modal awal '.format_rupiah($shift->opening_cash));
    }

    /**
     * Show summary / recap of a shift.
     */
    public function show(Shift $shift): View
    {
        $shift->load(['user', 'warehouse', 'sales.payments', 'cashTransactions.creator']);

        return view('pos.shifts.show', compact('shift'));
    }

    /**
     * Close an active shift.
     */
    public function close(Request $request, Shift $shift): RedirectResponse
    {
        if ($shift->status !== 'open') {
            return redirect()->route('pos.index')
                ->with('error', 'Shift ini sudah ditutup.');
        }

        $validated = $request->validate([
            'closing_cash_actual' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $cashActual = (float) $validated['closing_cash_actual'];
        $cashSystem = (float) $shift->closing_cash_system;
        $difference = $cashActual - $cashSystem;

        $shift->update([
            'end_time' => now(),
            'closing_cash_actual' => $cashActual,
            'cash_difference' => $difference,
            'status' => 'closed',
            'notes' => trim(($shift->notes ?? '')."\n".($validated['notes'] ?? '')),
        ]);

        return redirect()->route('shifts.show', $shift)
            ->with('success', 'Shift kasir berhasil ditutup. Selisih kas: '.format_rupiah($difference));
    }

    /**
     * Record a petty cash expense during the shift.
     */
    public function expense(Request $request, Shift $shift): RedirectResponse
    {
        if ($shift->status !== 'open') {
            return back()->with('error', 'Shift sudah ditutup.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        CashTransaction::create([
            'shift_id' => $shift->id,
            'type' => 'expense',
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'reference' => $validated['reference'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $shift->total_expenses = (float) $shift->total_expenses + (float) $validated['amount'];
        $shift->closing_cash_system = (float) $shift->opening_cash + (float) $shift->total_sales_cash - (float) $shift->total_expenses;
        $shift->save();

        return back()->with('success', 'Pengeluaran kas kecil sebesar '.format_rupiah($validated['amount']).' berhasil dicatat.');
    }
}
