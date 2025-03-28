<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::check() || !Auth::user()->hasPermission($permission)) {
            abort(403); // Laravel ahora mostrará automáticamente errors/403.blade.php si existe
        }

        return $next($request);
    }
}
