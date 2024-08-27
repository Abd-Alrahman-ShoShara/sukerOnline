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
       
        $configPath = config_path('appWorking.json');
    
        if (!File::exists($configPath)) {
            return response()->json(['message' => 'Configuration file not found.'], 404);
        }
    
        $config = json_decode(File::get($configPath), true);
        if ( $config['isActive']){

            return $next($request);
        }
        return response()->json([
            'message'=>'the application under Maintenance '
        ]);

    }
}
