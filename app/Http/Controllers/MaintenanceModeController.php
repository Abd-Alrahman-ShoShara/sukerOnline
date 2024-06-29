<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceModeController extends Controller
{
    //
    public function setMaintenanceMode(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'start_time' => 'required_if:is_active,true|date',
            'end_time' => 'required_if:is_active,true|date|after_or_equal:start_time',
        ]);

        DB::table('maintenance_mode')->updateOrInsert(
            ['id' => 1],
            [
                'is_active' => $request->is_active,
                'start_time' => $request->is_active ? Carbon::parse($request->start_time) : null,
                'end_time' => $request->is_active ? Carbon::parse($request->end_time) : null,
                'updated_at' => Carbon::now(),
            ]
        );

        return response()->json(['message' => 'Maintenance mode updated successfully.']);
    }

    public function getMaintenanceMode()
    {
        $maintenance = DB::table('maintenance_mode')->first();

        return response()->json($maintenance);
    }
}
