<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Branch::query();
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        $branches = $query->orderBy('name')->get();
        return response()->json($branches);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:32|unique:branches,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);
        if (!empty($validated['is_default'])) {
            Branch::query()->update(['is_default' => false]);
        }
        $branch = Branch::create($validated);
        return response()->json($branch, 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        return response()->json($branch);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'code' => 'nullable|string|max:32|unique:branches,code,'.$branch->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);
        if (!empty($validated['is_default'])) {
            Branch::where('id', '!=', $branch->id)->update(['is_default' => false]);
        }
        $branch->update($validated);
        return response()->json($branch);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();
        return response()->json(null, 204);
    }
}
