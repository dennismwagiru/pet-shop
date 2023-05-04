<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse {
        if (Auth::guard('jwt')->attemptWhen($request->all(), function (User $user) {
            return $user->is_admin;
        })) {
            $user = User::where('email', $request['email'])->first();

            $jwtToken = $user->generateJwtToken();

            return response()->json(array(
                "success" => 1,
                "data" => array(
                    "token" => $jwtToken->unique_id
                ),
                "error" => null,
                "errors" => [],
                "extra" => []
            ));
        }

        return response()->json(array(
            "success" => 0,
            "data" => [],
            "error" => "Failed to authenticate user",
            "errors" => [],
            "trace" => []
        ), 422);

    }

    public function logout(Request $request): JsonResponse {
        Auth::guard('jwt')->logout();

        return response()->json(array(
            "success" => 1,
            "data" => [],
            "error" => null,
            "errors" => [],
            "extra" => []
        ));
    }
}
