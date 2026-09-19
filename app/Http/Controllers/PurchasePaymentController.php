<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchasePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends Controller
{
    /**
     * Store a payment for a purchase invoice.
     */
    public function store(Request $request, PurchaseInvoice $purchaseInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:transfer,cash,giro'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $remaining = $purchaseInvoice->remaining_amount;
        if ((float) $validated['amount'] > $remaining + 0.01) {
            return back()->withInput()->with('error', 'Nominal pembayaran melebihi sisa tagihan faktur ('.format_rupiah($remaining).').');
        }

        DB::transaction(function () use ($purchaseInvoice, $validated) {
            PurchasePayment::create([
                'invoice_id' => $purchaseInvoice->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $newPaidAmount = (float) $purchaseInvoice->paid_amount + (float) $validated['amount'];
            $newStatus = $newPaidAmount >= (float) $purchaseInvoice->total_amount ? 'paid' : 'partial';

            $purchaseInvoice->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newStatus,
            ]);
        });

        return redirect()->route('purchase-invoices.show', $purchaseInvoice)
            ->with('success', 'Pembayaran sebesar '.format_rupiah($validated['amount']).' berhasil dicatat.');
    }
}
