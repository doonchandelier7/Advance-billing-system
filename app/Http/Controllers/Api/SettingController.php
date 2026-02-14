<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();
        if ($request->filled('group')) {
            $query->where('group', $request->input('group'));
        }
        $settings = $query->get()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function show(string $key): JsonResponse
    {
        $value = Setting::get($key);
        return response()->json(['key' => $key, 'value' => $value]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:191',
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:64',
        ]);
        Setting::set(
            $validated['key'],
            $validated['value'] ?? '',
            $validated['group'] ?? 'general'
        );
        return response()->json(['key' => $validated['key'], 'value' => $validated['value'] ?? '']);
    }
}
