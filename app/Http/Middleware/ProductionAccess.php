<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        $username = (string) config('app.access_username');
        $password = (string) config('app.access_password');
        if ($username === '' || $password === '') {
            abort(503, 'Website access is not configured.');
        }
        if (! hash_equals($username, (string) $request->getUser()) || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Please sign in.', 401, ['WWW-Authenticate' => 'Basic realm="MS Corporation", charset="UTF-8"', 'Cache-Control' => 'no-store']);
        }

        return $next($request);
    }
}
