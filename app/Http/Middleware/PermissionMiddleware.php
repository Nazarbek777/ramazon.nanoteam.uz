<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $page): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            return redirect()->route('admin.login');
        }

        if ($user->hasPermission($page)) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', "Sizda «{$page}» sahifasiga kirish ruxsati yo'q.");
    }
}
