<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Item::with('category');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', $request->search);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->low_stock) {
            $query->where('stock', '<=', 10);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'barcode'     => 'nullable|string|unique:items,barcode',
        ]);

        $item = Item::create($data);

        return response()->json($item->load('category'), 201);
    }

    public function show(Item $item): JsonResponse
    {
        return response()->json($item->load('category'));
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'barcode'     => 'nullable|string|unique:items,barcode,' . $item->id,
        ]);

        $item->update($data);

        return response()->json($item->load('category'));
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json(null, 204);
    }

    public function updateStock(Request $request, Item $item): JsonResponse
    {
        $data = $request->validate([
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string',
        ]);

        if ($data['type'] === 'out' && $item->stock < $data['quantity']) {
            return response()->json(['message' => 'Insufficient stock.'], 422);
        }

        $item->stock += ($data['type'] === 'in') ? $data['quantity'] : -$data['quantity'];
        $item->save();

        StockMovement::create([
            'item_id'  => $item->id,
            'user_id'  => $request->user()->id,
            'type'     => $data['type'],
            'quantity' => $data['quantity'],
            'note'     => $data['note'] ?? null,
        ]);

        return response()->json($item->load('category'));
    }
}
