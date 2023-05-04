<?php

namespace App\Http\Controllers\Api;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (Auth::guard('jwt')->attemptWhen($request->all(), function (User $user) {
            return $user->is_admin;
        })) {
            $user = User::where('email', $request['email'])->first();

            $jwtToken = $user->generateJwtToken();

            return response()->json([
                'success' => 1,
                'data' => [
                    'token' => $jwtToken->unique_id,
                ],
                'error' => null,
                'errors' => [],
                'extra' => [],
            ]);
        }

        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => 'Failed to authenticate user',
            'errors' => [],
            'trace' => [],
        ], 422);
    }

    public function logout(): JsonResponse
    {
        Auth::guard('jwt')->logout();

        return response()->json([
            'success' => 1,
            'data' => [],
            'error' => null,
            'errors' => [],
            'extra' => [],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|confirmed',
            'avatar' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Failed Validation',
                'errors' => $validator->errors(),
                'trace' => [],
            ], 422);
        }

        $user = User::create(array_merge(
            $request->all(),
            [
                'is_marketing' => $request->has('marketing'),
                'is_admin' => true,
                'password' => bcrypt($request->get('password')),
            ]
        ));

        $jwtToken = $user->generateJwtToken();

        return response()->json([
            'success' => 1,
            'data' => array_merge(
                $user->toArray(),
                ['token' => $jwtToken->unique_id]
            ),
            'error' => null,
            'errors' => [],
            'extra' => [],
        ]);
    }
}
