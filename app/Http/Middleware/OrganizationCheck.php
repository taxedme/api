<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizationCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'organization_id' => 'required'
        ]);

        if (!Auth::user()) {
            return response()->apiError("Unauthenticated", 401);
        }

        $organization = Auth::user()->organization()->where('organizations.id', $request->organization_id)->exists();

        if (!$organization) {
            return response()->apiError("Organization does not exist");
        }

        return $next($request);
    }
}
