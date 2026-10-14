<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::with('supplier')->withCount(['recipes', 'purchaseOrderItems']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search, $like) {
                      $sq->where('name', $like, "%{$search}%");
                  });
            });
        }

        if ($request->filled('supplier_id') && $request->supplier_id !== 'all') {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'out') {
                $query->where('quantity', '<=', 0);
            } elseif ($status === 'low') {
                $query->where('quantity', '>', 0)
                      ->whereColumn('quantity', '<=', 'minimum_quantity');
            } elseif ($status === 'normal') {
                $query->whereColumn('quantity', '>', 'minimum_quantity');
            }
        }

        $ingredients = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        // Consolidated KPI summary directly via database aggregation
        $statsSummary = Ingredient::query()
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN quantity > 0 AND quantity <= minimum_quantity THEN 1 END) as low_stock,
                COUNT(CASE WHEN quantity <= 0 THEN 1 END) as out_of_stock,
                COALESCE(SUM(quantity * cost), 0) as total_value
            ")
            ->first();

        $stats = [
            'total'        => (int) ($statsSummary->total ?? 0),
            'low_stock'    => (int) ($statsSummary->low_stock ?? 0),
            'out_of_stock' => (int) ($statsSummary->out_of_stock ?? 0),
            'total_value'  => (float) ($statsSummary->total_value ?? 0.0),
        ];

        return view('admin.ingredients', compact('ingredients', 'suppliers', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'name'             => 'required|string|max:255',
            'unit'             => 'required|string|max:50',
            'quantity'         => 'required|numeric|min:0',
            'cost'             => 'required|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'expiry_date'      => 'nullable|date',
            'status'           => 'nullable|string|in:active,inactive',
        ]);

        $ingredient = DB::transaction(function () use ($validated) {
            $ingredient = Ingredient::create([
                'supplier_id'      => $validated['supplier_id'] ?? null,
                'name'             => $validated['name'],
                'unit'             => $validated['unit'],
                'quantity'         => $validated['quantity'],
                'cost'             => $validated['cost'],
                'minimum_quantity' => $validated['minimum_quantity'] ?? 10.00,
                'expiry_date'      => $validated['expiry_date'] ?? null,
                'status'           => $validated['status'] ?? 'active',
            ]);

            // If initial quantity > 0, record initial stock movement
            if ((float) $ingredient->quantity > 0) {
                StockMovement::create([
                    'ingredient_id'    => $ingredient->id,
                    'type'             => 'in',
                    'quantity'         => $ingredient->quantity,
                    'unit'             => $ingredient->unit,
                    'unit_cost'        => $ingredient->cost,
                    'reference_type'   => 'manual_adjustment',
                    'notes'            => 'Initial inventory stock on creation',
                    'user_id'          => Auth::id(),
                ]);
            }

            return $ingredient;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ingredient added successfully!',
                'data'    => $ingredient->load('supplier'),
            ]);
        }

        return redirect()->route('admin.ingredients')->with('success', 'Ingredient added successfully!');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'name'             => 'required|string|max:255',
            'unit'             => 'required|string|max:50',
            'quantity'         => 'required|numeric|min:0',
            'cost'             => 'required|numeric|min:0',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'expiry_date'      => 'nullable|date',
            'status'           => 'nullable|string|in:active,inactive',
        ]);

        $validated['minimum_quantity'] = $validated['minimum_quantity'] ?? $ingredient->minimum_quantity ?? 10.00;
        $validated['status'] = $validated['status'] ?? $ingredient->status ?? 'active';

        $oldQty = (float) $ingredient->quantity;
        $newQty = (float) $validated['quantity'];

        DB::transaction(function () use ($ingredient, $validated, $oldQty, $newQty) {
            $ingredient->update($validated);

            // Record manual adjustment if stock was changed directly from ingredient edit
            if (abs($newQty - $oldQty) > 0.001) {
                $diff = round($newQty - $oldQty, 2);
                StockMovement::create([
                    'ingredient_id'    => $ingredient->id,
                    'type'             => $diff > 0 ? 'in' : 'out',
                    'quantity'         => abs($diff),
                    'unit'             => $ingredient->unit,
                    'unit_cost'        => $ingredient->cost,
                    'reference_type'   => 'manual_adjustment',
                    'notes'            => "Manual inventory adjustment ({$oldQty} -> {$newQty})",
                    'user_id'          => Auth::id(),
                ]);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ingredient updated successfully!',
                'data'    => $ingredient->fresh()->load('supplier'),
            ]);
        }

        return redirect()->route('admin.ingredients')->with('success', 'Ingredient updated successfully!');
    }

    public function destroy(Request $request, Ingredient $ingredient)
    {
        if ($ingredient->recipes()->exists()) {
            $message = "Cannot delete ingredient '{$ingredient->name}': It is currently used in active recipes.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        if ($ingredient->purchaseOrderItems()->exists()) {
            $message = "Cannot delete ingredient '{$ingredient->name}': Historical purchase orders reference this item.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        $name = $ingredient->name;
        $ingredient->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ingredient '{$name}' deleted successfully!",
            ]);
        }

        return redirect()->route('admin.ingredients')->with('success', 'Ingredient deleted successfully!');
    }
}
