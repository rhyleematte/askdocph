<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackLastActive
{
    /**
     * Update last_active_at for the authenticated user at most once per minute
     * to avoid hammering the DB on every request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Only write if last_active_at is null or older than 60 seconds
            $threshold = now()->subMinutes(1);
            if (is_null($user->last_active_at) || $user->last_active_at < $threshold) {
                // Use DB directly to skip model events / timestamps overhead
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['last_active_at' => now()]);

                // Keep the in-memory model fresh so this request also sees it
                $user->last_active_at = now();
            }
        }

        return $next($request);
    }
}
