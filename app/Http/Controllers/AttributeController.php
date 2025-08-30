<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class AttributeController extends Controller
{
    /* ========== أوقات العمل ========== */

    public function updateWorkTime(Request $request)
    {
        $request->validate([
            'startTime' => 'required',
            'endTime'   => 'required',
        ]);

        set_setting('startTime', $request->startTime);
        set_setting('endTime',   $request->endTime);

        return response()->json(['message' => 'تم تعديل ساعات العمل بنجاح'], 200);
    }

    public function getWorkTime()
    {
        return response()->json([
            'startTime' => setting('startTime'),
            'endTime'   => setting('endTime'),
        ]);
    }

    /* ========== من نحن ========== */

    public function getAboutUs()
    {
        $content      = setting('abouteUs', '');
        $userLanguage = auth()->user()->language ?? 'ar';

        $tr          = new GoogleTranslate();
        $translated  = $tr->setTarget($userLanguage)->translate($content);

        return response()->json(['abouteUs' => $translated]);
    }

    public function updateAboutUs(Request $request)
    {
        $request->validate(['abouteUs' => 'required|string']);
        set_setting('abouteUs', $request->abouteUs);

        return response()->json(['message' => 'تم تعديل المعلومات بنجاح'], 200);
    }

    /* ========== أرقام التواصل ========== */

    public function getPhoneNumbers()
    {
        return response()->json([
            'Num1' => setting('Num1'),
            'Num2' => setting('Num2'),
            'Num3' => setting('Num3'),
        ]);
    }

    public function updatePhoneNumbers(Request $request)
    {
        $request->validate([
            'Num1' => 'required|string',
            'Num2' => 'required|string',
            'Num3' => 'required|string',
        ]);

        set_setting('Num1', $request->Num1);
        set_setting('Num2', $request->Num2);
        set_setting('Num3', $request->Num3);

        return response()->json(['message' => 'تم تعديل أرقام التواصل بنجاح'], 200);
    }

    /*  الأسعار الثابتة  */

    public function getPrices()
    {
        return response()->json([
            'storePrice'  => setting('storePrice'),
            'urgentPrice' => setting('urgentPrice'),
        ]);
    }

    public function updateStorePrice(Request $request)
    {
        $request->validate(['storePrice' => 'required|numeric']);
        set_setting('storePrice', $request->storePrice);

        return response()->json(['message' => 'تم تعديل سعر الطلبية المخزَّنة بنجاح'], 200);
    }

    public function updateUrgentPrice(Request $request)
    {
        $request->validate(['urgentPrice' => 'required|numeric']);
        set_setting('urgentPrice', $request->urgentPrice);

        return response()->json(['message' => 'تم تعديل سعر الطلبية المستعجلة بنجاح'], 200);
    }

    /* ========== تشغيل/إيقاف التطبيق ========== */

    public function AppOnOff()
    {
        $current = setting('isActive', true);          // القيمة الافتراضية: يَعمل
        $next    = ! $current;

        set_setting('isActive', $next);

        return response()->json([
            'message' => $next ? 'التطبيق قيد العمل' : 'التطبيق في حالة صيانة',
        ], 200);
    }
}

// namespace App\Http\Controllers;
// use Stichoza\GoogleTranslate\GoogleTranslate;
// use GPBMetadata\Google\Api\Auth;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\File;

