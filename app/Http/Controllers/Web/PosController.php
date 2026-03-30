<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $items      = Item::with('category')->where('stock', '>', 0)->orderBy('name')->get();

        return view('pos.index', compact('categories', 'items'));
    }
}
