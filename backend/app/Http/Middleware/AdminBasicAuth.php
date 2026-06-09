<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP Basic Auth gate for the /admin pages.
 *
 * Credentials live in .env (ADMIN_USERNAME / ADMIN_PASSWORD).
 * No User model / DB row required — single admin scenario.
 */
class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = (string) config('quiz.admin.username', '');
        $expectedPass = (string) config('quiz.admin.password', '');

        if ($expectedUser === '' || $expectedPass === '') {
            abort(503, 'Admin credentials are not configured.');
        }

        $providedUser = (string) ($request->getUser() ?? '');
        $providedPass = (string) ($request->getPassword() ?? '');

        $userOk = hash_equals($expectedUser, $providedUser);
        $passOk = hash_equals($expectedPass, $providedPass);

        if (! $userOk || ! $passOk) {
            return response('Unauthorized.', 401, [
                'WWW-Authenticate' => 'Basic realm="Regency Quiz Admin"',
            ]);
        }

        return $next($request);
    }
}
