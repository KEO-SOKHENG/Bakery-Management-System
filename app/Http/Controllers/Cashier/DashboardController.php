<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\PosController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return app(PosController::class)->index();
    }
}
