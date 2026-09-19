<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\Unit;
use App\Services\InvoiceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumberService
    ) {}

    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $doctorId = $request->input('doctor_id');
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Prescription::with(['doctor', 'customer', 'validator', 'items.product', 'compounds'])
            ->when($search, function ($q, $search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('doctor', fn ($dq) => $dq->where('name', 'like', "%{$search}%"));
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($startDate, fn ($q) => $q->whereDate('prescription_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('prescription_date', '<=', $endDate))
            ->latest('prescription_date')
            ->latest('id');

        $prescriptions = $query->paginate(15)->withQueryString();

        $totalPrescriptions = Prescription::count();
        $submittedPrescriptions = Prescription::where('status', 'submitted')->count();
        $validatedPrescriptions = Prescription::where('status', 'validated')->count();
        $dispensedPrescriptions = Prescription::where('status', 'dispensed')->count();

        $doctors = Doctor::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('prescriptions.index', compact(
            'prescriptions',
            'doctors',
            'customers',
            'totalPrescriptions',
            'submittedPrescriptions',
            'validatedPrescriptions',
            'dispensedPrescriptions'
        ));
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create(Request $request): View
    {
        $prescriptionNumber = $this->invoiceNumberService->generatePrescriptionNumber();
        $doctors = Doctor::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $products = Product::active()->with(['productUnits.unit'])->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        $selectedCustomerId = $request->input('customer_id');

        return view('prescriptions.create', compact(
            'prescriptionNumber',
            'doctors',
            'customers',
            'products',
            'units',
            'selectedCustomerId'
        ));
    }

    /**
     * Store a newly created prescription in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'prescription_date' => ['required', 'date'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.1'],
            'items.*.dosage' => ['nullable', 'string', 'max:100'],
            'items.*.frequency' => ['nullable', 'string', 'max:100'],
            'items.*.duration_days' => ['nullable', 'integer', 'min:1'],
            'items.*.instructions' => ['nullable', 'string', 'max:255'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('prescriptions', 'public');
        }

        $prescriptionNumber = $this->invoiceNumberService->generatePrescriptionNumber();

        $prescription = DB::transaction(function () use ($validated, $prescriptionNumber, $imagePath) {
            $record = Prescription::create([
                'prescription_number' => $prescriptionNumber,
                'doctor_id' => $validated['doctor_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'prescription_date' => $validated['prescription_date'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'status' => 'submitted',
                'image_url' => $imagePath,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                PrescriptionItem::create([
                    'prescription_id' => $record->id,
                    'product_id' => $itemData['product_id'],
                    'unit_id' => $itemData['unit_id'],
                    'quantity' => $itemData['quantity'],
                    'dosage' => $itemData['dosage'] ?? null,
                    'frequency' => $itemData['frequency'] ?? null,
                    'duration_days' => $itemData['duration_days'] ?? null,
                    'instructions' => $itemData['instructions'] ?? null,
                    'is_compounded' => false,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $record;
        });

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', "Resep {$prescription->prescription_number} berhasil disimpan dan siap ditinjau apoteker.");
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription): View
    {
        $prescription->load([
            'doctor',
            'customer',
            'validator',
            'items.product.category',
            'items.product.productUnits.unit',
            'items.unit',
            'compounds.items.product',
            'compounds.template',
        ]);

        return view('prescriptions.show', compact('prescription'));
    }

    /**
     * Pharmacist validation workflow (Approve / Reject).
     */
    public function validatePrescription(Request $request, Prescription $prescription): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'approve') {
            $prescription->update([
                'status' => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
                'notes' => $validated['notes'] ? ($prescription->notes.' [Validasi: '.$validated['notes'].']') : $prescription->notes,
            ]);

            return back()->with('success', "Resep {$prescription->prescription_number} berhasil divalidasi dan disetujui apoteker.");
        }

        $prescription->update([
            'status' => 'rejected',
            'notes' => $validated['notes'] ? ($prescription->notes.' [Penolakan: '.$validated['notes'].']') : $prescription->notes,
        ]);

        return back()->with('warning', "Resep {$prescription->prescription_number} ditolak apoteker.");
    }

    /**
     * Mark prescription as dispensed.
     */
    public function dispense(Prescription $prescription): RedirectResponse
    {
        if ($prescription->status !== 'validated') {
            return back()->with('error', 'Resep harus berstatus divalidasi sebelum dapat diserahkan (dispensed).');
        }

        $prescription->update(['status' => 'dispensed']);

        return back()->with('success', "Resep {$prescription->prescription_number} telah ditandai selesai diserahkan (dispensed).");
    }

    /**
     * Duplicate prescription (Copy Resep for recurring refills).
     */
    public function copyPrescription(Prescription $prescription): RedirectResponse
    {
        $newNumber = $this->invoiceNumberService->generatePrescriptionNumber();

        $newPrescription = DB::transaction(function () use ($prescription, $newNumber) {
            $new = Prescription::create([
                'prescription_number' => $newNumber,
                'doctor_id' => $prescription->doctor_id,
                'customer_id' => $prescription->customer_id,
                'prescription_date' => now()->toDateString(),
                'diagnosis' => $prescription->diagnosis,
                'status' => 'submitted',
                'image_url' => $prescription->image_url,
                'notes' => "Salinan resep dari {$prescription->prescription_number}. ".$prescription->notes,
            ]);

            foreach ($prescription->items as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $new->id,
                    'product_id' => $item->product_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->quantity,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration_days' => $item->duration_days,
                    'instructions' => $item->instructions,
                    'is_compounded' => $item->is_compounded,
                    'notes' => $item->notes,
                ]);
            }

            return $new;
        });

        return redirect()->route('prescriptions.show', $newPrescription)
            ->with('success', "Salinan resep berhasil dibuat dengan nomor baru {$newPrescription->prescription_number}.");
    }
}
