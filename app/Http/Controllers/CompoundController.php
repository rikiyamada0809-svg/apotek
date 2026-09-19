<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Compound;
use App\Models\CompoundItem;
use App\Models\CompoundTemplate;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Unit;
use App\Services\InvoiceNumberService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompoundController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService,
        protected StockService $stockService
    ) {}

    /**
     * Show the form for compounding medicine (from prescription or standalone).
     */
    public function create(Request $request): View
    {
        $prescriptionId = $request->input('prescription_id');
        $selectedPrescription = null;

        if ($prescriptionId) {
            $selectedPrescription = Prescription::with(['customer', 'doctor', 'items.product.productUnits'])->find($prescriptionId);
        }

        $templates = CompoundTemplate::with(['items.product.productUnits.unit', 'items.unit'])->get();
        $products = Product::active()->with(['productUnits.unit'])->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $compoundNumber = $this->invoiceNumberService->generateCompoundNumber();

        return view('compounds.create', compact(
            'selectedPrescription',
            'templates',
            'products',
            'units',
            'compoundNumber'
        ));
    }

    /**
     * Store a newly created compound in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prescription_id' => ['nullable', 'exists:prescriptions,id'],
            'template_id' => ['nullable', 'exists:compound_templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'total_packages' => ['required', 'integer', 'min:1'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_per_package' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $compoundNumber = $this->invoiceNumberService->generateCompoundNumber();
        $totalPackages = (int) $validated['total_packages'];
        $serviceFee = (float) $validated['service_fee'];

        $compound = DB::transaction(function () use ($validated, $compoundNumber, $totalPackages, $serviceFee) {
            $totalMaterialCost = 0;

            // Pre-calculate total material cost
            foreach ($validated['items'] as $itemData) {
                $totalQty = (float) $itemData['quantity_per_package'] * $totalPackages;
                $subtotal = $totalQty * (float) $itemData['unit_price'];
                $totalMaterialCost += $subtotal;
            }

            $totalPrice = $totalMaterialCost + $serviceFee;

            $record = Compound::create([
                'prescription_id' => $validated['prescription_id'] ?? null,
                'template_id' => $validated['template_id'] ?? null,
                'compound_number' => $compoundNumber,
                'name' => $validated['name'],
                'total_packages' => $totalPackages,
                'service_fee' => $serviceFee,
                'total_material_cost' => $totalMaterialCost,
                'total_price' => $totalPrice,
                'prepared_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                $productId = (int) $itemData['product_id'];
                $totalQty = (float) $itemData['quantity_per_package'] * $totalPackages;
                $unitPrice = (float) $itemData['unit_price'];
                $subtotal = $totalQty * $unitPrice;

                // Select earliest expiring active batch via FEFO
                $fefoBatch = Batch::where('product_id', $productId)
                    ->where('status', 'active')
                    ->where('current_qty', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                CompoundItem::create([
                    'compound_id' => $record->id,
                    'product_id' => $productId,
                    'batch_id' => $fefoBatch?->id,
                    'quantity_used' => $totalQty,
                    'unit_id' => $itemData['unit_id'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            return $record;
        });

        return redirect()->route('compounds.show', $compound)
            ->with('success', "Racikan obat {$compound->compound_number} berhasil dibuat.");
    }

    /**
     * Display the specified compound.
     */
    public function show(Compound $compound): View
    {
        $compound->load([
            'prescription.customer',
            'prescription.doctor',
            'template',
            'preparer',
            'verifier',
            'items.product.category',
            'items.batch',
            'items.unit',
        ]);

        return view('compounds.show', compact('compound'));
    }

    /**
     * Print etiket / label for the compound.
     */
    public function printLabel(Compound $compound): View
    {
        $compound->load([
            'prescription.customer',
            'prescription.doctor',
            'preparer',
            'items.product',
        ]);

        return view('compounds.label', compact('compound'));
    }
}
