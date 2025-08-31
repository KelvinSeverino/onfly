<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index()
    {
        $users = $this->service->getUsers();
        return UserResource::collection($users)
                    ->response()
                    ->setStatusCode(200);
    }

    public function store(UserRequest $request)
    {
        $user = $this->service->createUser($request->validated());
        return (new UserResource($user))
                ->response()
                ->setStatusCode(201);
    }

    public function show(User $user)
    {
        $user = $this->service->findUser($user->id);
        return new UserResource($user);
    }

    public function update(UserRequest $request, User $user)
    {
        $userUpdated = $this->service->updateUser($user, $request->validated());

        return (new UserResource($userUpdated))
                    ->response()
                    ->setStatusCode(200);
    }

    public function destroy(User $user)
    {
        $this->service->deleteUser($user);
        return response()->json(null, 204);
    }
}
