<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $todaySales   = Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total');
        $todayOrders  = Order::whereDate('created_at', $today)->where('status', 'completed')->count();
        $monthSales   = Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
        $totalItems   = Item::count();
        $lowStockItems = Item::where('stock', '<=', 10)->count();
        $totalUsers   = User::count();

        $recentOrders = Order::with(['user', 'items.item'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'today_sales'    => $todaySales,
            'today_orders'   => $todayOrders,
            'month_sales'    => $monthSales,
            'total_items'    => $totalItems,
            'low_stock_items' => $lowStockItems,
            'total_users'    => $totalUsers,
            'recent_orders'  => $recentOrders,
        ]);
    }
}
