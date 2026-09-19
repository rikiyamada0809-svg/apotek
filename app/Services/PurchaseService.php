<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected BatchService $batchService,
        protected StockService $stockService,
        protected MutationService $mutationService
    ) {}

    /**
     * Create a Purchase Order with line items.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createPO(array $data, array $items, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            $poNumber = $data['po_number'] ?? $this->invoiceNumberService->generatePoNumber();

            $subtotal = 0;
            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? 0); // e.g. 11 for 11% PPN

            foreach ($items as $item) {
                $itemSubtotal = ((float) $item['quantity_ordered'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0);
                $subtotal += max(0, $itemSubtotal);
            }

            $taxAmount = (float) ($data['tax_amount'] ?? (($subtotal - $discountAmount) * ($taxRate / 100)));
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => $data['status'] ?? 'pending_approval',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                $itemSubtotal = ((float) $item['quantity_ordered'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0);
                PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => max(0, $itemSubtotal),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $po->load(['items.product', 'items.unit', 'supplier', 'warehouse']);
        });
    }

    /**
     * Approve a Purchase Order.
     */
    public function approvePO(PurchaseOrder $po, int $userId): PurchaseOrder
    {
        $po->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $po;
    }

    /**
     * Cancel a Purchase Order.
     */
    public function cancelPO(PurchaseOrder $po): PurchaseOrder
    {
        $po->update(['status' => 'cancelled']);

        return $po;
    }

    /**
     * Process physical goods receipt against a PO.
     *
     * @param  array<string, mixed>  $receiptData
     * @param  array<int, array<string, mixed>>  $receivedItems
     */
    public function receivePO(
        PurchaseOrder $po,
        array $receiptData,
        array $receivedItems,
        int $userId
    ): GoodsReceipt {
        return DB::transaction(function () use ($po, $receiptData, $receivedItems, $userId) {
            $receiptNumber = $receiptData['receipt_number'] ?? $this->invoiceNumberService->generateReceiptNumber();

            $goodsReceipt = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'po_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $po->warehouse_id,
                'receipt_date' => $receiptData['receipt_date'] ?? now()->toDateString(),
                'delivery_note_number' => $receiptData['delivery_note_number'] ?? null,
                'status' => $receiptData['status'] ?? 'verified',
                'received_by' => $userId,
                'verified_by' => $userId,
                'notes' => $receiptData['notes'] ?? null,
            ]);

            foreach ($receivedItems as $item) {
                $qtyReceived = (float) $item['quantity_received'];
                if ($qtyReceived <= 0) {
                    continue;
                }

                $poItem = isset($item['po_item_id'])
                    ? PurchaseOrderItem::find($item['po_item_id'])
                    : null;

                $unitPrice = $poItem ? (float) $poItem->unit_price : 0;

                // 1. Create batch record via BatchService
                $batch = $this->batchService->createBatch([
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'],
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'expiry_date' => $item['expiry_date'],
                    'supplier_id' => $po->supplier_id,
                    'purchase_price' => $unitPrice,
                    'received_qty' => $qtyReceived,
                    'received_date' => $goodsReceipt->receipt_date,
                    'receipt_id' => $goodsReceipt->id,
                    'notes' => $item['notes'] ?? null,
                ]);

                // 2. Create GoodsReceiptItem record
                GoodsReceiptItem::create([
                    'receipt_id' => $goodsReceipt->id,
                    'po_item_id' => $poItem?->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $batch->id,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity_received' => $qtyReceived,
                    'storage_location_id' => $item['storage_location_id'] ?? null,
                    'condition' => $item['condition'] ?? 'good',
                    'notes' => $item['notes'] ?? null,
                ]);

                // 3. Update received_qty in PurchaseOrderItem
                if ($poItem) {
                    $poItem->quantity_received = (float) $poItem->quantity_received + $qtyReceived;
                    $poItem->save();
                }

                // 4. Update warehouse inventory stock
                $this->stockService->updateStock(
                    productId: (int) $item['product_id'],
                    warehouseId: (int) $po->warehouse_id,
                    qtyChange: $qtyReceived,
                    batchId: $batch->id,
                    locationId: ! empty($item['storage_location_id']) ? (int) $item['storage_location_id'] : null
                );

                // 5. Record stock mutation
                $this->mutationService->recordMutation(
                    productId: (int) $item['product_id'],
                    warehouseId: (int) $po->warehouse_id,
                    mutationType: 'in',
                    quantity: $qtyReceived,
                    batchId: $batch->id,
                    referenceType: 'goods_receipt',
                    referenceId: $goodsReceipt->id,
                    notes: "Penerimaan PO #{$po->po_number} (SJ: ".($goodsReceipt->delivery_note_number ?? '-').')',
                    userId: $userId
                );
            }

            // Check if PO is completely fulfilled
            $po->refresh();
            $allFulfilled = true;
            $anyReceived = false;

            foreach ($po->items as $item) {
                if ((float) $item->quantity_received >= (float) $item->quantity_ordered) {
                    $anyReceived = true;
                } else {
                    $allFulfilled = false;
                    if ((float) $item->quantity_received > 0) {
                        $anyReceived = true;
                    }
                }
            }

            if ($allFulfilled) {
                $po->update(['status' => 'received']);
            } elseif ($anyReceived) {
                $po->update(['status' => 'partially_received']);
            }

            return $goodsReceipt->load(['items.product', 'items.batch', 'items.storageLocation', 'supplier']);
        });
    }
}
