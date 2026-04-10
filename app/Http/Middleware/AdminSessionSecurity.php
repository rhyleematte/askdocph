<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminSessionSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to authenticated admins
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {

            $session = $request->session();

            $lastActivity = $session->get('admin_last_activity');
            $sessionIp = $session->get('admin_ip');
            $sessionUserAgent = $session->get('admin_user_agent');

            $lifetime = config('admin.session_lifetime', 900); // 15 mins default

            // 1. Session Idle Timeout Check
            if ($lastActivity && (time() - $lastActivity > $lifetime)) {
                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                $session->invalidate();
                $session->regenerateToken();
                return redirect()->route('admin.login')->withErrors(['email' => 'Your session has expired due to inactivity.']);
            }

            // 2. IP Hijacking Check
            if ($sessionIp && $sessionIp !== $request->ip()) {
                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                $session->invalidate();
                $session->regenerateToken();
                return redirect()->route('admin.login')->withErrors(['email' => 'Session invalidated due to IP address change.']);
            }

            // 3. User-Agent Hijacking Check
            if ($sessionUserAgent && $sessionUserAgent !== $request->userAgent()) {
                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                $session->invalidate();
                $session->regenerateToken();
                return redirect()->route('admin.login')->withErrors(['email' => 'Session invalidated due to browser anomaly.']);
            }

            // Update Last Activity
            $session->put('admin_last_activity', time());
        }

        return $next($request);
    }
}
