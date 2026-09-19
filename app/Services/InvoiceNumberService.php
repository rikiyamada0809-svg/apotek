<?php

namespace App\Services;

use App\Models\BatchRecall;
use App\Models\Compound;
use App\Models\DrugDestruction;
use App\Models\GoodsReceipt;
use App\Models\Prescription;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockOpname;
use App\Models\StockTransfer;

class InvoiceNumberService
{
    public function generatePoNumber(): string
    {
        $datePrefix = 'PO-'.now()->format('Ymd');
        $lastOrder = PurchaseOrder::where('po_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastOrder) {
            $parts = explode('-', $lastOrder->po_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateReceiptNumber(): string
    {
        $datePrefix = 'GR-'.now()->format('Ymd');
        $lastReceipt = GoodsReceipt::where('receipt_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastReceipt) {
            $parts = explode('-', $lastReceipt->receipt_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateInvoiceNumber(): string
    {
        $datePrefix = 'INV-'.now()->format('Ymd');
        $lastInvoice = PurchaseInvoice::where('invoice_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateSaleNumber(): string
    {
        $datePrefix = 'POS-'.now()->format('Ymd');
        $lastSale = Sale::where('invoice_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastSale) {
            $parts = explode('-', $lastSale->invoice_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generatePurchaseReturnNumber(): string
    {
        $datePrefix = 'PR-'.now()->format('Ymd');
        $lastReturn = PurchaseReturn::where('return_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastReturn) {
            $parts = explode('-', $lastReturn->return_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateSaleReturnNumber(): string
    {
        $datePrefix = 'SR-'.now()->format('Ymd');
        $lastReturn = SaleReturn::where('return_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastReturn) {
            $parts = explode('-', $lastReturn->return_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateStockOpnameNumber(): string
    {
        $datePrefix = 'SO-'.now()->format('Ymd');
        $lastOpname = StockOpname::where('opname_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastOpname) {
            $parts = explode('-', $lastOpname->opname_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateDestructionNumber(): string
    {
        $datePrefix = 'DST-'.now()->format('Ymd');
        $lastRecord = DrugDestruction::where('destruction_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->destruction_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generatePrescriptionNumber(): string
    {
        $datePrefix = 'RX-'.now()->format('Ymd');
        $lastRecord = Prescription::where('prescription_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->prescription_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateCompoundNumber(): string
    {
        $datePrefix = 'CMP-'.now()->format('Ymd');
        $lastRecord = Compound::where('compound_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->compound_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateTransferNumber(): string
    {
        $datePrefix = 'TRF-'.now()->format('Ymd');
        $lastRecord = StockTransfer::where('transfer_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->transfer_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }

    public function generateRecallNumber(): string
    {
        $datePrefix = 'RCL-'.now()->format('Ymd');
        $lastRecord = BatchRecall::where('recall_number', 'like', "{$datePrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastRecord) {
            $parts = explode('-', $lastRecord->recall_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return sprintf('%s-%04d', $datePrefix, $nextNumber);
    }
}
