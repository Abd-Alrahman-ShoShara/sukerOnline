<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AttributeController extends Controller
{
    public function updateWorkTime(Request $request)
{
    $request->validate([
        'startTime' => 'required|date_format:H:i',
        'endTime' => 'required|date_format:H:i|after:startTime'
    ]);

    $configPath = config_path('timeWork.json');
    $config = json_decode(File::get($configPath), true);

    $config['startTime'] = $request->startTime;
    $config['endTime'] = $request->endTime;

    File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));

    return response()->json([
        'message' => 'Time updated successfully',
    ]);
}
    public function getWorskTime()
    {
        $configPath = config_path('timeWork.json');
        $config = json_decode(File::get($configPath), true);

        return response()->json([
            'startTime' => $config['startTime'],
            'endTime' => $config['endTime'],
        ]);
    }
    public function updateStorePrice(Request $request)
    {
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');

        $configPath = config_path('timeWork.json');
        $config = json_decode(File::get($configPath), true);

        $config['startTime'] = $startTime;
        $config['endTime'] = $endTime;

        File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));

        return response()->json([
            'message' => 'Time updated successfully',
        ]);
    }
    public function getWorkTime()
    {
        $configPath = config_path('timeWork.json');
        $config = json_decode(File::get($configPath), true);

        return response()->json([
            'startTime' => $config['startTime'],
            'endTime' => $config['endTime'],
        ]);
    }
}
