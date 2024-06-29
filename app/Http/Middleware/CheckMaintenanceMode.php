<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $maintenance = DB::table('maintenance_modes')->first();

        if ($maintenance && $maintenance->is_active) {
            $currentTime = Carbon::now();

            if ($maintenance->start_time <= $currentTime && $currentTime <= $maintenance->end_time) {
                return response()->json(['message' => 'The app is under maintenance. Please try again later.'], 503);
            }
        }

        return $next($request);
    }
}
