<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class ActivityLoggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && strpos($request->path(), 'admin') === 0) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $request->method() . ' ' . $request->path(),
                'description' => $request->getRequestUri(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => json_encode($request->except('password', 'password_confirmation')),
            ]);
        }

        return $response;
    }
}
