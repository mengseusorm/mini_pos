<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('items')->latest()->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return back()->with('success', 'Category created successfully.');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
