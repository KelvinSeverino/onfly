<?php

namespace App\Http\Middlewares;

use App\Exceptions\AdminOnlyActionException;
use Closure;

class CheckUserRole
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user || $user->role != 'admin' || empty($user->role)) {
            throw new AdminOnlyActionException();
        }

        return $next($request);
    }
}