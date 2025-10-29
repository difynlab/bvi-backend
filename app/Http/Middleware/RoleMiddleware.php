<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user();

        if($user->role != $role) {
            $access_token = $user->token();

            if($access_token) {
                $access_token->revoke();
                $access_token->refreshToken?->revoke();
            }

            return errorResponse('Unauthorized access', 401, [
                'info' => 'Invalid request from a different user'
            ]);
        }

        return $next($request);
    }
}
