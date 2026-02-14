<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Unit::query()->withCount('products');
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        $units = $query->orderBy('name')->paginate($request->integer('per_page', 15));
        return response()->json($units);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'symbol' => 'required|string|max:32|unique:units,symbol',
            'description' => 'nullable|string|max:512',
            'is_active' => 'boolean',
        ]);
        $unit = Unit::create($validated);
        return response()->json($unit, 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        $unit->loadCount('products');
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'symbol' => 'sometimes|string|max:32|unique:units,symbol,'.$unit->id,
            'description' => 'nullable|string|max:512',
            'is_active' => 'boolean',
        ]);
        $unit->update($validated);
        return response()->json($unit);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        if ($unit->products()->exists()) {
            return response()->json(['message' => 'Unit has products. Remove or reassign them first.'], 422);
        }
        $unit->delete();
        return response()->json(null, 204);
    }
}
