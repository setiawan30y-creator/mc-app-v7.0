<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchContextMiddleware
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

        // User wajib memiliki branch.
        if ($user->branch_id === null) {
            abort(403, 'Akun Anda belum memiliki cabang.');
        }

        // Pastikan branch user benar-benar tersedia.
        $branch = $user->branch;

        if ($branch === null) {
            abort(403, 'Cabang akun tidak ditemukan.');
        }

        // Branch harus aktif.
        if ($branch->status !== 'active') {
            abort(403, 'Cabang Anda sedang tidak aktif.');
        }

        // Pastikan branch benar-benar milik tenant user.
        if (
            $user->tenant_id === null ||
            $branch->tenant_id !== $user->tenant_id
        ) {
            abort(403, 'Cabang tidak berada dalam tenant akun Anda.');
        }

        // Simpan branch aktif ke request.
        $request->attributes->set('branch', $branch);
        $request->attributes->set('branch_id', $branch->id);

        return $next($request);
    }
}