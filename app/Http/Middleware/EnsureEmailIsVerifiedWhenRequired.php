<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedWhenRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('civictrack.require_email_verification')) {
            return $next($request);
        }

        if ($request->user()?->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->user() === null) {
            return redirect()->guest(route('login'));
        }

        return redirect()->guest(route('verification.notice'));
    }
}
