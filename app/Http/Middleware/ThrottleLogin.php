<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Blocks repeated failed login attempts per IP + email combination.
 * Max 5 attempts per minute. On lockout → 429 with Retry-After header.
 */
class ThrottleLogin
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'login:' . md5(strtolower($request->input('email', '')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withErrors([
                'email' => "Too many login attempts. Please wait {$seconds} seconds before trying again.",
            ])
                ->withInput($request->only('email'));
        }

        $response = $next($request);

        // If login failed (redirect back with errors), hit the counter
        if ($response->isRedirect() && session()->has('errors')) {
            RateLimiter::hit($key, 60); // 1-minute decay
        }
        else {
            // Successful login — clear the counter
            RateLimiter::clear($key);
        }

        return $response;
    }
}
