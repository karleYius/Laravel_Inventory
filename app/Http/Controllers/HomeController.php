<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the dashboard / home page.
     */
    public function index(Request $request)
    {
        $isAdmin = $request->user()->isAdmin();

        return view('home', [
            'isAdmin' => $isAdmin,
            'stats' => Product::inventoryStats($isAdmin),
            'recentProducts' => Product::query()
                ->latest('updated_at')
                ->take(5)
                ->get(),
            'lowStockProducts' => Product::query()
                ->where('qty', '>', 0)
                ->where('qty', '<=', Product::LOW_STOCK_THRESHOLD)
                ->orderBy('qty')
                ->take(5)
                ->get(),
            'outOfStockProducts' => Product::query()
                ->where('qty', '<=', 0)
                ->orderBy('name')
                ->take(5)
                ->get(),
        ]);
    }
}
