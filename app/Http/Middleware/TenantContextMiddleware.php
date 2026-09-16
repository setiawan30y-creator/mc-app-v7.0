<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        // Middleware ini membutuhkan user yang sudah login.
        if ($user === null) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        // User wajib memiliki tenant.
        if ($user->tenant_id === null) {
            abort(403, 'Akun Anda belum memiliki tenant.');
        }

        // Pastikan tenant user benar-benar tersedia.
        $tenant = $user->tenant;

        if ($tenant === null) {
            abort(403, 'Tenant akun tidak ditemukan.');
        }

        // Tenant harus aktif.
        if ($tenant->status !== 'active') {
            abort(403, 'Tenant Anda sedang tidak aktif.');
        }

        // Simpan tenant aktif ke request.
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);

        return $next($request);
    }
}