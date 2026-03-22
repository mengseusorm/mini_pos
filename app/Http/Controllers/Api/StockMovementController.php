<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['item', 'user'])->latest();

        if ($request->item_id) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'item_id'  => 'required|exists:items,id',
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string',
        ]);

        $item = \App\Models\Item::findOrFail($data['item_id']);

        if ($data['type'] === 'out' && $item->stock < $data['quantity']) {
            return response()->json(['message' => 'Insufficient stock.'], 422);
        }

        $item->stock += ($data['type'] === 'in') ? $data['quantity'] : -$data['quantity'];
        $item->save();

        $movement = StockMovement::create(array_merge($data, ['user_id' => $request->user()->id]));

        return response()->json($movement->load(['item', 'user']), 201);
    }
}
