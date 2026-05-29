<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\AdminLog;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId   = $request->cookie('admin_id');
        $sessionId = $request->cookie('admin_session_id');
        
        if (!$adminId) {
            $this->expireOrphanSession($sessionId);
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);
        
        if (!$admin || $admin->is_active != 1) {
            $this->expireOrphanSession($sessionId);
            return redirect()->route('admin.login')
                ->withCookie(\Cookie::forget('admin_id'))
                ->withCookie(\Cookie::forget('admin_session_id'));
        }

        return $next($request);
    }

    /**
     * If there's an orphan active session in DB, mark it as expired.
     */
    private function expireOrphanSession(?string $sessionId): void
    {
        if (!$sessionId) return;

        $log = AdminLog::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if ($log) {
            $log->closeSession('expired');
        }
    }
}
