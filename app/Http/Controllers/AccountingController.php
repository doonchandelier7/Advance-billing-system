<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Account::query()->with('parent:id,name,code');
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        $accounts = $query->orderBy('code')->paginate(20)->withQueryString();
        return view('modules.accounting', [
            'accounts' => $accounts,
            'types' => Account::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:32|unique:accounts,code',
            'type' => 'required|string|in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:512',
        ]);
        $validated['opening_balance'] = (float) ($validated['opening_balance'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);
        Account::create($validated);
        return redirect()->route('modules.accounting')->with('success', 'Account created successfully.');
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'code' => 'sometimes|string|max:32|unique:accounts,code,' . $account->id,
            'type' => 'sometimes|string|in:asset,liability,equity,income,expense',
            'opening_balance' => 'nullable|numeric',
            'parent_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:512',
        ]);
        if (isset($validated['parent_id']) && (int) $validated['parent_id'] === (int) $account->id) {
            return redirect()->back()->withInput()->with('error', 'Account cannot be its own parent.');
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $account->update($validated);
        return redirect()->route('modules.accounting')->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->children()->exists()) {
            return redirect()->route('modules.accounting')->with('error', 'Account has sub-accounts. Remove or reassign them first.');
        }
        $account->delete();
        return redirect()->route('modules.accounting')->with('success', 'Account deleted successfully.');
    }
}
