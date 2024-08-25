<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UltraMsg\WhatsAppApi;

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
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'adress' => $request->adress,
            'classification_id' => $request->classification_id,
            'nameOfStore' => $request->nameOfStore,
        ]);

        // $token = $user->createToken('auth_token')->accessToken;

        $code = mt_rand(1000, 9999);
        $user->verification_code = $code;
        $user->save();

        $this->sendCode($user['phone'], $code,$user['name']);

        return response([
            'message' => 'User registered successfully. Please enter the verification code.',
            'user_id' => $user->id,

        ]);
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
            'message' => 'Verification successful. User is now verified.',
            'token'=>$token,
        ],200);
    } else {
        return response([
            'message' => 'Invalid verification code.',
        ], 422);
    }

    }

    /////////////////////////////////////////////
    public function logout(){
        User::find(Auth::id())->tokens()->delete();
        return response([
            'message'=>'Logged out sucesfully'
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
                'message'=>'the phone number is wrong'
            ]);
        }
        $code = mt_rand(1000, 9999);
        $user->verification_code = $code;
        $user->save();

        $this->sendCode($user['phone'], $code,$user['name']);

        return response([
            'message' => 'The code was sent. Please enter it to verification.',
            'user_id' => $user->id,

        ]);

    }
    ///////////////////////////////////////////////////
    public function verifyForgetPassword(Request $request){

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->verification_code == $request->code) {

            //  $token=$user->createToken('auth_token')->accessToken;

            return response([
                'message' => 'Verification successful. enter the new password.',

            ],200);
        } else {
            return response([
                'message' => 'Invalid verification code.',
            ], 422);
        }

    }

    /////////////////////////////////////////////////////
    public function resatPassword(Request $request){
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password'=>'required|min:6|confirmed',
        ]);
         $user = User::findOrFail($request->user_id);
         $user-> update(['password' => Hash::make($request['password'])]);

        $token=$user->createToken('auth_token')->accessToken;

         return response()->json([
        'message'=> 'the password is updated',
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
            'message' => 'The password has been updated.',
        ]);
        } else {
        return response()->json([
            'message' => 'The old password is incorrect.',
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

    /////////////////////////////////////////
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'The provided credentials are incorrect'
            ], 403);
        }

        if ($user->role == '0') {
            $user->type = 'admin';
        } else {
            $user->type = 'user';
        }

        $token = $user->createToken('auth_token')->accessToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }
    // public function loginForUser(Request $request)
    // {
    //     $request->validate([
    //         'phone' => 'required',
    //         'password' => 'required'
    //     ]);

    //     $user = User::where('phone', $request->phone)->first();

    //     if ($user->role == '0') {
    //         return response([
    //             'message' => 'no accses'
    //         ], 403);
    //     }

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response([
    //             'message' => 'The provided credentials are incorrect'
    //         ], 403);
    //     }

    //     $token = $user->createToken('auth_token')->accessToken;

    //     return response([
    //         'user' => $user,
    //         'token' => $token
    //     ]);
    // }

    public function userInfo(){
        return response()->json([
            'user'=> User::where('id',Auth::user()->id)->with('classification')->get(),
        ]);
    }
}
