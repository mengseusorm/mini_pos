<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type    = $request->input('type', 'daily');
        $data    = collect();
        $summary = null;

        if ($type === 'daily') {
            $from = $request->input('date_from', now()->startOfMonth()->toDateString());
            $to   = $request->input('date_to', now()->toDateString());

            $data = Order::where('status', 'completed')
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(total) as revenue')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $summary = [
                'total_orders' => $data->sum('total_orders'),
                'revenue'      => $data->sum('revenue'),
            ];
        } elseif ($type === 'monthly') {
            $year = $request->input('year', now()->year);

            $data = Order::where('status', 'completed')
                ->whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, MONTHNAME(created_at) as month_name, COUNT(*) as total_orders, SUM(total) as revenue')
                ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
                ->orderBy('month')
                ->get();

            $summary = [
                'total_orders' => $data->sum('total_orders'),
                'revenue'      => $data->sum('revenue'),
            ];
        } elseif ($type === 'top_products') {
            $from  = $request->input('date_from', now()->startOfMonth()->toDateString());
            $to    = $request->input('date_to', now()->toDateString());
            $limit = (int) $request->input('limit', 10);

            $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->where('orders.status', 'completed')
                ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to])
                ->selectRaw('items.name as item_name, SUM(order_items.quantity) as total_quantity, SUM(order_items.subtotal) as revenue')
                ->groupBy('order_items.item_id', 'items.name')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->get();
        }

        return view('reports.index', compact('type', 'data', 'summary'));
    }
}
