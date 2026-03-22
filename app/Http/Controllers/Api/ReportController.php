<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function daily(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $from = $request->date_from ?? now()->startOfMonth()->toDateString();
        $to   = $request->date_to ?? now()->toDateString();

        $data = Order::where('status', 'completed')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(total) as revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    public function monthly(Request $request): JsonResponse
    {
        $year = $request->year ?? now()->year;

        $data = Order::where('status', 'completed')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total_orders, SUM(total) as revenue')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return response()->json($data);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date',
            'limit'     => 'nullable|integer|min:1|max:50',
        ]);

        $from  = $request->date_from ?? now()->startOfMonth()->toDateString();
        $to    = $request->date_to ?? now()->toDateString();
        $limit = $request->limit ?? 10;

        $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('orders.status', 'completed')
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
            ->selectRaw('items.id, items.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as revenue')
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return response()->json($data);
    }
}
