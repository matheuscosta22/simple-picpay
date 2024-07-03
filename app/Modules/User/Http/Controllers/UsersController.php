<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Data\UserData;
use App\Modules\User\Http\Requests\UsersRequest;
use App\Modules\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->userService->getAll(
                $request->input('page', 1),
                $request->input('per_page', 10)
            )->setPath($request->url())
        );
    }

    public function show(int $userId)
    {
        $user = $this->userService->find($userId);

        if (!$user) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        return response()->json($user);
    }

    public function store(UsersRequest $request)
    {
        $userData = UserData::from($request->all());

        $user = $this->userService->create($userData);
        return response()->json($user, 201);
    }
}
