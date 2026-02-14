<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('products');
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        $categories = $query->orderBy('name')->paginate($request->integer('per_page', 15));
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191|unique:categories,slug',
            'description' => 'nullable|string|max:512',
            'is_active' => 'boolean',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }
        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'slug' => 'nullable|string|max:191|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string|max:512',
            'is_active' => 'boolean',
        ]);
        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Category has products. Remove or reassign them first.'], 422);
        }
        $category->delete();
        return response()->json(null, 204);
    }
}
