<?php

namespace Rahban\LaravelLogbook\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogbookAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();

        if ($session->get('logbook_authenticated')) {
            return $next($request);
        }

        // Check for basic auth credentials
        $username = $request->getUser();
        $password = $request->getPassword();

        if ($username && $password) {
            $configUser = config('logbook.auth_user');
            $configPass = config('logbook.auth_pass');

            if ($username === $configUser && $password === $configPass) {
                $session->put('logbook_authenticated', true);
                return $next($request);
            }
        }

        // Return 401 with basic auth challenge
        return response('Unauthorized', 401, [
            'WWW-Authenticate' => 'Basic realm="Logbook Admin Panel"'
        ]);
    }
}
