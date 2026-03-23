<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Samaj;
use App\Models\State;
use App\Models\City;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;



class LookupController extends Controller
{

    public function masterData(): JsonResponse
    {
        $data = Cache::remember('lookup_master_data', 86400, function () {

            return [

                'states' => State::query()
                    ->orderBy('name')
                    ->get(['id', 'name']),

                'samaj' => Samaj::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name']),

                'educations' => UserProfile::query()
                    ->select('education')
                    ->whereNotNull('education')
                    ->where('education', '!=', '')
                    ->distinct()
                    ->orderBy('education')
                    ->pluck('education'),

                'occupations' => UserProfile::query()
                    ->select('occupation')
                    ->whereNotNull('occupation')
                    ->where('occupation', '!=', '')
                    ->distinct()
                    ->orderBy('occupation')
                    ->pluck('occupation')

            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function searchCities(Request $request): JsonResponse
    {
        $request->validate([
            'state_id' => 'required|integer|exists:states,id',
            'search' => 'nullable|string|max:50'
        ]);

        $stateId = $request->state_id;
        $search = $request->search;

        $cities = City::query()
            ->where('state_id', $stateId)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', $search . '%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'latitude', 'longitude']);

        return response()->json([
            'status' => true,
            'data' => $cities
        ]);
    }

    // API to fetch distinct states
    public function states(): JsonResponse
    {
        $states = Cache::remember('lookup_states', 86400, function () {

            return State::query()
                ->orderBy('name')
                ->get(['id', 'name']);

        });

        return response()->json([
            'status' => true,
            'message' => 'States list retrieved successfully',
            'data' => $states
        ]);
    }

    // API to fetch samaj list
    public function samaj(): JsonResponse
    {
        $samajList = Cache::remember('lookup_samaj', 86400, function () {

            return Samaj::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'page_title'
                ]);

        });

        return response()->json([
            'status' => true,
            'message' => 'Samaj list retrieved successfully',
            'data' => $samajList
        ]);
    }

    // API to fetch cities based on state
    public function citiesByState(Request $request): JsonResponse
    {
        $request->validate([
            'state_id' => 'nullable|integer|exists:states,id'
        ]);

        // Default state id if not provided (Maharashtra)
        $stateId = $request->input('state_id', 21);

        $cities = Cache::remember(
            'lookup_cities_' . $stateId,
            86400,
            function () use ($stateId) {

                return City::query()
                    ->where('state_id', $stateId)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name'
                    ]);

            }
        );

        return response()->json([
            'status' => true,
            'message' => 'Cities list retrieved successfully',
            'data' => $cities
        ]);
    }

    // API to fetch distinct professions
    public function occupations(): JsonResponse
    {
        $occupations = Cache::remember('lookup_occupations', 86400, function () {

            return UserProfile::query()
                ->select('occupation')
                ->whereNotNull('occupation')
                ->where('occupation', '!=', '')
                ->distinct()
                ->orderBy('occupation')
                ->pluck('occupation');

        });

        return response()->json([
            'status' => true,
            'message' => 'Occupations list retrieved successfully',
            'data' => $occupations
        ]);
    }

    // API to fetch distinct education qualifications
    public function educations(): JsonResponse
    {
        $educations = Cache::remember('lookup_educations', 86400, function () {

            return UserProfile::query()
                ->select('education')
                ->whereNotNull('education')
                ->where('education', '!=', '')
                ->distinct()
                ->orderBy('education')
                ->pluck('education');

        });

        return response()->json([
            'status' => true,
            'message' => 'Education list retrieved successfully',
            'data' => $educations
        ]);
    }
}
