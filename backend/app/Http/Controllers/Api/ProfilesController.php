<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ProfilesController extends Controller
{
    public function search(Request $request)
    {
        try {

            /* -----------------------------
               Validation
            --------------------------------*/
            $validated = $request->validate([
                'gender' => 'nullable|in:male,female',
                'samajId' => 'nullable|integer',
                'ageFrom' => 'nullable|integer|min:18|max:80',
                'ageTo' => 'nullable|integer|min:18|max:80',
                'heightFrom' => 'nullable|numeric',
                'heightTo' => 'nullable|numeric',
                'maritalStatus' => 'nullable|string',
                'state' => 'nullable|string',
                'cityId' => 'nullable|integer',
                'searchKey' => 'nullable|string',
                'education' => 'nullable|string',
                'occupation' => 'nullable|string',
                'page' => 'nullable|integer|min:1'
            ]);

            $user = $request->user();

            $query = DB::table('profile_users as pu')
                ->join('profile_family as pf', 'pu.profileUserId', '=', 'pf.profileUserId')
                ->join('samaj as s', 'pu.samajId', '=', 's.id')
                ->leftJoin('states_cities_details as sc', 'pu.currentCityId', '=', 'sc.city_id')
                ->select(
                    'pu.*',
                    's.samaj',
                    'sc.city_name as currentCity',
                    'sc.state as currentState',
                    DB::raw('TIMESTAMPDIFF(YEAR, pu.birthDate, CURDATE()) as age')
                )
                // ->where('pu.profileUserId', '<>', $user->profileUserId)
                ->where('pu.isDeleted', 0)
                ->where('pu.profileStatus', 'Approved');

            /* -----------------------------
               Gender (Quick Search)
            --------------------------------*/
            if (!empty($validated['gender'])) {
                $query->where('pu.gender', $validated['gender']);
            }

            /* -----------------------------
               Samaj Filter
            --------------------------------*/
            if (!empty($validated['samajId'])) {
                $query->where('pu.samajId', $validated['samajId']);
            }

            /* -----------------------------
               Search by Profile ID
            --------------------------------*/
            if (!empty($validated['searchKey'])) {
                $profileId = preg_replace('/\D+/', '', $validated['searchKey']);
                if ($profileId) {
                    $query->where('pu.profileUserId', (int) $profileId);
                }
            }

            /* -----------------------------
               Age Filter
            --------------------------------*/
            if (!empty($validated['ageFrom'])) {
                $query->whereRaw(
                    "TIMESTAMPDIFF(YEAR, pu.birthDate, CURDATE()) >= ?",
                    [$validated['ageFrom']]
                );
            }

            if (!empty($validated['ageTo'])) {
                $query->whereRaw(
                    "TIMESTAMPDIFF(YEAR, pu.birthDate, CURDATE()) <= ?",
                    [$validated['ageTo']]
                );
            }

            /* -----------------------------
               Height
            --------------------------------*/
            if (!empty($validated['heightFrom'])) {
                $query->where('pu.height', '>=', $validated['heightFrom']);
            }

            if (!empty($validated['heightTo'])) {
                $query->where('pu.height', '<=', $validated['heightTo']);
            }

            /* -----------------------------
               Marital Status
            --------------------------------*/
            if (!empty($validated['maritalStatus'])) {
                $query->where('pu.maritalStatus', $validated['maritalStatus']);
            }

            /* -----------------------------
               Location
            --------------------------------*/
            if (!empty($validated['state'])) {
                $query->where('sc.state', $validated['state']);
            }

            if (!empty($validated['cityId'])) {
                $query->where('pu.currentCityId', $validated['cityId']);
            }

            /* -----------------------------
               Education
            --------------------------------*/
            $query->when($validated['education'] ?? null, function ($q, $education) {
                $q->where('pu.education', 'LIKE', '%' . $education . '%');
            });

            /* -----------------------------
                Occupation
            --------------------------------*/
            $query->when($validated['occupation'] ?? null, function ($q, $occupation) {
                $q->whereRaw('TRIM(LOWER(pu.occupation)) = ?', [trim(strtolower($occupation))]);
            });
            /* -----------------------------
               Pagination
            --------------------------------*/
            $profiles = $query->paginate(15);
    // \Log::info($validated);
            return response()->json([
                'success' => true,
                'message' => 'Profiles fetched successfully.',
                'data' => $profiles
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function featuredPublic()
    {
        try {

            $baseQuery = DB::table('profile_users as pu')
                ->join('samaj as s', 'pu.samajId', '=', 's.id')
                ->leftJoin('states_cities_details as sc', 'pu.currentCityId', '=', 'sc.city_id')
                ->select(
                    'pu.profileUserId',
                    'pu.firstName',
                    'pu.birthDate',
                    'pu.birthTime',
                    'pu.education',
                    'pu.height',
                    'pu.aboutMe',
                    'pu.profileStatus',
                    'pu.registrationDatetime',
                    'pu.gender',
                    'pu.pic1',
                    'pu.caste',
                    'pu.occupation',
                    's.samaj',
                    'sc.city_name as currentCity',
                    'sc.state as currentState',

                    DB::raw('TIMESTAMPDIFF(YEAR, pu.birthDate, CURDATE()) as age')
                )

                ->where('pu.isDeleted', 0)
                ->where('pu.profileStatus', 'Approved')
                ->whereNotNull('pu.pic1')
                ->where('pu.pic1', '<>', '');

            // Fetch 8 males
            $males = (clone $baseQuery)
                ->where('pu.gender', 'male')
                ->inRandomOrder()
                ->limit(8)
                ->get();

            // Fetch 7 females
            $females = (clone $baseQuery)
                ->where('pu.gender', 'female')
                ->inRandomOrder()
                ->limit(7)
                ->get();

            // Merge & shuffle
            $profiles = $males->merge($females)->shuffle()->values();

            return response()->json([
                'success' => true,
                'message' => 'Featured profiles fetched successfully.',
                'data' => $profiles
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
