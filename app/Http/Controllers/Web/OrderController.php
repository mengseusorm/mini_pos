<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.item', 'payment'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,transfer,qris',
            'payment_amount' => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',
        ]);

        $discount = $data['discount'] ?? 0;
        $tax = $data['tax'] ?? 0;

        $items = Item::whereIn('id', collect($data['items'])->pluck('item_id'))->get()->keyBy('id');

        $subtotal = 0;

        foreach ($data['items'] as $line) {
            $item = $items->get($line['item_id']);
            if (! $item) {
                return response()->json(['message' => 'Item not found.'], 404);
            }
            if ($item->stock < $line['quantity']) {
                return response()->json(['message' => "Insufficient stock for {$item->name}. Available: {$item->stock}"], 422);
            }
            $subtotal += $item->price * $line['quantity'];
        }

        $total = $subtotal - $discount + $tax;

        if ($data['payment_method'] === 'cash' && $data['payment_amount'] < $total) {
            return response()->json(['message' => 'Amount paid is less than total'], 422);
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id'        => auth()->id(),
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'payment_method' => $data['payment_method'],
                'status'         => 'completed',
            ]);

            foreach ($data['items'] as $line) {
                $item = $items->get($line['item_id']);
                $lineSubtotal = $item->price * $line['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id'  => $item->id,
                    'quantity' => $line['quantity'],
                    'price'    => $item->price,
                    'subtotal' => $lineSubtotal,
                ]);

                // Reduce stock
                $item->decrement('stock', $line['quantity']);

                StockMovement::create([
                    'item_id'  => $item->id,
                    'user_id'  => auth()->id(),
                    'type'     => 'out',
                    'quantity' => $line['quantity'],
                    'note'     => 'Sale order #'.$order->id,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'amount'   => $data['payment_amount'],
                'change'   => max(0, $data['payment_amount'] - $total),
            ]);

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'data' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed: '.$e->getMessage()], 500);
        }
    }
}
