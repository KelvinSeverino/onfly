<?php 

namespace App\Http\Middlewares;

use App\Exceptions\Domain\{
    Auth\InvalidTokenException,
    Auth\TokenMissingException,
    Auth\UserNotAuthenticatedException
};
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Exception;

class AuthenticateWithCookie
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->cookie('token_' . env('APP_NAME'));

            if (!$token) {
                throw new TokenMissingException();
            }

            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if (!$user) {
                throw new UserNotAuthenticatedException();
            }
        } catch (TokenExpiredException | TokenInvalidException $e) {
            throw new InvalidTokenException();
        } catch (Exception $e) {
            if ($e instanceof TokenMissingException ||
                $e instanceof UserNotAuthenticatedException) {
                throw $e;
            }

            throw new InvalidTokenException(); // genérico
        }

        return $next($request);
    }
}
