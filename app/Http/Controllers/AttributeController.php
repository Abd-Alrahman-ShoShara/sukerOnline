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


    public function getWorkTime()
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
        $request->validate([
            'storePrice'=>'required|numeric'
        ]);
        $storePrice = $request->input('storePrice');
        $configPath = config_path('staticPrice.json');
        $config = json_decode(File::get($configPath), true);
        
        $config['storePrice'] = $storePrice;
        
        
        File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
        return response()->json([
            'message' => 'storePrice updated successfully',
        ]);
    }
    public function getPrices()
    {
        $configPath = config_path('staticPrice.json');
        $config = json_decode(File::get($configPath), true);
    
        return response()->json([
            'storePrice' => $config['storePrice'],
            'urgentPrice' => $config['urgentPrice'],
        ]);
    }
    public function updateUrgentPrice(Request $request)
    {
        $request->validate([
            'urgentPrice'=>'required|numeric'
        ]);
        $urgentPrice = $request->input('urgentPrice');
        $configPath = config_path('staticPrice.json');
        $config = json_decode(File::get($configPath), true);
        
        $config['urgentPrice'] = $urgentPrice;
        
        
        File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
        return response()->json([
            'message' => 'UrgentPrice updated successfully',
        ]);
    }
    
}
