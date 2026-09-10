<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount(['ingredients', 'purchaseOrders']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $suppliers = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $stats = [
            'total'       => Supplier::count(),
            'active'      => Supplier::where('status', 'active')->count(),
            'inactive'    => Supplier::where('status', 'inactive')->count(),
            'total_spend' => (float) PurchaseOrder::where('status', 'received')->sum('total_cost'),
        ];

        return view('admin.suppliers', compact('suppliers', 'stats'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadMissing([
            'ingredients',
            'purchaseOrders' => function ($q) {
                $q->with('items.ingredient')->orderBy('order_date', 'desc');
            }
        ]);

        $stats = [
            'total_orders'    => $supplier->purchaseOrders->count(),
            'pending_orders'  => $supplier->purchaseOrders->whereIn('status', ['draft', 'ordered'])->count(),
            'received_orders' => $supplier->purchaseOrders->where('status', 'received')->count(),
            'total_spend'     => (float) $supplier->purchaseOrders->where('status', 'received')->sum('total_cost'),
        ];

        return view('admin.suppliers.show', compact('supplier', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'status'         => 'nullable|string|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        Supplier::create([
            'name'           => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone'          => $validated['phone'] ?? null,
            'email'          => $validated['email'] ?? null,
            'address'        => $validated['address'] ?? null,
            'status'         => $validated['status'] ?? 'active',
            'notes'          => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.suppliers')
            ->with('success', 'Supplier created successfully!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
            'status'         => 'required|string|in:active,inactive',
            'notes'          => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('admin.suppliers')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', "Cannot delete supplier '{$supplier->name}': Historical purchase orders exist. Retaining records is required for auditing.");
        }

        if ($supplier->ingredients()->exists()) {
            return back()->with('error', "Cannot delete supplier '{$supplier->name}': Raw ingredients are linked to this supplier. Please reassign ingredients first.");
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers')
            ->with('success', 'Supplier deleted successfully!');
    }
}
