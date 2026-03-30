<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'today_sales'     => Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total'),
            'today_orders'    => Order::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'month_sales'     => Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
            'total_items'     => Item::count(),
            'low_stock_items' => Item::where('stock', '<=', 10)->count(),
            'total_users'     => User::count(),
        ];

        $recentOrders = Order::with(['user', 'items.item'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentOrders'));
    }
}
