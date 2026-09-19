<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Shift;
use App\Models\StockOpname;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleService
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected BatchService $batchService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Get active shift for a user.
     */
    public function getActiveShift(int $userId): ?Shift
    {
        return Shift::where('user_id', $userId)
            ->where('status', 'open')
            ->latest('start_time')
            ->first();
    }

    /**
     * Create a completed or pending sale.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $payments
     */
    public function createSale(
        array $data,
        array $items,
        array $payments,
        int $cashierId,
        ?int $shiftId = null
    ): Sale {
        return DB::transaction(function () use ($data, $items, $payments, $cashierId, $shiftId) {
            $invoiceNumber = $data['invoice_number'] ?? $this->invoiceNumberService->generateSaleNumber();
            $status = $data['status'] ?? 'completed';
            $warehouseId = $data['warehouse_id'] ?? null;

            if (! $warehouseId && $shiftId) {
                $shift = Shift::find($shiftId);
                $warehouseId = $shift?->warehouse_id;
            }

            if (! $warehouseId) {
                $defaultWarehouse = Warehouse::where('is_active', true)->first();
                $warehouseId = $defaultWarehouse?->id ?? 1;
            }

            // Check if warehouse is frozen for stock opname
            if (StockOpname::isWarehouseFrozen($warehouseId)) {
                throw new InvalidArgumentException('Gudang saat ini sedang dibekukan untuk pelaksanaan Stok Opname aktif. Transaksi tidak dapat diproses.');
            }

            // Validate that selected batches are not expired
            foreach ($items as $item) {
                if (! empty($item['batch_id'])) {
                    $itemBatch = Batch::find($item['batch_id']);
                    if ($itemBatch && ($itemBatch->status === 'expired' || $itemBatch->isExpired())) {
                        throw new InvalidArgumentException("Batch {$itemBatch->batch_number} telah kedaluwarsa (expired) dan tidak dapat diperjualbelikan.");
                    }
                }
            }

            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $discount = (float) ($item['discount_per_item'] ?? 0);
                $subtotal += max(0, ($qty * $price) - $discount);
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount);

            // Calculate payment status
            $totalPaid = 0;
            foreach ($payments as $payment) {
                $totalPaid += (float) ($payment['amount'] ?? 0);
            }

            $paymentStatus = 'unpaid';
            if ($totalPaid >= $grandTotal) {
                $paymentStatus = 'paid';
            } elseif ($totalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'sale_date' => $data['sale_date'] ?? now(),
                'customer_id' => $data['customer_id'] ?? null,
                'cashier_id' => $cashierId,
                'pharmacist_id' => $data['pharmacist_id'] ?? null,
                'prescription_id' => $data['prescription_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'shift_id' => $shiftId,
            ]);

            // Process items and deduct inventory (only if completed)
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qtyToDeduct = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discountPerItem = (float) ($item['discount_per_item'] ?? 0);
                $itemSubtotal = max(0, ($qtyToDeduct * $unitPrice) - $discountPerItem);

                $batchId = ! empty($item['batch_id']) ? (int) $item['batch_id'] : null;

                if ($status === 'completed') {
                    // If no explicit batch was picked, use FEFO to find active batch with earliest expiry
                    if (! $batchId) {
                        $fefoBatches = $this->stockService->getFEFOBatch($productId, $warehouseId);
                        $batch = $fefoBatches->first();
                        $batchId = $batch?->id;
                    } else {
                        $batch = Batch::find($batchId);
                    }

                    if ($batch) {
                        // Deduct from batch
                        $this->batchService->updateBatchQty($batch, -$qtyToDeduct);
                    }

                    // Deduct from warehouse stock
                    $this->stockService->updateStock(
                        productId: $productId,
                        warehouseId: $warehouseId,
                        qtyChange: -$qtyToDeduct,
                        batchId: $batchId
                    );

                    // Record stock mutation
                    $this->mutationService->recordMutation(
                        productId: $productId,
                        warehouseId: $warehouseId,
                        mutationType: 'out',
                        quantity: $qtyToDeduct,
                        batchId: $batchId,
                        referenceType: 'sale',
                        referenceId: $sale->id,
                        notes: "Penjualan POS #{$sale->invoice_number}",
                        userId: $cashierId
                    );
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'unit_id' => $item['unit_id'],
                    'quantity' => $qtyToDeduct,
                    'unit_price' => $unitPrice,
                    'discount_per_item' => $discountPerItem,
                    'subtotal' => $itemSubtotal,
                    'is_prescription' => (bool) ($item['is_prescription'] ?? false),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Record payments (only if completed or payment provided)
            if (! empty($payments)) {
                $cashPaymentTotal = 0;
                $nonCashPaymentTotal = 0;

                foreach ($payments as $payment) {
                    $amount = (float) ($payment['amount'] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $method = $payment['payment_method'] ?? 'cash';
                    if ($method === 'cash') {
                        $cashPaymentTotal += $amount;
                    } else {
                        $nonCashPaymentTotal += $amount;
                    }

                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method' => $method,
                        'amount' => $amount,
                        'reference_number' => $payment['reference_number'] ?? null,
                        'payment_date' => $payment['payment_date'] ?? now(),
                    ]);
                }

                // Update shift cash totals if shift is assigned and completed
                if ($shiftId && $status === 'completed') {
                    $shift = Shift::find($shiftId);
                    if ($shift) {
                        $shift->total_sales_cash = (float) $shift->total_sales_cash + $cashPaymentTotal;
                        $shift->total_sales_non_cash = (float) $shift->total_sales_non_cash + $nonCashPaymentTotal;
                        $shift->closing_cash_system = (float) $shift->opening_cash + (float) $shift->total_sales_cash - (float) $shift->total_expenses;
                        $shift->save();
                    }
                }
            }

            return $sale->load(['items.product', 'items.unit', 'items.batch', 'payments', 'customer', 'cashier']);
        });
    }

    /**
     * Void a completed sale, restoring inventory and adjusting shift.
     */
    public function voidSale(Sale $sale, string $reason, int $userId): Sale
    {
        if ($sale->status === 'voided') {
            throw new InvalidArgumentException('Transaksi ini sudah dibatalkan (voided).');
        }

        return DB::transaction(function () use ($sale, $reason, $userId) {
            $shift = $sale->shift;
            $warehouseId = $shift?->warehouse_id;

            if (! $warehouseId) {
                $defaultWarehouse = Warehouse::where('is_active', true)->first();
                $warehouseId = $defaultWarehouse?->id ?? 1;
            }

            // Restore batch and stock for each item
            foreach ($sale->items as $item) {
                $qty = (float) $item->quantity;

                if ($item->batch) {
                    $this->batchService->updateBatchQty($item->batch, $qty);
                }

                $this->stockService->updateStock(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    qtyChange: $qty,
                    batchId: $item->batch_id
                );

                $this->mutationService->recordMutation(
                    productId: $item->product_id,
                    warehouseId: $warehouseId,
                    mutationType: 'in',
                    quantity: $qty,
                    batchId: $item->batch_id,
                    referenceType: 'sale_void',
                    referenceId: $sale->id,
                    notes: "Pembatalan (Void) Penjualan #{$sale->invoice_number}: {$reason}",
                    userId: $userId
                );
            }

            // Adjust shift cash totals if shift is present
            if ($shift && $sale->status === 'completed') {
                foreach ($sale->payments as $payment) {
                    $amount = (float) $payment->amount;
                    if ($payment->payment_method === 'cash') {
                        $shift->total_sales_cash = max(0, (float) $shift->total_sales_cash - $amount);
                    } else {
                        $shift->total_sales_non_cash = max(0, (float) $shift->total_sales_non_cash - $amount);
                    }
                }
                $shift->closing_cash_system = (float) $shift->opening_cash + (float) $shift->total_sales_cash - (float) $shift->total_expenses;
                $shift->save();
            }

            $sale->update([
                'status' => 'voided',
                'notes' => trim(($sale->notes ?? '')." [VOID: {$reason}]"),
            ]);

            return $sale;
        });
    }
}
