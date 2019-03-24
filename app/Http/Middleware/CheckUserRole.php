<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $me = auth("api")->user();
        if (!in_array($me->role, $roles)) {
            return response()->json([
                "success" => false,
                "message" => "Нет доступа для групппы " . $me->role
            ], 403);
        }
        return $next($request);

    }
}
