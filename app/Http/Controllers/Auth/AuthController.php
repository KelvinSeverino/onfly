<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $service
    ) {}

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = $this->service->login($credentials);

        $user = $this->service->user();

        return $this->respondWithUserAndTokenInCookie($token, $user);
    }

    public function profile()
    {
        $user = $this->service->user();

        return new UserResource($user);
    }

    public function logout()
    {
        $this->service->logout();
        return response()->json(['message' => 'Successfully logged out'])
                                ->cookie('token_'.env('APP_NAME'), '', -1); // remove o cookie;
    }

    public function refresh()
    {
        return $this->respondWithTokenInCookie($this->service->refresh());
    }

    protected function respondWithUserAndTokenInCookie(string $token, $user)
    {
        $minutes = $this->service->tokenTTL();

        return response()
            ->json(new UserResource($user))
            ->cookie(
                'token_'.env('APP_NAME'),   // nome do cookie
                $token,                     // valor
                $minutes,                   // duração
                null,                       // path
                null,                       // domain
                true,                       // Secure (HTTPS)
                true                        // HttpOnly
            );
    }    
    
    protected function respondWithTokenInCookie(string $token)
    {
        $minutes = $this->service->tokenTTL();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => $minutes,
        ])->cookie(
            'token_'.env('APP_NAME'),   // nome do cookie
            $token,                     // valor
            $minutes,                   // duração
            null,                       // path
            null,                       // domain
            true,                       // Secure (HTTPS)
            true                        // HttpOnly;
        );
    }

    protected function respondWithToken(string $token)
    {
        $minutes = $this->service->tokenTTL();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => $minutes,
        ]);
    }
}

