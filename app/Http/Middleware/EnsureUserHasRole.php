<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route group to one or more roles.
 *
 * Registered as the "role" alias, e.g. middleware('role:admin,lecturer').
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $allowed = array_map(
            fn (string $role) => UserRole::from($role),
            $roles
        );

        abort_unless(in_array($user->role, $allowed, true), 403, 'You do not have access to this area.');

        return $next($request);
    }
}
