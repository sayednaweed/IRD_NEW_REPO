<?php

namespace App\Http\Middleware\api\template\user\main;

use App\Models\UserPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserHasMainViewPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        $authUser = $request->user();
        if ($authUser) {
            // 1. Check user has user permission
            $permission = UserPermission::where("user_id", "=", $authUser->id)
                ->where("permission", '=', $permission)
                ->where('view', true)
                ->select('id')
                ->first();
            if ($permission) {
                return $next($request);
            }
        }
        return response()->json([
            'message' => __('app_translation.unauthorized'),
        ], 403, [], JSON_UNESCAPED_UNICODE);
    }
}
