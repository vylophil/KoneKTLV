<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;

class AuthenticateBackend extends Authenticate
{
    /**
     * @param  Request  $request
     * @param  string  ...$guards
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $request->headers->set('Accept', 'application/json');

        return parent::handle($request, $next, ...$guards);
    }
}
