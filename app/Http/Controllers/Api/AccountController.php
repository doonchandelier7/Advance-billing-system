<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Account::query()->with('parent:id,name,code');
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        $accounts = $query->orderBy('code')->paginate($request->integer('per_page', 50));
        return response()->json($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:32|unique:accounts,code',
            'type' => 'required|string|in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:512',
        ]);
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $account = Account::create($validated);
        return response()->json($account->load('parent'), 201);
    }

    public function show(Account $account): JsonResponse
    {
        $account->load('parent', 'children');
        return response()->json($account);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'code' => 'sometimes|string|max:32|unique:accounts,code,' . $account->id,
            'type' => 'sometimes|string|in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:512',
        ]);
        if (isset($validated['parent_id']) && (int) $validated['parent_id'] === (int) $account->id) {
            return response()->json(['message' => 'Account cannot be its own parent.'], 422);
        }
        $account->update($validated);
        return response()->json($account->load('parent'));
    }

    public function destroy(Account $account): JsonResponse
    {
        if ($account->children()->exists()) {
            return response()->json(['message' => 'Account has sub-accounts. Remove or reassign them first.'], 422);
        }
        $account->delete();
        return response()->json(null, 204);
    }
}
