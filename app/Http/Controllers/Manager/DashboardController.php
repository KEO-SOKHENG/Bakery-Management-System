<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Render the Manager Operational Dashboard with real database metrics.
     */
    public function index()
    {
        $todayOrdersCount = Order::whereDate('created_at', today())->count();
        $todaySalesSum = (float) Sale::whereDate('sold_at', today())->sum('total');
        $totalProducts = Product::where('status', 'active')->count();
        $lowStockCount = Ingredient::whereColumn('quantity', '<=', 'minimum_quantity')->count();

        // Production Quota Progress (Real database production batches today)
        $todayProductions = Production::with(['product', 'baker'])
            ->whereDate('production_date', today())
            ->get();

        $activeProductionsCount = Production::whereIn('status', ['scheduled', 'in_progress'])->count();
        $completedTodayUnits = (float) Production::whereDate('production_date', today())
            ->where('status', 'completed')
            ->sum('quantity');

        // Recent Orders
        $recentOrders = Order::with('customer')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Low stock ingredients requiring replenishment
        $lowStockIngredients = Ingredient::whereColumn('quantity', '<=', 'minimum_quantity')
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        // Procurement metrics
        $pendingPosCount = PurchaseOrder::whereIn('status', ['draft', 'ordered'])->count();
        $suppliersCount = Supplier::where('status', 'active')->count();

        $currency = Setting::get('currency', '$');

        return view('manager.dashboard', compact(
            'todayOrdersCount',
            'todaySalesSum',
            'totalProducts',
            'lowStockCount',
            'todayProductions',
            'activeProductionsCount',
            'completedTodayUnits',
            'recentOrders',
            'lowStockIngredients',
            'pendingPosCount',
            'suppliersCount',
            'currency'
        ));
    }
}
