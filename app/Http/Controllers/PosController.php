<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PosController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    /**
     * Main Point of Sale cashier screen.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $activeShift = $this->saleService->getActiveShift(auth()->id());
        if (! $activeShift) {
            return redirect()->route('shifts.open')
                ->with('info', 'Silakan buka shift kasir terlebih dahulu sebelum memulai transaksi penjualan.');
        }

        $customers = Customer::active()->orderBy('name')->get();
        $heldSales = Sale::where('status', 'pending')
            ->where('shift_id', $activeShift->id)
            ->with(['items.product', 'customer'])
            ->latest()
            ->get();

        $warehouseId = $activeShift->warehouse_id;

        // Load active products with units and stock in this warehouse
        $products = Product::active()
            ->with([
                'productUnits.unit',
                'category',
                'stocks' => fn ($q) => $q->where('warehouse_id', $warehouseId),
            ])
            ->get()
            ->map(function ($product) {
                $totalStock = (float) $product->stocks->sum('available_qty');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'barcode' => $product->barcode,
                    'category_name' => $product->category?->name ?? 'Umum',
                    'requires_prescription' => (bool) $product->requires_prescription,
                    'available_stock' => $totalStock,
                    'units' => $product->productUnits->map(fn ($pu) => [
                        'unit_id' => $pu->unit_id,
                        'name' => $pu->unit?->name ?? 'Satuan',
                        'is_base_unit' => (bool) $pu->is_base_unit,
                        'selling_price' => (float) $pu->selling_price,
                        'wholesale_price' => (float) $pu->wholesale_price,
                        'prescription_price' => (float) $pu->prescription_price,
                        'conversion_factor' => (float) $pu->conversion_factor,
                    ]),
                ];
            });

        return view('pos.index', compact('activeShift', 'customers', 'heldSales', 'products'));
    }

    /**
     * Fast search endpoint for products via query or barcode scanner.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $activeShift = $this->saleService->getActiveShift(auth()->id());
        $warehouseId = $activeShift?->warehouse_id ?? 1;

        $products = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('product_code', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->with([
                'productUnits.unit',
                'category',
                'stocks' => fn ($q) => $q->where('warehouse_id', $warehouseId),
            ])
            ->take(15)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'barcode' => $product->barcode,
                    'category' => $product->category?->name ?? 'Umum',
                    'requires_prescription' => (bool) $product->requires_prescription,
                    'available_stock' => (float) $product->stocks->sum('available_qty'),
                    'units' => $product->productUnits->map(fn ($pu) => [
                        'unit_id' => $pu->unit_id,
                        'name' => $pu->unit?->name ?? 'Satuan',
                        'is_base_unit' => (bool) $pu->is_base_unit,
                        'selling_price' => (float) $pu->selling_price,
                    ]),
                ];
            });

        return response()->json($products);
    }

    /**
     * Store completed sale or hold/pending transaction.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $activeShift = $this->saleService->getActiveShift(auth()->id());
        if (! $activeShift) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Shift kasir belum aktif.'], 403);
            }

            return redirect()->route('shifts.open')->with('error', 'Shift kasir belum aktif.');
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['required', 'in:completed,pending'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_per_item' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_prescription' => ['nullable', 'boolean'],
            'items.*.batch_id' => ['nullable', 'exists:batches,id'],
            'items.*.notes' => ['nullable', 'string'],
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method' => ['required', 'string', 'in:cash,debit,qris,transfer,credit'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:100'],
            'held_sale_id' => ['nullable', 'exists:sales,id'],
        ]);

        // If resuming a held sale, delete previous pending sale
        if (! empty($validated['held_sale_id'])) {
            $prevSale = Sale::where('id', $validated['held_sale_id'])->where('status', 'pending')->first();
            if ($prevSale) {
                $prevSale->items()->delete();
                $prevSale->payments()->delete();
                $prevSale->delete();
            }
        }

        try {
            $sale = $this->saleService->createSale(
                data: [
                    'customer_id' => $validated['customer_id'] ?? null,
                    'status' => $validated['status'],
                    'discount_amount' => $validated['discount_amount'] ?? 0,
                    'tax_amount' => $validated['tax_amount'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                    'warehouse_id' => $activeShift->warehouse_id,
                ],
                items: $validated['items'],
                payments: $validated['payments'] ?? [],
                cashierId: auth()->id(),
                shiftId: $activeShift->id
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'grand_total' => $sale->grand_total,
                'status' => $sale->status,
                'receipt_url' => route('pos.receipt', $sale),
            ]);
        }

        return redirect()->route('pos.receipt', $sale)
            ->with('success', "Transaksi {$sale->invoice_number} berhasil diselesaikan.");
    }

    /**
     * Today's sales history list.
     */
    public function history(Request $request): View
    {
        $query = Sale::with(['customer', 'cashier', 'payments', 'items.product'])
            ->latest('sale_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('sale_date', $request->input('date'));
        } else {
            $query->whereDate('sale_date', now()->toDateString());
        }

        $sales = $query->paginate(20)->withQueryString();

        return view('pos.history', compact('sales'));
    }

    /**
     * Void a transaction with reason.
     */
    public function void(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $this->saleService->voidSale($sale, $validated['reason'], auth()->id());

            return back()->with('success', "Transaksi #{$sale->invoice_number} berhasil dibatalkan (void) dan stok telah dipulihkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: '.$e->getMessage());
        }
    }

    /**
     * Display thermal printable receipt (58mm / 80mm).
     */
    public function receipt(Sale $sale): View
    {
        $sale->load(['customer', 'cashier', 'payments', 'items.product', 'items.unit']);

        return view('pos.receipt', compact('sale'));
    }

    /**
     * Print or download PDF receipt.
     */
    public function receiptPdf(Sale $sale): Response
    {
        $sale->load(['customer', 'cashier', 'payments', 'items.product', 'items.unit']);

        $pdf = Pdf::loadView('pos.receipt', compact('sale'));

        return $pdf->stream("Struk-{$sale->invoice_number}.pdf");
    }
}
