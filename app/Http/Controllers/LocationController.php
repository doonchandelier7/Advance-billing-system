<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected static ?array $locationData = null;

    protected function data(): array
    {
        if (static::$locationData === null) {
            $path = database_path('data/indian_locations.json');
            static::$locationData = json_decode(file_get_contents($path), true);
        }
        return static::$locationData;
    }

    /**
     * Search states by query string.
     * GET /api/locations/states?q=Mah
     */
    public function states(Request $request): JsonResponse
    {
        $q = strtolower(trim($request->query('q', '')));
        $states = collect($this->data()['states'])
            ->filter(fn ($s) => $q === '' || str_contains(strtolower($s['name']), $q))
            ->map(fn ($s) => ['code' => $s['code'], 'name' => $s['name']])
            ->values();

        return response()->json($states);
    }

    /**
     * Get districts for a given state, optionally filtered by query.
     * GET /api/locations/districts?state=Maharashtra&q=Pun
     */
    public function districts(Request $request): JsonResponse
    {
        $stateName = trim($request->query('state', ''));
        $q = strtolower(trim($request->query('q', '')));

        $state = collect($this->data()['states'])
            ->first(fn ($s) => strtolower($s['name']) === strtolower($stateName));

        if (!$state) {
            $allDistricts = collect($this->data()['states'])
                ->flatMap(fn ($s) => array_map(fn ($d) => ['district' => $d, 'state' => $s['name']], $s['districts']))
                ->filter(fn ($d) => $q === '' || str_contains(strtolower($d['district']), $q))
                ->values()
                ->take(20);
            return response()->json($allDistricts);
        }

        $districts = collect($state['districts'])
            ->filter(fn ($d) => $q === '' || str_contains(strtolower($d), $q))
            ->map(fn ($d) => ['district' => $d, 'state' => $state['name']])
            ->values();

        return response()->json($districts);
    }

    /**
     * Get cities for a given state, optionally filtered by query.
     * GET /api/locations/cities?state=Maharashtra&q=Pun
     */
    public function cities(Request $request): JsonResponse
    {
        $stateName = trim($request->query('state', ''));
        $q = strtolower(trim($request->query('q', '')));

        $state = collect($this->data()['states'])
            ->first(fn ($s) => strtolower($s['name']) === strtolower($stateName));

        if (!$state) {
            $allCities = collect($this->data()['states'])
                ->flatMap(fn ($s) => array_map(fn ($c) => ['city' => $c, 'state' => $s['name']], $s['cities']))
                ->filter(fn ($c) => $q === '' || str_contains(strtolower($c['city']), $q))
                ->values()
                ->take(20);
            return response()->json($allCities);
        }

        $cities = collect($state['cities'])
            ->filter(fn ($c) => $q === '' || str_contains(strtolower($c), $q))
            ->map(fn ($c) => ['city' => $c, 'state' => $state['name']])
            ->values();

        return response()->json($cities);
    }
}
