<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'manufacturer'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->drug_class, function ($query, $drugClass) {
                $query->where('drug_class', $drugClass);
            })
            ->when($request->manufacturer_id, function ($query, $manufacturerId) {
                $query->where('manufacturer_id', $manufacturerId);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::active()->orderBy('name')->get();
        $manufacturers = Manufacturer::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'manufacturers'));
    }

    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();
        $manufacturers = Manufacturer::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.create', compact('categories', 'manufacturers', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:30', 'unique:products,product_code'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'drug_class' => ['nullable', 'string', 'max:30'],
            'form' => ['nullable', 'string', 'max:50'],
            'strength' => ['nullable', 'string', 'max:50'],
            'manufacturer_id' => ['nullable', 'exists:manufacturers,id'],
            'requires_prescription' => ['boolean'],
            'storage_condition' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit_id' => ['required', 'exists:units,id'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:0.01'],
            'units.*.is_base_unit' => ['boolean'],
            'units.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'units.*.selling_price' => ['required', 'numeric', 'min:0'],
            'units.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.prescription_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['requires_prescription'] = $request->boolean('requires_prescription');
        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create(collect($validated)->except('units')->toArray());

        if (! empty($validated['units'])) {
            foreach ($validated['units'] as $unitData) {
                $unitData['is_base_unit'] = ! empty($unitData['is_base_unit']);
                $product->productUnits()->create($unitData);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'manufacturer', 'productUnits.unit']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('productUnits.unit');
        $categories = Category::active()->orderBy('name')->get();
        $manufacturers = Manufacturer::active()->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'manufacturers', 'units'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:30', 'unique:products,product_code,'.$product->id],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode,'.$product->id],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'drug_class' => ['nullable', 'string', 'max:30'],
            'form' => ['nullable', 'string', 'max:50'],
            'strength' => ['nullable', 'string', 'max:50'],
            'manufacturer_id' => ['nullable', 'exists:manufacturers,id'],
            'requires_prescription' => ['boolean'],
            'storage_condition' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'units' => ['nullable', 'array'],
            'units.*.unit_id' => ['required', 'exists:units,id'],
            'units.*.conversion_factor' => ['required', 'numeric', 'min:0.01'],
            'units.*.is_base_unit' => ['boolean'],
            'units.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'units.*.selling_price' => ['required', 'numeric', 'min:0'],
            'units.*.wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'units.*.prescription_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['requires_prescription'] = $request->boolean('requires_prescription');
        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update(collect($validated)->except('units')->toArray());

        $product->productUnits()->delete();
        if (! empty($validated['units'])) {
            foreach ($validated['units'] as $unitData) {
                $unitData['is_base_unit'] = ! empty($unitData['is_base_unit']);
                $product->productUnits()->create($unitData);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
