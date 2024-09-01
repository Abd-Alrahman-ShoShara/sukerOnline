<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UltraMsg\WhatsAppApi;
use App\Notifications\FirebasePushNotification;
class AuthenticationController extends Controller
{


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|unique:users|regex:/^[0-9]+$/',
            'password' => 'required|min:6|confirmed',
            'nameOfStore'=> 'required',
            'classification_id'=>'required',
            'adress'=> 'required',
            'fcm_token'=> 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'adress' => $request->adress,
            'classification_id' => $request->classification_id,
            'nameOfStore' => $request->nameOfStore,
            'fcm_token'=>$request['fcm_token'],
        ]);


        // $token = $user->createToken('auth_token')->accessToken;

        $code = mt_rand(1000, 9999);
        $user->verification_code = $code;
        $user->save();

        $this->sendCode($user['phone'], $code,$user['name']);

        return response([
            'message' => 'تم التسجيل بنجاح , الرجاء ادخال كود التأكيد',
            'user_id' => $user->id,

        ],200);
    }
    /////////////////////////////////////////////////////////////////////
    function verifyCode(Request $request)
    {

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'code' => 'required',
       
    ]);

    $user = User::findOrFail($request->user_id);

    if ($user->verification_code == $request->code) {
        // Code is correct, perform any additional actions (e.g., update user status, etc.)
        $user->is_verified = true;
        $user->save();
         $token=$user->createToken('auth_token')->accessToken;
        return response([
            'message' => 'تم التأكيد بنجاح',
            'token'=>$token,
        ],200);
    } else {

        return response([
            'message' => 'الكود الذي ادخلته خاطئ الرجاء المحاولة مجدداً',
        ], 422);
    }

    }

    /////////////////////////////////////////////
    public function logout(){
        User::find(Auth::id())->tokens()->delete();
        return response([
            'message'=>'تم تسجيل الخروج'
        ]);
    }
    //////////////////////////////////////
    public function forgetPassword(Request $request){
        $request->validate([
            'phone'=>'required',
        ]);

        $user=User::where('phone',$request->phone)->first();
        if(!$user){
            return response()->json([
                'message'=>'رقم الموبايل الذي ادخلته خاطئ'
            ]);
        }
        $code = mt_rand(1000, 9999);
        $user->verification_code = $code;
        $user->save();

        $this->sendCode($user['phone'], $code,$user['name']);

        return response([
            'message' => 'لقد ارسلنا كود تأكيد الى رقمك قم بادخاله ',
            'user_id' => $user->id,

        ],200);

    }
    ///////////////////////////////////////////////////
    public function verifyForgetPassword(Request $request){

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required',
        ]);
        
        $user = User::findOrFail($request->user_id);
        
        if ($user->verification_code == $request->code) {
            
            
            
            return response([
                'message' => 'تم التأكيد , ادخل كلمة السر الجديدة.',
                
            ],200);
        } else {
            return response([
                'message' => 'الكود الذي قمت بادخاله خاطئ',
            ], 422);
        }
        
    }
    
    /////////////////////////////////////////////////////
    public function resatPassword(Request $request){
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password'=>'required|min:6|confirmed',
            'fcm_token' => 'required',
        ]);
         $user = User::findOrFail($request->user_id);
         $user-> update(['password' => Hash::make($request['password'])]);

        $token=$user->createToken('auth_token')->accessToken;
        $user->fcm_token=$request['fcm_token'];
        $user->save();
         return response()->json([
        'message'=> 'تم تعديل كلمة السر',
        'token'=>$token,
         ]);

    }
    ///////////////////////////////////////////
    public function resatPasswordEnternal(Request $request){

    $request->validate([
        'password' => 'required|min:6',
        'NewPassword' => 'required|min:6|confirmed',
    ]);


    if (Hash::check($request->password, auth()->user()->password)) {

        auth()->user()->update(['password' => Hash::make($request['NewPassword'])]);
        return response()->json([
            'message' => 'تم تعديل كلمة السر.',
        ]);
        } else {
        return response()->json([
            'message' => 'كلمة السر القديمة خاطئة',
        ]);
        }
    }



    /////////////////////////////////////////
    public function sendCode($phoneNumber, $code, $name)
    {
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN');
        $instance_id = env('WHATSAPP_ID');
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr($phoneNumber, 1, 9);
        $to = $number;

        $body = 'Hi ' . $name . ', your verification code is: ' . $code ;
        $client->sendChatMessage($to, $body);
        // return $this->success(null, 'we send the code');
    }
  
    /////////////////////////////////////


    public function login(Request $request){
        $request->validate([
            'phone' => 'required|regex:/^[0-9]+$/',
            'password' => 'required|min:6',
            'fcm_token' => 'required'
        ]);
    
        $user = User::where('phone', $request->phone)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'المعلومات المدخلة خاطئة,الرجاء اعادة المحاولة'
            ], 403);
        }
        $user->fcm_token = $request->fcm_token; 
        $user->save();

        $user->type = $user->role == 0 ? 'admin' : 'user';
    
        $token = $user->createToken('auth_token')->accessToken;
    
        // Optionally send a notification (replace with your actual notification logic)
        // if ($user->role == 0) { // Only for admins
        //     $user->notify(new FirebasePushNotification('Login', 'Admin login successful'));
        // }
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
      public function userInfo(){
        return response()->json([
            'user'=> User::where('id',Auth::user()->id)->with('classification')->get(),
        ]);
    }

    
    public function allUsers(Request $request)
{
    $request->validate([
        'sort' => 'required|boolean',
    ]);

    $userQuery = User::where('role', 1)->with('classification');

    if ($request->sort) {
        $userQuery = $userQuery->withCount('order')->orderBy('order_count', 'desc');
    }

    $users = $userQuery->get();

    return response()->json([
        'users' => $users
    ]);
}

public function choseLanguage(Request $request) {
    $request->validate([
        'language' => 'required|in:en,ar',
    ]);

    $user = Auth::user();
    $user->language = $request->language;
    $user->save();

    return response()->json([
        'theUser' => $user,
    ]);
}    
}
