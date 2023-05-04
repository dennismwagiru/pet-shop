<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    use ApiResponse;

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

            return $this->apiResponse(['success' => 1, 'data' => ['token' => $jwtToken->unique_id]]);
        }

        return $this->apiResponse(
            data: ['error' => 'Failed to authenticate user'],
            statusCode: 422
        );
    }

    public function logout(): JsonResponse
    {
        Auth::guard('jwt')->logout();

        return $this->apiResponse();
    }

    public function create(UserRequest $request): JsonResponse
    {
        $user = User::create(array_merge(
            $request->all(),
            [
                'is_marketing' => $request->has('marketing'),
                'is_admin' => true,
                'password' => bcrypt($request->get('password')),
            ]
        ));

        $jwtToken = $user->generateJwtToken();

        return $this->apiResponse(
            data: [
                'success' => 1,
                'data' => array_merge(
                    $user->toArray(),
                    ['token' => $jwtToken->unique_id]
                ),
            ]
        );
    }
}
