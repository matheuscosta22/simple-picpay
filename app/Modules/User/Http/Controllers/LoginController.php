<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Http\Requests\LoginRequest;
use App\Modules\User\Services\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $accessToken = (new LoginService())->getAccessToken(
            $request->input('email'),
            $request->input('password')
        );

        if (!$accessToken) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        return response()->json(['access_token' => $accessToken]);
    }
}
