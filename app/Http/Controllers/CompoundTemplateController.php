<?php

namespace App\Http\Controllers;

use App\Models\CompoundTemplate;
use App\Models\CompoundTemplateItem;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompoundTemplateController extends Controller
{
    /**
     * Display a listing of compound templates.
     */
    public function index(): View
    {
        $templates = CompoundTemplate::with(['creator', 'items.product', 'items.unit'])
            ->withCount(['items', 'compounds'])
            ->latest()
            ->paginate(15);

        return view('compound-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new compound template.
     */
    public function create(): View
    {
        $products = Product::active()->with(['productUnits.unit'])->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('compound-templates.create', compact('products', 'units'));
    }

    /**
     * Store a newly created compound template in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'preparation_instructions' => ['nullable', 'string'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_per_unit' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $template = DB::transaction(function () use ($validated) {
            $record = CompoundTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'preparation_instructions' => $validated['preparation_instructions'] ?? null,
                'service_fee' => $validated['service_fee'],
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                CompoundTemplateItem::create([
                    'template_id' => $record->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_per_unit' => $itemData['quantity_per_unit'],
                    'unit_id' => $itemData['unit_id'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $record;
        });

        return redirect()->route('compound-templates.index')
            ->with('success', "Template racikan '{$template->name}' berhasil dibuat.");
    }

    /**
     * Display the specified compound template.
     */
    public function show(CompoundTemplate $compoundTemplate): View
    {
        $compoundTemplate->load(['creator', 'items.product.category', 'items.unit']);

        return view('compound-templates.show', compact('compoundTemplate'));
    }

    /**
     * Show the form for editing the specified compound template.
     */
    public function edit(CompoundTemplate $compoundTemplate): View
    {
        $compoundTemplate->load(['items.product', 'items.unit']);
        $products = Product::active()->with(['productUnits.unit'])->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('compound-templates.edit', compact('compoundTemplate', 'products', 'units'));
    }

    /**
     * Update the specified compound template in storage.
     */
    public function update(Request $request, CompoundTemplate $compoundTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'preparation_instructions' => ['nullable', 'string'],
            'service_fee' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_per_unit' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $compoundTemplate) {
            $compoundTemplate->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'preparation_instructions' => $validated['preparation_instructions'] ?? null,
                'service_fee' => $validated['service_fee'],
            ]);

            // Synchronize items
            $compoundTemplate->items()->delete();

            foreach ($validated['items'] as $itemData) {
                CompoundTemplateItem::create([
                    'template_id' => $compoundTemplate->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_per_unit' => $itemData['quantity_per_unit'],
                    'unit_id' => $itemData['unit_id'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('compound-templates.index')
            ->with('success', "Template racikan '{$compoundTemplate->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified compound template from storage.
     */
    public function destroy(CompoundTemplate $compoundTemplate): RedirectResponse
    {
        $name = $compoundTemplate->name;
        $compoundTemplate->items()->delete();
        $compoundTemplate->delete();

        return redirect()->route('compound-templates.index')
            ->with('success', "Template racikan '{$name}' berhasil dihapus.");
    }
}
