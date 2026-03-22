<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.item', 'payment'])->latest();

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.item_id'       => 'required|exists:items,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'discount'              => 'nullable|numeric|min:0',
            'tax'                   => 'nullable|numeric|min:0',
            'payment_method'        => 'required|in:cash,card',
            'payment_amount'        => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $discount = $request->discount ?? 0;
            $taxRate  = $request->tax ?? 0;
            $subtotal = 0;
            $orderLines = [];

            foreach ($request->items as $line) {
                $item = Item::lockForUpdate()->findOrFail($line['item_id']);

                if ($item->stock < $line['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Insufficient stock for item: {$item->name}",
                    ], 422);
                }

                $lineSubtotal = $item->price * $line['quantity'];
                $subtotal += $lineSubtotal;

                $orderLines[] = [
                    'item'      => $item,
                    'quantity'  => $line['quantity'],
                    'price'     => $item->price,
                    'subtotal'  => $lineSubtotal,
                ];
            }

            $taxAmount = round($subtotal * $taxRate / 100, 2);
            $total     = round($subtotal - $discount + $taxAmount, 2);
            $change    = round($request->payment_amount - $total, 2);

            if ($change < 0) {
                DB::rollBack();
                return response()->json(['message' => 'Payment amount is insufficient.'], 422);
            }

            $order = Order::create([
                'user_id'        => $request->user()->id,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $taxAmount,
                'total'          => $total,
                'payment_method' => $request->payment_method,
                'status'         => 'completed',
            ]);

            foreach ($orderLines as $line) {
                $order->items()->create([
                    'item_id'  => $line['item']->id,
                    'quantity' => $line['quantity'],
                    'price'    => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['item']->decrement('stock', $line['quantity']);

                StockMovement::create([
                    'item_id'  => $line['item']->id,
                    'user_id'  => $request->user()->id,
                    'type'     => 'out',
                    'quantity' => $line['quantity'],
                    'note'     => "Order #{$order->id}",
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'amount'   => $request->payment_amount,
                'change'   => $change,
            ]);

            DB::commit();

            return response()->json($order->load(['items.item', 'payment', 'user']), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Order failed: ' . $e->getMessage()], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['items.item', 'payment', 'user']));
    }

    public function receipt(Order $order): JsonResponse
    {
        return response()->json($order->load(['items.item', 'payment', 'user']));
    }
}
