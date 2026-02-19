<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            // Update last seen (Safe check)
            try {
                Auth::user()->update(['last_seen_at' => now()]);
            } catch (\Exception $e) {
                // Column might be missing, ignore for now
            }

            if ($request->isMethod('GET')) {
                try {
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'page_visit',
                        'path' => $request->path(),
                        'method' => $request->method(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'data' => [
                            'full_url' => $request->fullUrl(),
                        ]
                    ]);
                } catch (\Exception $e) {
                    // Table might be missing
                }
            }
        }

        return $response;
    }
}
