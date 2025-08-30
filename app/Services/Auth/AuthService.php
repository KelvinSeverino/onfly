<?php

namespace App\Services\Auth;

use App\Exceptions\Domain\{
    Auth\InvalidCredentialsException,
    Auth\InvalidTokenException,
    Auth\TokenMissingException,
    Auth\UserNotAuthenticatedException
};
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(array $credentials): string
    {
        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            throw new InvalidCredentialsException();
        }

        JWTAuth::setToken($token)->toUser(); // força o contexto do usuário

        return $token;
    }

    public function logout(): void
    {
        $token = request()->cookie('token_' . env('APP_NAME'));

        if (!$token) {
            throw new TokenMissingException();
        }

        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException | TokenInvalidException | JWTException $e) {
            throw new InvalidTokenException();
        }
    }

    public function refresh(): string
    {
        $token = request()->cookie('token_' . env('APP_NAME'));

        if (!$token) {
            throw new TokenMissingException();
        }

        try {
            return JWTAuth::setToken($token)->refresh();
        } catch (TokenExpiredException | TokenInvalidException | JWTException $e) {
            throw new InvalidTokenException();
        }
    }

    public function user(): User
    {
        $user = Auth::user();

        if (!$user) {
            throw new UserNotAuthenticatedException();
        }

        return $user;
    }

    public function tokenTTL(): int
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        return $guard->factory()->getTTL() * 60;
    }
}
