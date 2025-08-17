<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next)
    {
        // جلب الحالة من جدول settings
        $isActive = setting('isActive', true); // القيمة الافتراضية true يعني التطبيق يعمل

        if ($isActive) {
            return $next($request); // التطبيق يعمل، يسمح بالطلب
        }

        return response()->json([
            'message' => 'The application is under maintenance'
        ], 503); // 503 كود مناسب للصيانة
    }
}
