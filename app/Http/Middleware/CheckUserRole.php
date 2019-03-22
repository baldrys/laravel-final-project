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
        foreach ($roles as $role) {
    
            try {
                if ($request->user()->can($role)) {
                  return $next($request);
            }
    
            } catch (ModelNotFoundException $exception) {
              abort(403);
            }
        }
    
    }
}
