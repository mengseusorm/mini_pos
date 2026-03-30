<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items      = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }

    public function store(ItemRequest $request)
    {
        Item::create($request->validated());

        return back()->with('success', 'Item created successfully.');
    }

    public function update(ItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return back()->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return back()->with('success', 'Item deleted.');
    }
}
