<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function withImage(Category $category): array
    {
        $data                = $category->toArray();
        $data['image_url']   = $category->getFirstMediaUrl('image') ?: null;
        $data['image_thumb'] = $category->getFirstMediaUrl('image', 'thumb') ?: null;
        return $data;
    }

    public function index(): JsonResponse
    {
        $categories = Category::withCount('items')->get();

        $result = $categories->map(function (Category $cat) {
            $arr                = $cat->toArray();
            $arr['image_url']   = $cat->getFirstMediaUrl('image') ?: null;
            $arr['image_thumb'] = $cat->getFirstMediaUrl('image', 'thumb') ?: null;
            return $arr;
        });

        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $data     = $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create($data);

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return response()->json($this->withImage($category), 201);
    }

    public function show(Category $category): JsonResponse
    {
        $data                = $category->load('items')->toArray();
        $data['image_url']   = $category->getFirstMediaUrl('image') ?: null;
        $data['image_thumb'] = $category->getFirstMediaUrl('image', 'thumb') ?: null;
        return response()->json($data);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $category->update($data);

        return response()->json($this->withImage($category));
    }

    public function uploadImage(Request $request, Category $category): JsonResponse
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,webp|max:2048']);

        $media = $category->addMediaFromRequest('image')->toMediaCollection('image');

        return response()->json([
            'image_url'   => $media->getUrl(),
            'image_thumb' => $media->getUrl('thumb'),
        ]);
    }

    public function deleteImage(Category $category): JsonResponse
    {
        $category->clearMediaCollection('image');
        return response()->json(['image_url' => null, 'image_thumb' => null]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, 204);
    }
}
