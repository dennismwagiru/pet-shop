<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\HasApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\EditUserRequest;

class AdminController extends Controller
{
    use HasApiResponse;

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

        return $this->apiResponse(data: ['success' => 1]);
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

    public function userListing(): JsonResponse
    {
        $order = boolean(request('desc')) ? 'desc' : 'asc';
        $payload = User::where('is_admin', true)
            ->filterBy(request()->all())
            ->when(request('sortBy', false), function ($q, $sortBy) use ($order) {
                if (Schema::hasColumn('users', $sortBy)) {
                    return $q->orderBy($sortBy, $order);
                }
                return null;
            })
            ->paginate(request('limit', config('settings.defaults.limit')));

        return $this->apiResponse(
            data: ['payload' => $payload],
            type: 'payload',
        );
    }

    /**
     * @param EditUserRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function userEdit(EditUserRequest $request, string $uuid): JsonResponse
    {
        $user = User::where('is_admin', true)->where('uuid', $uuid)->firstOrFail();

        $user->update(array_merge(
            $request->all(),
            [
                'avatar' => $request->get('avatar', $user->avatar) ?? $user->avatar,
                'is_marketing' => $request->has('marketing', $request->get('is_marketing')),
                'is_admin' => true,
                'password' => bcrypt($request->get('password')),
            ]
        ));
        return $this->apiResponse(
            data: [
                'success' => 1,
                'data' => $user->toArray(),
            ]
        );
    }

    public function userDelete(string $uuid): JsonResponse
    {
        $user = User::where('is_admin', true)->where('uuid', $uuid)->firstOrFail();

        $user->delete();

        return $this->apiResponse(data: ['success' => 1]);
    }
}
