<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     * ->middleware('permission:dashboard.view')
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        // User harus sudah login.
        if ($user === null) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        // User harus memiliki permission yang diminta.
        if (! $user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}