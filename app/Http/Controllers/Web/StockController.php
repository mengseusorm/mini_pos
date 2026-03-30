<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockMovementRequest;
use App\Models\Item;
use App\Models\StockMovement;

class StockController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['item', 'user'])->latest()->paginate(15);
        $items     = Item::orderBy('name')->get();

        return view('stock.index', compact('movements', 'items'));
    }

    public function store(StockMovementRequest $request)
    {
        $data = $request->validated();

        $item = Item::findOrFail($data['item_id']);

        if ($data['type'] === 'out' && $item->stock < $data['quantity']) {
            return back()->withErrors(['quantity' => "Insufficient stock. Current stock: {$item->stock}"])->withInput();
        }

        $item->stock += ($data['type'] === 'in') ? $data['quantity'] : -$data['quantity'];
        $item->save();

        StockMovement::create(array_merge($data, ['user_id' => auth()->id()]));

        return back()->with('success', 'Stock movement recorded.');
    }
}