// class AttributeController extends Controller
// {
    
    // public function updateWorkTime(Request $request)
    // {
    //     $request->validate([
    //         'startTime' => 'required|date_format:H:i',
    //         'endTime' => 'required|date_format:H:i|after:startTime'
    //     ]);

    //     $configPath = config_path('timeWork.json');
    //     $config = json_decode(File::get($configPath), true);

    //     $config['startTime'] = $request->startTime;
    //     $config['endTime'] = $request->endTime;

    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));

    //     return response()->json([
    //         'message' => 'تم تعديل ساعات العمل بنجاح',
    //     ],200);
    // }

    // public function getWorkTime()
    // {
    //     $configPath = config_path('timeWork.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     return response()->json([
    //         'startTime' => $config['startTime'],
    //         'endTime' => $config['endTime'],
    //     ]);
    // }

    // public function getAboutUs()
    // {
    //     $configPath = config_path('abouteUs.json');
    //     $config = json_decode(File::get($configPath), true);
    //     $userLanguage = auth()->user()->language;
    //     $tr = new GoogleTranslate();
    //     // $config['abouteUs'] = mb_convert_encoding($config['abouteUs'], 'UTF-8', 'UTF-16BE'); 
    //     // Translate only the 'abouteUs' part
    //     $config['abouteUs'] = $tr->setTarget($userLanguage)->translate($config['abouteUs']);
    
    //     return response()->json([
    //         'abouteUs' => $config['abouteUs'],
    //     ]);
    // }
    // public function updateAboutUs(Request $request)
    // {
    //     $request->validate([
    //         'abouteUs' => 'required|string',
    //     ]);

    //     $configPath = config_path('abouteUs.json');
    //     $config = json_decode(File::get($configPath), true);

    //     $config['abouteUs'] = $request->abouteUs;
        

    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));

    //     return response()->json([
    //         'message' => ' تم تعديل المعلومات بنجاح',
    //     ],200);
    // }
    // public function getPhoneNumbers()
    // {
    //     $configPath = config_path('phoneNumbers.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     return response()->json([
    //         'Num1' => $config['Num1'],
    //         'Num2' => $config['Num2'],
    //         'Num3' => $config['Num3'],
    //     ]);
    // }
    // public function updatePhoneNumbers(Request $request)
    // {
    //     $request->validate([
    //         'Num1' => 'required|string',
    //         'Num2' => 'required|string',
    //         'Num3' => 'required|string',
    //     ]);
        
    //     $configPath = config_path('phoneNumbers.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     $config['Num1'] = $request->Num1;
    //     $config['Num2'] = $request->Num2;
    //     $config['Num3'] = $request->Num3;
        
        
    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
    //     return response()->json([
    //         'message' => ' تم تعديل ارقام التواصل بنجاح',
    //     ],200);
    // }
    
    
    
    // public function updateStorePrice(Request $request)
    // {
    //     $request->validate([
    //         'storePrice'=>'required|numeric'
    //     ]);
    //     $storePrice = $request->input('storePrice');
    //     $configPath = config_path('staticPrice.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     $config['storePrice'] = $storePrice;
        
        
    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
    //     return response()->json([
    //         'message' => ' تم تعديل سعر الطلبية المخزنة بنجاح',
    //     ],200);
    // }
    // public function getPrices()
    // {
    //     $configPath = config_path('staticPrice.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     return response()->json([
    //         'storePrice' => $config['storePrice'],
    //         'urgentPrice' => $config['urgentPrice'],
    //     ]);
    // }
    // public function updateUrgentPrice(Request $request)
    // {
    //     $request->validate([
    //         'urgentPrice'=>'required|numeric'
    //     ]);
    //     $urgentPrice = $request->input('urgentPrice');
    //     $configPath = config_path('staticPrice.json');
    //     $config = json_decode(File::get($configPath), true);
        
    //     $config['urgentPrice'] = $urgentPrice;
        
        
    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
        
    //     return response()->json([
    //         'message' => ' تم تعديل سعر الطلبية المستعجلة بنجاح',
    //     ],200);
    // }
    
    // public function AppOnOff()
    // {
    //     $configPath = config_path('appWorking.json');
    
    //     if (!File::exists($configPath)) {
    //         return response()->json(['message' => 'Configuration file not found.'], 404);
    //     }
    
    //     $config = json_decode(File::get($configPath), true);
    
    //     if ($config === null) {
    //         return response()->json(['message' => 'Invalid JSON format.'], 400);
    //     }
    
    //     $config['isActive'] = !$config['isActive'];
    
    //     File::put($configPath, json_encode($config, JSON_PRETTY_PRINT));
    
    //     return response()->json([
    //         'message' => $config['isActive'] ? ' التطبيق قيد العمل ' : ' التطبيق في حالة صيانة',
    //     ],200);
    // }
// }
