<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    private function withImage(Item $item): array
    {
        $data                = $item->load('category')->toArray();
        $data['image_url']   = $item->getFirstMediaUrl('image') ?: null;
        $data['image_thumb'] = $item->getFirstMediaUrl('image', 'thumb') ?: null;
        return $data;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Item::with('category');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', $request->search);
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->low_stock) {
            $query->where('stock', '<=', 10);
        }

        $paginated = $query->latest()->paginate(20);

        $paginated->getCollection()->transform(function (Item $item) {
            $arr                = $item->toArray();
            $arr['image_url']   = $item->getFirstMediaUrl('image') ?: null;
            $arr['image_thumb'] = $item->getFirstMediaUrl('image', 'thumb') ?: null;
            return $arr;
        });

        return response()->json($paginated);
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

        if ($request->hasFile('image')) {
            $item->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return response()->json($this->withImage($item), 201);
    }

    public function show(Item $item): JsonResponse
    {
        return response()->json($this->withImage($item));
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

        return response()->json($this->withImage($item));
    }

    public function uploadImage(Request $request, Item $item): JsonResponse
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,webp|max:2048']);

        $media = $item->addMediaFromRequest('image')->toMediaCollection('image');

        return response()->json([
            'image_url'   => $media->getUrl(),
            'image_thumb' => $media->getUrl('thumb'),
        ]);
    }

    public function deleteImage(Item $item): JsonResponse
    {
        $item->clearMediaCollection('image');
        return response()->json(['image_url' => null, 'image_thumb' => null]);
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

        return response()->json($this->withImage($item));
    }
}
