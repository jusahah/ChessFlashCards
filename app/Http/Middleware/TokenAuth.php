<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        $user = \Auth::guard('api')->user();

        if ($user && $this->tokenPresentInHeader($user, $request)) {
            return $next($request);
        }

        throw new AuthenticationException('TokenAuth middleware rejected request');
    }

    protected function tokenPresentInHeader(User $user, $request)
    {
        return $user->getApiKey() && $user->getApiKey() === \Auth::guard('api')->getTokenForRequest();
    }
}
