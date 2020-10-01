<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class AuthenticateExternalAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validsecrets = explode(',', env('EXTERNAL_ACCESS_KEY'));

        if (in_array($request->bearerToken(), $validsecrets)) {
            return $next($request);
        }
        abort(Response::HTTP_UNAUTHORIZED);
    }
}
