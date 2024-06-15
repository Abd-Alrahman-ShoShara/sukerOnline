<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([]);
        
        
        $order = Order::create([
            'user_id' => Auth::user()->id,
        ]);

        
    }


    public function ordreOfuser()
    {
        return response([
            'orders' => Order::where('user_id', auth()->user()->id)->get()
        ], 200);
    }

    public function orderDetails($order_id)
    {
        return response([
            'carts' => cart::where('order_id', $order_id)->get()
        ], 200);
    }

    public function deleteOrder($id)
    {
        $order = Order::where(['id' => $id, 'state' => 'pending'])->first();

        if (!$order) {
            return response()->json([
                'message' => 'You cannot remove the order',
            ], 200);
        } else {
            $order->delete();
            return response()->json([
                'message' => 'The order was deleted successfully',

            ], 200);
        }
    }


    public function preparingOrder($id)
    {
        $accOrder = Order::where(['id' => $id, 'state' => 'pending'])->first();

        if ($accOrder) {
            $accOrder->update(['state' => 'preparing']);

            $phar_id = $accOrder->user_id;
            $phar = User::find($phar_id);
            $token = $phar->notiToken;
            $this->noti("your order is preparing", $token);
            return response([
                'message' => 'the order accepted',
                'state' => 'preparing'
            ], 200);
        } else {
            return response()->json(['message' => 'it is not possible to edit due to preparing'], 403);
        }
    }

    public function has_been_sentOrder($order_id)
    {
        $accOrder = Order::where([['id', $order_id], ['state', 'preparing']])->first();

        if ($accOrder) {
            $accOrder->update(['state' => 'has_been_sent']);
            
            return response([
                'message' => 'the order accepted',
                'state' => 'has_been_sent'
            ], 200);
        } else {
            return response()->json(['message' => 'it is not possible to edit due to has_been_sent'], 403);
        }
    }

    public function receivedOrder($id)
    {
        $accOrder = Order::where(['id' => $id, 'state' => 'has_been_sent'])->first();

        if ($accOrder) {
            $accOrder->update(['state' => 'received']);

            return response([
                'message' => 'the order accepted',
                'state' => 'received'
            ], 200);
        } else {
            return response()->json(['message' => 'it is not possible to edit due to received'], 403);
        }
    }


    // public function paidOrder($id)
    // {
    //     $accOrder = Order::where([
    //         ['id' , $id],
    //         ['payment_state','unpaid']
    //         ])->first();
    //     // dd($accOrder);

    //     if ($accOrder) {
    //         $accOrder->update(['payment_state' => 'paid']);

    //         $phar_id=$accOrder->user_id;
    //         $phar=Order::find($id);
    //         $user = DB::table('users')->where('id',$phar->user_id)->first();
    //         $token=$user->notiToken;
    //         // dd($token);
    //         $this->noti("your order is paid ",$token);

    //         return response([
    //             'message' => 'the order accepted',
    //             'state' => 'paid'
    //         ],200);
    //     }
    //     else{
    //         return response()->json(['message' => 'it is not possible to edit due to paid'], 403);
    //     }
    // }

    // public function report(Request $request)
    //     {
    //         $attrs=$request->validate([
    //             'first_date'=>'required|date',
    //             'second_date'=>'required|date',
    //         ]);
    //         if($attrs['first_date'] > $attrs['second_date'])
    //         return response(['message'=>'the first date is greater than the second date']);
    //         $it=auth()->user()->id;
    //         $orders=Order::with('users')
    //         ->whereHas('carts.depotmedicines', function ($query) use ($it) {
    //             $query->where('depot_id',$it);
    //         })
    //         ->whereDate('created_at', '>=',$attrs['first_date'])
    //         ->whereDate('created_at', '<=',$attrs['second_date'])
    //         ->get();

    //         $carts=Depot::with('medicines')
    //         ->whereDate('created_at', '>=',$attrs['first_date'])
    //         ->whereDate('created_at', '<=',$attrs['second_date'])
    //         ->get();

    //         $medicines=Depot::where('depot_id',$it)
    //         ->whereDate('date_of_end', '>=',$attrs['first_date'])
    //         ->whereDate('date_of_end', '<=',$attrs['second_date'])
    //         ->get();

    //         return response([
    //             'message_1'=>'orders during this period',
    //             'orders'=>$orders,
    //             'message_2'=>'mmmm',
    //             'carts'=>$carts,
    //             'message_3'=>'expired medications during this period',
    //             'medicines'=>$medicines

    //         ]);
    //     }
}
