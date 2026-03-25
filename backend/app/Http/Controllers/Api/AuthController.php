<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ProfileFamily;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | REGISTER USER
    |--------------------------------------------------------------------------
    */

    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Create User Account
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'first_name' => trim($data['firstName']),
                'last_name'  => trim($data['lastName']),
                'mobile'     => $data['mobileNumber'],
                'email'      => $data['emailId'] ?? null,
                'gender'     => ucfirst($data['gender']),
                'password'   => Hash::make($data['password']),
                'profile_status' => 'INPROCESS'
            ]);


            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Create Profile
            |--------------------------------------------------------------------------
            */

            $profile = UserProfile::create([
                'user_id' => $user->id,

                'birth_date' => $data['birthDate'],
                'birth_time' => $data['birthTime'] ?? null,

                'samaj' => $data['samajId'],

                'education' => $data['education'],
                'occupation' => $data['occupation'],

                'city_id' => $data['city']
            ]);


            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Create Default Family Record
            |--------------------------------------------------------------------------
            */

            ProfileFamily::create([
                'user_id' => $user->id
            ]);


            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 4️⃣ Create Sanctum Token
            |--------------------------------------------------------------------------
            */

            $token = $user->createToken('matrimonial-api')->plainTextToken;


            /*
            |--------------------------------------------------------------------------
            | 5️⃣ Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => [

                    'token' => $token,

                    'user' => [
                        'id' => $user->id,
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'gender' => $user->gender,
                        'profileStatus' => $user->profile_status
                    ]

                ]

            ], 201);


        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('Registration failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'error_code' => 'SERVER_ERROR',
                'message' => 'Registration failed'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN USER
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|min:8'
        ]);

        try {

            $login = $request->login;

            $user = User::query()
                ->when(
                    filter_var($login, FILTER_VALIDATE_EMAIL),
                    fn($q) => $q->where('email', $login),
                    fn($q) => $q->where('mobile', $login)
                )
                ->first();


            if (!$user || !Hash::check($request->password, $user->password)) {

                return response()->json([
                    'status' => false,
                    'error_code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid login credentials'
                ], 401);

            }


            /*
            |--------------------------------------------------------------------------
            | Generate Token
            |--------------------------------------------------------------------------
            */

            $token = $user->createToken('matrimonial-api')->plainTextToken;


            /*
            |--------------------------------------------------------------------------
            | Update last login
            |--------------------------------------------------------------------------
            */

            $user->update([
                'last_login' => now()
            ]);


            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [

                    'token' => $token,

                    'user' => [
                        'id' => $user->id,
                        'firstName' => $user->first_name,
                        'gender' => $user->gender,
                        'profileStatus' => $user->profile_status
                    ]

                ]

            ]);

        } catch (\Throwable $e) {

            \Log::error('Login failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'error_code' => 'SERVER_ERROR',
                'message' => 'Internal server error'
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LOGOUT USER
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK USER EXISTS
    |--------------------------------------------------------------------------
    */

    public function checkUserExists(Request $request)
    {

        $request->validate([
            'email' => 'nullable|email',
            'mobile' => 'nullable|digits:10'
        ]);

        if (!$request->email && !$request->mobile) {

            return response()->json([
                'status' => false,
                'message' => 'Email or mobile required'
            ], 422);
        }


        if ($request->email && User::where('email', $request->email)->exists()) {

            return response()->json([
                'status' => false,
                'error_code' => 'EMAIL_EXISTS',
                'message' => 'Email already registered'
            ], 409);
        }


        if ($request->mobile && User::where('mobile', $request->mobile)->exists()) {

            return response()->json([
                'status' => false,
                'error_code' => 'MOBILE_EXISTS',
                'message' => 'Mobile already registered'
            ], 409);
        }


        return response()->json([
            'status' => true,
            'message' => 'User available'
        ]);
    }
}
