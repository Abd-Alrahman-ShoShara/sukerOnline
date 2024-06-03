<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function create(Request $request)
    {
    $neworder=Order::create([
            'user_id'=>Auth::uesr()->id,
            'state'=> $request->state,
            'ReadOrNot'=>$request->ReadOrNot,
        ]);
        
        return response()->json([
            'message'=>'the order was added successfully',
            'neworder'=>$neworder,
        ],200);

    }

    public function showOrders(Request $request )
    {
        $order=Order::all();
        return response()->json([
            'orders :'=>$order,
        ],200);
    }

    
    public function editorder(Request $request,$id)
    { 
        $attrs= $request->validate([
        'quantity'=> 'required|int',
            ]);

        $eorder=Order::where(['id',$id],['state','pending'])->get();
        if(!$eorder){
            return response()->json([
                'message'=>'you can not edit the order',
            ],200);
            
        }else{
            $afterEdit=Cart::where('order_id',$id)->update([
                'quantity'=>$attrs['quantity'],
            ]);
        }
    }
    

    ///////////////////////////to Delete the order
    public function deleteOrder(Request $request,$id){
        $eorder=Order::where(['id',$id],['state','pending'])->get();
        if(!$eorder){
            return response()->json([
                'message'=>'you can not remove the order',
            ],200);
        }else{
            $toRemove=Order::where('id',$id)->delete();
            return response()->json([
                'message'=>'the order was deleted successfully ',
                'deltedone'=>$toRemove,
            ],200);
            
        }
    }
    ////////////////////////////for order state
    public function accepetOrder($id){
        $accOrder=Order::where(['id',$id],['state','pending'])->first();
        return response()->json([
            'message'=>'the order is accepted',
            'uporder'=>$accOrder->update([
                'state'=>'accepted',
            ]),
        ],200);

    }

    public function rejectedOrder($id){
        $rejOrder=Order::where(['id',$id],['state','pending'])->first();
        return response()->json([
            'message'=>'the order is accepted',
            'uporder'=>$rejOrder->update([
                'state'=>'rejected',
            ]),
        ],200);

    }

    //////////////////////////////////////for notifications
    public function orderNotifications(Request $request){
        $noti=Order::where('ReadOrNot','=','1')->get();
        if($noti)
        {
        return response()->json([
            'message'=>'you have an order',
            'new-order'=>$noti,
        ],200);
        }
        else
        {
        return response()->json([
            'message'=>'there are no new orders'
        ],200);
        }
    }


}
